<?php

namespace App\Services;

use App\Support\UniqueCodeResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class UniqueCodeService
{
    public function resolve(
        string $model,
        string $field,
        ?string $requestedCode,
        ?string $prefix = null,
        string $separator = '-',
        int $digits = 3,
        ?callable $normalize = null,
        ?callable $scope = null,
        ?callable $format = null,
        ?string $ignoreId = null,
        int $maxIterations = 1000
    ): UniqueCodeResult {
        $requested = $this->normalize($requestedCode, $normalize);
        $candidate = $requested ?: $this->formatCode((string) $prefix, 1, $digits, $separator, $format);

        if (! $this->exists($model, $field, $candidate, $scope, $ignoreId)) {
            return new UniqueCodeResult($requested, $candidate, false);
        }

        [$base, $number, $width] = $this->splitCode($candidate, (string) $prefix, $separator, $digits);
        $usedNumbers = $this->usedNumbers($model, $field, $base, $scope, $ignoreId);
        $start = max($number, empty($usedNumbers) ? 0 : max($usedNumbers)) + 1;

        for ($i = 0; $i < $maxIterations; $i++) {
            $next = $this->formatCode($base, $start + $i, $width, $separator, $format);

            if (! $this->exists($model, $field, $next, $scope, $ignoreId)) {
                $result = new UniqueCodeResult($requested, $next, true, 'already_used');

                Log::info('Unique code automatically replaced', [
                    'model' => $model,
                    'field' => $field,
                    'requested_code' => $requested,
                    'resolved_code' => $next,
                    'user_id' => auth()->id(),
                ]);

                return $result;
            }
        }

        throw new \RuntimeException("Unable to resolve a unique {$field} after {$maxIterations} attempts.");
    }

    public function runWithDuplicateRetry(callable $callback, int $attempts = 5): mixed
    {
        beginning:
        try {
            return $callback();
        } catch (QueryException $exception) {
            if (--$attempts <= 0 || ! $this->isDuplicateKey($exception)) {
                throw $exception;
            }

            goto beginning;
        }
    }

    public function isDuplicateKey(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $driverCode = (string) ($exception->errorInfo[1] ?? '');

        return in_array($sqlState, ['23000', '23505'], true)
            || in_array($driverCode, ['1062', '1555', '2067'], true);
    }

    private function normalize(?string $code, ?callable $normalize): ?string
    {
        if ($code === null) {
            return null;
        }

        $code = trim($code);

        if ($normalize) {
            $code = (string) $normalize($code);
        }

        return $code === '' ? null : $code;
    }

    private function splitCode(string $code, string $prefix, string $separator, int $digits): array
    {
        if ($prefix !== '' && str_starts_with($code, $prefix)) {
            $suffix = substr($code, strlen($prefix));
            if (preg_match('/^(\d+)$/', $suffix, $matches)) {
                return [$prefix, (int) $matches[1], strlen($matches[1])];
            }
        }

        if (preg_match('/^(.*?)(\d+)$/', $code, $matches)) {
            return [$matches[1], (int) $matches[2], strlen($matches[2])];
        }

        $base = $code !== '' ? $code . $separator : $prefix;

        return [$base, 0, $digits];
    }

    private function formatCode(string $base, int $number, int $digits, string $separator, ?callable $format): string
    {
        if ($format) {
            return (string) $format($number, $base, $digits);
        }

        if ($base === '') {
            return str_pad((string) $number, $digits, '0', STR_PAD_LEFT);
        }

        return $base . str_pad((string) $number, $digits, '0', STR_PAD_LEFT);
    }

    private function usedNumbers(string $model, string $field, string $base, ?callable $scope, ?string $ignoreId): array
    {
        $codes = $this->baseQuery($model, $scope, $ignoreId)
            ->where($field, 'like', $base . '%')
            ->pluck($field);

        $numbers = [];
        $pattern = '/^' . preg_quote($base, '/') . '(\d+)$/';

        foreach ($codes as $code) {
            if (preg_match($pattern, (string) $code, $matches)) {
                $numbers[] = (int) $matches[1];
            }
        }

        return $numbers;
    }

    private function exists(string $model, string $field, string $code, ?callable $scope, ?string $ignoreId): bool
    {
        return $this->baseQuery($model, $scope, $ignoreId)
            ->where($field, $code)
            ->exists();
    }

    private function baseQuery(string $model, ?callable $scope, ?string $ignoreId): Builder
    {
        /** @var Model $instance */
        $instance = new $model;
        $query = $model::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($model), true)) {
            $query->withTrashed();
        }

        if ($ignoreId !== null) {
            $query->where($instance->getKeyName(), '!=', $ignoreId);
        }

        if ($scope) {
            $scope($query);
        }

        return $query;
    }
}
