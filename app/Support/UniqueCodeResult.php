<?php

namespace App\Support;

final class UniqueCodeResult
{
    public function __construct(
        public readonly ?string $requestedCode,
        public readonly string $resolvedCode,
        public readonly bool $wasChanged,
        public readonly string $reason = 'available'
    ) {}

    public function flashPayload(): array
    {
        return [
            'requested' => $this->requestedCode,
            'resolved' => $this->resolvedCode,
            'reason' => $this->reason,
        ];
    }
}
