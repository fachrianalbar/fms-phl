<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement("
                UPDATE purchase
                SET
                    subtotal = COALESCE(
                        NULLIF(subtotal, 0),
                        COALESCE(NULLIF(nominal, 0), (
                            SELECT SUM(pd.price * COALESCE(pd.qty, pd.receivedQty))
                            FROM purchase_detail pd
                            WHERE pd.purchaseCode = purchase.code AND pd.deleted_at IS NULL
                        ), 0)
                    ),
                    paidAmount = COALESCE(NULLIF(nominal, 0), (
                        SELECT SUM(pd.price * COALESCE(pd.qty, pd.receivedQty))
                        FROM purchase_detail pd
                        WHERE pd.purchaseCode = purchase.code AND pd.deleted_at IS NULL
                    ), 0),
                    nominal = COALESCE(NULLIF(nominal, 0), (
                        SELECT SUM(pd.price * COALESCE(pd.qty, pd.receivedQty))
                        FROM purchase_detail pd
                        WHERE pd.purchaseCode = purchase.code AND pd.deleted_at IS NULL
                    ), 0),
                    paymentStatus = 'Paid',
                    status = 3,
                    paymentDate = COALESCE(
                        paymentDate,
                        CASE WHEN CAST(strftime('%Y', dueDate) AS INTEGER) BETWEEN 2020 AND 2030 THEN dueDate END,
                        date
                    ),
                    paymentCode = COALESCE(NULLIF(paymentCode, ''), 'HIST-PRE-202609')
                WHERE deleted_at IS NULL
                  AND date < '2026-09-01'
                  AND (paymentStatus IS NULL OR paymentStatus != 'Paid')
            ");

            return;
        }

        DB::statement("
            UPDATE purchase p
            LEFT JOIN (
                SELECT purchaseCode, SUM(price * COALESCE(qty, receivedQty)) AS detail_sum
                FROM purchase_detail
                WHERE deleted_at IS NULL
                GROUP BY purchaseCode
            ) ds ON ds.purchaseCode = p.code
            SET
                p.subtotal = COALESCE(NULLIF(p.subtotal, 0), COALESCE(NULLIF(p.nominal, 0), ds.detail_sum, 0)),
                p.paidAmount = COALESCE(NULLIF(p.nominal, 0), ds.detail_sum, 0),
                p.nominal = COALESCE(NULLIF(p.nominal, 0), ds.detail_sum, 0),
                p.paymentStatus = 'Paid',
                p.status = 3,
                p.paymentDate = COALESCE(
                    p.paymentDate,
                    CASE WHEN YEAR(p.dueDate) BETWEEN 2020 AND 2030 THEN p.dueDate END,
                    p.date
                ),
                p.paymentCode = COALESCE(NULLIF(p.paymentCode, ''), 'HIST-PRE-202609')
            WHERE p.deleted_at IS NULL
              AND p.date < '2026-09-01'
              AND (p.paymentStatus IS NULL OR p.paymentStatus != 'Paid')
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('purchase')
            ->where('paymentCode', 'HIST-PRE-202609')
            ->update([
                'paidAmount' => 0,
                'paymentStatus' => 'Unpaid',
                'status' => 0,
                'paymentDate' => null,
                'paymentCode' => null,
            ]);
    }
};
