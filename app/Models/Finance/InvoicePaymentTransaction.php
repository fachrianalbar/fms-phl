<?php

namespace App\Models\Finance;

use App\Models\Bank\UserBank;
use App\Models\Master\Customer;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoicePaymentTransaction extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'invoice_payment_transaction';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'paymentDate',
        'customerCode',
        'userBankCode',
        'amount',
        'totalClaim',
        'description',
        'paymentReceipt',
    ];

    protected $casts = [
        'amount' => 'integer',
        'totalClaim' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerCode', 'code');
    }

    public function userBank()
    {
        return $this->belongsTo(UserBank::class, 'userBankCode', 'code');
    }

    /**
     * Alokasi pembayaran per invoice di dalam transaksi ini.
     */
    public function payments()
    {
        return $this->hasMany(InvoicePayment::class, 'transactionCode', 'code');
    }

    /**
     * Claim (biaya lain-lain pengurang tagihan) di dalam transaksi ini.
     */
    public function claims()
    {
        return $this->hasMany(InvoicePaymentClaim::class, 'transactionCode', 'code');
    }

    /**
     * Kumpulan kode invoice unik yang terlibat dalam transaksi ini
     * (gabungan dari alokasi pembayaran dan claim).
     */
    public function involvedInvoiceCodes()
    {
        return $this->payments->pluck('invoiceCode')
            ->merge($this->claims->pluck('invoiceCode'))
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Kumpulan invoice unik yang terlibat dalam transaksi ini.
     */
    public function involvedInvoices()
    {
        $invoices = collect();

        foreach ($this->payments as $payment) {
            if ($payment->invoice && ! $invoices->has($payment->invoiceCode)) {
                $invoices->put($payment->invoiceCode, $payment->invoice);
            }
        }

        foreach ($this->claims as $claim) {
            if ($claim->invoice && ! $invoices->has($claim->invoiceCode)) {
                $invoices->put($claim->invoiceCode, $claim->invoice);
            }
        }

        return $invoices->values();
    }
}
