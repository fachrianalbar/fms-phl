<?php

namespace App\Support;

use Carbon\Carbon;
use Throwable;

/**
 * Aturan murni untuk umur piutang (AR aging).
 *
 * Kelas ini sengaja tidak menyentuh database maupun model agar bisa diuji
 * tanpa koneksi dan dipakai bersama oleh laporan, export, dan halaman detail.
 */
final class ReceivableAging
{
    public const NOT_DUE = 'notDue';

    public const DUE_SOON = 'dueSoon';

    public const D0_30 = 'd0_30';

    public const D31_60 = 'd31_60';

    public const D61_90 = 'd61_90';

    public const OVER_90 = 'dOver90';

    /**
     * Jatuh tempo efektif: pakai `overdueDate` bila tahunnya wajar, selain itu
     * `invoiceDate + customer.dueDateDuration` hari, dan terakhir hari ini.
     */
    public static function effectiveDueDate(
        ?string $overdueDate,
        ?string $invoiceDate,
        ?int $dueDateDuration,
        Carbon $today
    ): Carbon {
        if (self::isUsableDate($overdueDate)) {
            return Carbon::parse($overdueDate)->startOfDay();
        }

        if (self::isUsableDate($invoiceDate)) {
            return Carbon::parse($invoiceDate)
                ->startOfDay()
                ->addDays(max((int) $dueDateDuration, 0));
        }

        return $today->copy()->startOfDay();
    }

    /**
     * Selisih hari menuju jatuh tempo; negatif berarti sudah lewat.
     */
    public static function daysToDue(Carbon $dueDate, Carbon $today): int
    {
        return (int) $today->copy()->startOfDay()
            ->diffInDays($dueDate->copy()->startOfDay(), false);
    }

    public static function bucket(int $daysToDue): string
    {
        if ($daysToDue > 7) {
            return self::NOT_DUE;
        }

        if ($daysToDue >= 0) {
            return self::DUE_SOON;
        }

        if ($daysToDue >= -30) {
            return self::D0_30;
        }

        if ($daysToDue >= -60) {
            return self::D31_60;
        }

        if ($daysToDue >= -90) {
            return self::D61_90;
        }

        return self::OVER_90;
    }

    public static function bucketLabel(string $key): string
    {
        return match ($key) {
            self::NOT_DUE => 'Belum jatuh tempo',
            self::DUE_SOON => 'Jatuh tempo ≤ 7 hari',
            self::D0_30 => '1–30 hari',
            self::D31_60 => '31–60 hari',
            self::D61_90 => '61–90 hari',
            self::OVER_90 => '> 90 hari',
            default => '-',
        };
    }

    private static function isUsableDate(?string $date): bool
    {
        if (! $date) {
            return false;
        }

        try {
            $year = Carbon::parse($date)->year;
        } catch (Throwable) {
            return false;
        }

        return $year >= 2020 && $year <= 2030;
    }
}
