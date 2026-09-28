<?php

namespace App\Models\Master;

use App\Models\Data\Route;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'customer';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'officeAddress',
        'billingAddress',
        'phone',
        'accountNumber',
        'ppn',
        'pph',
        'pphBaseType',
        'invoiceFormat',
        'nickname',
        'picName',
        'email',
        'npwp',
        'telegramUsername',
        'dueDateDuration',
        'companyCode',
        'type',
        'isDo',
        'invoicePdf',
        'overdueInvoice',
    ];

    public function routes()
    {
        return $this->hasMany(Route::class, 'customerCode', 'code');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'companyCode', 'code');
    }

    public function details()
    {
        return $this->hasMany(CustomerDetail::class, 'customerCode', 'code');
    }

    public function pic()
    {
        return $this->hasMany(CustomerPic::class, 'customerCode', 'code');
    }

    public function invoices()
    {
        return $this->hasMany(\App\Models\Finance\Invoice::class, 'customerCode', 'code');
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Operational\Order::class, 'customerCode', 'code');
    }

    public function paymentTransactions()
    {
        return $this->hasMany(\App\Models\Finance\InvoicePaymentTransaction::class, 'customerCode', 'code');
    }

    /**
     * Ringkasan transaksi yang berelasi dengan customer ini.
     * Meliputi order operasional, faktur tagihan (invoice), dan transaksi pembayaran invoice.
     */
    public function getTransactionSummary(): array
    {
        $ordersCount = \App\Models\Operational\Order::withTrashed()->where('customerCode', $this->code)->count();
        $invoicesCount = \App\Models\Finance\Invoice::withTrashed()->where('customerCode', $this->code)->count();
        $paymentsCount = \App\Models\Finance\InvoicePaymentTransaction::withTrashed()->where('customerCode', $this->code)->count();
        $total = $ordersCount + $invoicesCount + $paymentsCount;

        return [
            'has_transactions' => $total > 0,
            'orders' => $ordersCount,
            'invoices' => $invoicesCount,
            'payments' => $paymentsCount,
            'total' => $total,
        ];
    }

    /**
     * Cek apakah customer sudah memiliki riwayat transaksi apapun.
     */
    public function hasTransactions(): bool
    {
        return \App\Models\Operational\Order::withTrashed()->where('customerCode', $this->code)->exists()
            || \App\Models\Finance\Invoice::withTrashed()->where('customerCode', $this->code)->exists()
            || \App\Models\Finance\InvoicePaymentTransaction::withTrashed()->where('customerCode', $this->code)->exists();
    }
}

