<?php

namespace Tests\Unit;

use App\Support\ReceivableAging;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ReceivableAgingTest extends TestCase
{
    private Carbon $today;

    protected function setUp(): void
    {
        parent::setUp();

        $this->today = Carbon::parse('2026-11-17')->startOfDay();
    }

    public function test_days_to_due_keeps_negative_values_for_overdue_invoices(): void
    {
        $this->assertSame(8, ReceivableAging::daysToDue($this->today->copy()->addDays(8), $this->today));
        $this->assertSame(7, ReceivableAging::daysToDue($this->today->copy()->addDays(7), $this->today));
        $this->assertSame(0, ReceivableAging::daysToDue($this->today->copy(), $this->today));
        $this->assertSame(-1, ReceivableAging::daysToDue($this->today->copy()->subDay(), $this->today));
        $this->assertSame(-30, ReceivableAging::daysToDue($this->today->copy()->subDays(30), $this->today));
        $this->assertSame(-31, ReceivableAging::daysToDue($this->today->copy()->subDays(31), $this->today));
        $this->assertSame(-60, ReceivableAging::daysToDue($this->today->copy()->subDays(60), $this->today));
        $this->assertSame(-61, ReceivableAging::daysToDue($this->today->copy()->subDays(61), $this->today));
        $this->assertSame(-90, ReceivableAging::daysToDue($this->today->copy()->subDays(90), $this->today));
        $this->assertSame(-91, ReceivableAging::daysToDue($this->today->copy()->subDays(91), $this->today));
    }

    public function test_bucket_follows_the_agreed_boundaries(): void
    {
        $this->assertSame(ReceivableAging::NOT_DUE, ReceivableAging::bucket(8));
        $this->assertSame(ReceivableAging::DUE_SOON, ReceivableAging::bucket(7));
        $this->assertSame(ReceivableAging::DUE_SOON, ReceivableAging::bucket(0));
        $this->assertSame(ReceivableAging::D0_30, ReceivableAging::bucket(-1));
        $this->assertSame(ReceivableAging::D0_30, ReceivableAging::bucket(-30));
        $this->assertSame(ReceivableAging::D31_60, ReceivableAging::bucket(-31));
        $this->assertSame(ReceivableAging::D31_60, ReceivableAging::bucket(-60));
        $this->assertSame(ReceivableAging::D61_90, ReceivableAging::bucket(-61));
        $this->assertSame(ReceivableAging::D61_90, ReceivableAging::bucket(-90));
        $this->assertSame(ReceivableAging::OVER_90, ReceivableAging::bucket(-91));
    }

    public function test_effective_due_date_prefers_a_usable_overdue_date(): void
    {
        $dueDate = ReceivableAging::effectiveDueDate('2026-12-01', '2026-11-01', 30, $this->today);

        $this->assertSame('2026-12-01', $dueDate->toDateString());
    }

    public function test_effective_due_date_falls_back_to_invoice_date_plus_customer_duration(): void
    {
        $dueDate = ReceivableAging::effectiveDueDate('0026-01-01', '2026-11-01', 30, $this->today);

        $this->assertSame('2026-12-01', $dueDate->toDateString());
    }

    public function test_effective_due_date_uses_invoice_date_when_duration_is_missing(): void
    {
        $dueDate = ReceivableAging::effectiveDueDate(null, '2026-11-01', null, $this->today);

        $this->assertSame('2026-11-01', $dueDate->toDateString());
    }

    public function test_effective_due_date_uses_today_when_every_date_is_missing(): void
    {
        $dueDate = ReceivableAging::effectiveDueDate(null, null, null, $this->today);

        $this->assertSame('2026-11-17', $dueDate->toDateString());
    }

    public function test_bucket_labels_are_present_for_every_bucket(): void
    {
        foreach ([
            ReceivableAging::NOT_DUE,
            ReceivableAging::DUE_SOON,
            ReceivableAging::D0_30,
            ReceivableAging::D31_60,
            ReceivableAging::D61_90,
            ReceivableAging::OVER_90,
        ] as $bucket) {
            $this->assertNotSame('-', ReceivableAging::bucketLabel($bucket));
        }
    }
}
