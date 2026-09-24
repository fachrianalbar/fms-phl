<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * MenuSeeder — struktur menu ERP (2 level) untuk FMS PHL.
 *
 * Prinsip:
 *  - Grup level-1 mengikuti alur bisnis (Master → Operasional → Supplier →
 *    Inventory → Piutang → Hutang → Pembayaran → Bank → Laporan → Administrator).
 *  - `url` TIDAK diubah (harus cocok dengan route/controller).
 *  - Kolom `name` menu yang di-lookup via MenuService::getByName() TIDAK diubah
 *    (Order, Route, Customer, Fleet, Fleet Company, Location, Not Return Do,
 *     Return Do, Purchase, Purchase Payment, Maintenance).
 *  - Kode menu diseragamkan (SCREAMING_SNAKE_CASE), kode auto `MNxxxx` &
 *    `ORDER-DETAIL` diganti.
 *  - Parent `DATA` & `WAREHOUSE_PARENT` digabung, `FINANCE` (kosong) dihapus.
 *  - Idempotent: aman dijalankan berulang.
 *
 * Relasi role_menu untuk kode lama/rename ditangani oleh RoleMenuSeeder.
 */
class MenuSeeder extends Seeder
{
    /**
     * Parent menu (level-1). parentCode selalu '0', url selalu '#'.
     *
     * @var array<int, array{code:string,name:string,nama:string,icon:string,sort:int}>
     */
    private array $parents = [
        ['code' => 'MASTER', 'name' => 'Master', 'nama' => 'Data Master', 'icon' => 'table', 'sort' => 1],
        ['code' => 'OPERATIONAL', 'name' => 'Operational', 'nama' => 'Operasional', 'icon' => 'file-text', 'sort' => 2],
        ['code' => 'SUPPLIER_GROUP', 'name' => 'Supplier', 'nama' => 'Supplier', 'icon' => 'shopping-bag', 'sort' => 3],
        ['code' => 'INVENTORY', 'name' => 'Inventory', 'nama' => 'Inventori', 'icon' => 'package', 'sort' => 4],
        ['code' => 'FAKTUR', 'name' => 'Accounts Receivable (Invoice)', 'nama' => 'Piutang (Faktur)', 'icon' => 'file-text', 'sort' => 5],
        ['code' => 'VENDOR', 'name' => 'Accounts Payable (Vendor)', 'nama' => 'Hutang (Vendor)', 'icon' => 'truck', 'sort' => 6],
        ['code' => 'DIRECT_PAYMENT', 'name' => 'Direct Payment', 'nama' => 'Pembayaran Langsung', 'icon' => 'credit-card', 'sort' => 7],
        ['code' => 'BANK', 'name' => 'Cash & Bank', 'nama' => 'Kas & Bank', 'icon' => 'book-open', 'sort' => 8],
        ['code' => 'REPORT', 'name' => 'Reports', 'nama' => 'Laporan', 'icon' => 'clipboard', 'sort' => 9],
        ['code' => 'ADMINISTRATOR', 'name' => 'System', 'nama' => 'Sistem', 'icon' => 'users', 'sort' => 10],
    ];

    /**
     * Child menu (level-2).
     *
     * @var array<int, array{code:string,name:string,nama:string,parentCode:string,url:string,sort:int}>
     */
    private array $children = [
        // ── MASTER (Master Data) ──────────────────────────────────────────
        ['code' => 'CUSTOMER', 'name' => 'Customer', 'nama' => 'Pelanggan', 'parentCode' => 'MASTER', 'url' => 'master/customer', 'sort' => 1],
        ['code' => 'FLEET_BRAND', 'name' => 'Fleet Brand', 'nama' => 'Merek Armada', 'parentCode' => 'MASTER', 'url' => 'master/fleet-brand', 'sort' => 2],
        ['code' => 'FLEET_TYPE', 'name' => 'Fleet Type', 'nama' => 'Tipe Armada', 'parentCode' => 'MASTER', 'url' => 'master/fleet-type', 'sort' => 3],
        ['code' => 'FLEET', 'name' => 'Fleet', 'nama' => 'Armada', 'parentCode' => 'MASTER', 'url' => 'master/fleets', 'sort' => 4],
        ['code' => 'FLEET_COMPANY', 'name' => 'Fleet Company', 'nama' => 'Perusahaan Armada', 'parentCode' => 'MASTER', 'url' => 'master/fleet-company', 'sort' => 5],
        ['code' => 'EMPLOYEE', 'name' => 'Employee', 'nama' => 'Karyawan', 'parentCode' => 'MASTER', 'url' => 'master/employee', 'sort' => 6],
        ['code' => 'LOCATION', 'name' => 'Location', 'nama' => 'Lokasi', 'parentCode' => 'MASTER', 'url' => 'master/location', 'sort' => 7],
        ['code' => 'ROUTE', 'name' => 'Route', 'nama' => 'Rute', 'parentCode' => 'MASTER', 'url' => 'data/route', 'sort' => 8],
        ['code' => 'MATERIAL', 'name' => 'Material', 'nama' => 'Material', 'parentCode' => 'MASTER', 'url' => 'master/material', 'sort' => 9],
        ['code' => 'UNIT', 'name' => 'Unit', 'nama' => 'Unit', 'parentCode' => 'MASTER', 'url' => 'master/unit', 'sort' => 10],
        ['code' => 'COST_COMPONENT', 'name' => 'Cost Component', 'nama' => 'Komponen Biaya', 'parentCode' => 'MASTER', 'url' => 'master/cost-component', 'sort' => 11],
        ['code' => 'COST_COMPONENT_LOG', 'name' => 'Cost Component Price Log', 'nama' => 'Riwayat Harga Komponen', 'parentCode' => 'MASTER', 'url' => 'master/cost-component-price-log', 'sort' => 12],
        ['code' => 'COMPANY', 'name' => 'Company', 'nama' => 'Perusahaan', 'parentCode' => 'MASTER', 'url' => 'master/company', 'sort' => 13],

        // ── OPERATIONAL ───────────────────────────────────────────────────
        ['code' => 'ORDER', 'name' => 'Order', 'nama' => 'Pesanan', 'parentCode' => 'OPERATIONAL', 'url' => 'operational/order', 'sort' => 1],
        ['code' => 'NOT_RETURN_DO', 'name' => 'Not Return Do', 'nama' => 'DO Belum Kembali', 'parentCode' => 'OPERATIONAL', 'url' => 'operational/not-return-do', 'sort' => 2],
        ['code' => 'RETURN_DO', 'name' => 'Return Do', 'nama' => 'DO Kembali', 'parentCode' => 'OPERATIONAL', 'url' => 'operational/return-do', 'sort' => 3],

        // ── SUPPLIER ──────────────────────────────────────────────────
        ['code' => 'SUPPLIER', 'name' => 'Supplier', 'nama' => 'Data Supplier', 'parentCode' => 'SUPPLIER_GROUP', 'url' => 'inventory/supplier', 'sort' => 1],
        ['code' => 'PURCHASE', 'name' => 'Purchase', 'nama' => 'Pembelian', 'parentCode' => 'SUPPLIER_GROUP', 'url' => 'purchasing/purchase', 'sort' => 2],
        ['code' => 'DIRECT_PURCHASE', 'name' => 'Direct Purchase', 'nama' => 'Pembelian Langsung', 'parentCode' => 'SUPPLIER_GROUP', 'url' => 'purchasing/direct-purchase', 'sort' => 3],
        ['code' => 'PURCHASE_PAYMENT', 'name' => 'Purchase Payment', 'nama' => 'Hutang Belum Lunas', 'parentCode' => 'SUPPLIER_GROUP', 'url' => 'purchasing/purchase-payment', 'sort' => 4],
        ['code' => 'PURCHASE_PAID', 'name' => 'Supplier Paid', 'nama' => 'Hutang Lunas', 'parentCode' => 'SUPPLIER_GROUP', 'url' => 'purchasing/purchase-paid', 'sort' => 5],
        ['code' => 'SUPPLIER_PURCHASE', 'name' => 'Supplier Purchase Report', 'nama' => 'Laporan Supplier', 'parentCode' => 'SUPPLIER_GROUP', 'url' => 'report/supplier', 'sort' => 6],

        // ── INVENTORY ─────────────────────────────────────────────────────
        ['code' => 'ITEM', 'name' => 'Item', 'nama' => 'Barang', 'parentCode' => 'INVENTORY', 'url' => 'inventory/items', 'sort' => 1],
        ['code' => 'ITEM_CATEGORY', 'name' => 'Item Category', 'nama' => 'Kategori Barang', 'parentCode' => 'INVENTORY', 'url' => 'inventory/item-category', 'sort' => 2],
        ['code' => 'ITEM_UNIT', 'name' => 'Item Unit', 'nama' => 'Satuan Barang', 'parentCode' => 'INVENTORY', 'url' => 'inventory/item-unit', 'sort' => 3],
        ['code' => 'WAREHOUSE', 'name' => 'Warehouse', 'nama' => 'Gudang', 'parentCode' => 'INVENTORY', 'url' => 'inventory/warehouse', 'sort' => 4],
        ['code' => 'STOCK', 'name' => 'Stock', 'nama' => 'Stok', 'parentCode' => 'INVENTORY', 'url' => 'inventory/stock', 'sort' => 5],
        ['code' => 'STOCK_TRANSACTION', 'name' => 'Stock Card', 'nama' => 'Kartu Stok', 'parentCode' => 'INVENTORY', 'url' => 'inventory/transaction-stock', 'sort' => 6],
        ['code' => 'STOCK_SYNC', 'name' => 'Stock Sync', 'nama' => 'Sinkronisasi Stok', 'parentCode' => 'INVENTORY', 'url' => 'inventory/stock-sync', 'sort' => 7],
        ['code' => 'MAINTENANCE', 'name' => 'Maintenance', 'nama' => 'Perawatan', 'parentCode' => 'INVENTORY', 'url' => 'warehouse/maintenance', 'sort' => 8],

        // ── FAKTUR (Piutang / AR) ─────────────────────────────────────────
        ['code' => 'FAKTUR_PEMBUATAN', 'name' => 'Create Invoice', 'nama' => 'Buat Faktur', 'parentCode' => 'FAKTUR', 'url' => 'invoice/create', 'sort' => 1],
        ['code' => 'FAKTUR_BELUM_LUNAS', 'name' => 'Unpaid Invoice', 'nama' => 'Faktur Belum Lunas', 'parentCode' => 'FAKTUR', 'url' => 'invoice/unpaid', 'sort' => 2],
        ['code' => 'FAKTUR_LUNAS', 'name' => 'Paid Invoice', 'nama' => 'Faktur Lunas', 'parentCode' => 'FAKTUR', 'url' => 'invoice/paid', 'sort' => 3],
        ['code' => 'FAKTUR_PEMBAYARAN', 'name' => 'Invoice Payment', 'nama' => 'Pembayaran Faktur', 'parentCode' => 'FAKTUR', 'url' => 'invoice/payment', 'sort' => 4],
        ['code' => 'FAKTUR_TRANSAKSI_PEMBAYARAN', 'name' => 'Payment Transaction', 'nama' => 'Transaksi Pembayaran', 'parentCode' => 'FAKTUR', 'url' => 'invoice/payment-transaction', 'sort' => 5],

        // ── VENDOR (Hutang / AP) ──────────────────────────────────────────
        ['code' => 'VENDOR_ORDER_WAITING', 'name' => 'Order Awaiting Invoice', 'nama' => 'Order Menunggu Faktur', 'parentCode' => 'VENDOR', 'url' => 'vendor/order/waiting', 'sort' => 1],
        ['code' => 'VENDOR_INV_UNPAID', 'name' => 'Unpaid Vendor Invoice', 'nama' => 'Faktur Vendor Belum Lunas', 'parentCode' => 'VENDOR', 'url' => 'vendor/invoice/unpaid', 'sort' => 2],
        ['code' => 'VENDOR_INV_PAID', 'name' => 'Paid Vendor Invoice', 'nama' => 'Faktur Vendor Lunas', 'parentCode' => 'VENDOR', 'url' => 'vendor/invoice/paid', 'sort' => 3],
        ['code' => 'VENDOR_PAY_LIST', 'name' => 'Vendor Payment List', 'nama' => 'Daftar Pembayaran Vendor', 'parentCode' => 'VENDOR', 'url' => 'vendor/payment', 'sort' => 4],

        // ── DIRECT_PAYMENT ────────────────────────────────────────────────
        ['code' => 'DIRECT_PAYMENT_UNPAID', 'name' => 'Unpaid Direct Payment', 'nama' => 'Pembayaran Belum Lunas', 'parentCode' => 'DIRECT_PAYMENT', 'url' => 'direct-payment/order/unpaid', 'sort' => 1],
        ['code' => 'DIRECT_PAYMENT_PAID', 'name' => 'Paid Direct Payment', 'nama' => 'Pembayaran Lunas', 'parentCode' => 'DIRECT_PAYMENT', 'url' => 'direct-payment/order/paid', 'sort' => 2],
        ['code' => 'DIRECT_PAYMENT_LIST', 'name' => 'Direct Payment List', 'nama' => 'Daftar Pembayaran', 'parentCode' => 'DIRECT_PAYMENT', 'url' => 'direct-payment/payment', 'sort' => 3],

        // ── BANK (Kas & Bank) ─────────────────────────────────────────────
        ['code' => 'BANK_ACCOUNT', 'name' => 'Bank Account', 'nama' => 'Rekening Bank', 'parentCode' => 'BANK', 'url' => 'bank/bank-account', 'sort' => 1],
        ['code' => 'USER_BANK', 'name' => 'User Bank', 'nama' => 'Bank Pengguna', 'parentCode' => 'BANK', 'url' => 'bank/user-bank', 'sort' => 2],
        ['code' => 'BANK_BOOK', 'name' => 'Bank Book', 'nama' => 'Buku Bank', 'parentCode' => 'BANK', 'url' => 'bank/bank-book', 'sort' => 3],

        // ── REPORT ────────────────────────────────────────────────────────
        ['code' => 'ORDER_DETAIL', 'name' => 'Order Detail', 'nama' => 'Detail Order', 'parentCode' => 'REPORT', 'url' => 'report/order-detail', 'sort' => 1],
        ['code' => 'PROFIT_LOSS', 'name' => 'Profit & Loss', 'nama' => 'Laba & Rugi', 'parentCode' => 'REPORT', 'url' => 'report/profit-loss', 'sort' => 2],
        ['code' => 'DRIVER_SALARY', 'name' => 'Driver Salary', 'nama' => 'Gaji Sopir', 'parentCode' => 'REPORT', 'url' => 'report/driver-salary', 'sort' => 3],
        ['code' => 'MAINTENANCE_FLEET', 'name' => 'Maintenance Per Fleet', 'nama' => 'Perawatan Per Armada', 'parentCode' => 'REPORT', 'url' => 'report/maintenance-fleet', 'sort' => 4],
        ['code' => 'MAINTENANCE_COMPANY', 'name' => 'Maintenance Per Fleet Company', 'nama' => 'Perawatan Per Perusahaan', 'parentCode' => 'REPORT', 'url' => 'report/maintenance-company-internal', 'sort' => 5],

        // ── ADMINISTRATOR ─────────────────────────────────────────────────
        ['code' => 'USER', 'name' => 'User', 'nama' => 'Pengguna', 'parentCode' => 'ADMINISTRATOR', 'url' => 'administrator/user', 'sort' => 1],
        ['code' => 'ROLE', 'name' => 'Role', 'nama' => 'Peran', 'parentCode' => 'ADMINISTRATOR', 'url' => 'administrator/role', 'sort' => 2],
        ['code' => 'MASTER_MENU', 'name' => 'Menu', 'nama' => 'Menu', 'parentCode' => 'ADMINISTRATOR', 'url' => 'master/menu', 'sort' => 3],
        ['code' => 'ACTIVITY_LOG', 'name' => 'Activity Log', 'nama' => 'Log Aktivitas', 'parentCode' => 'ADMINISTRATOR', 'url' => 'administrator/activity-log', 'sort' => 4],
    ];

    public function run(): void
    {
        $now = Carbon::now();

        // 1. Kumpulkan kode target (parent + child).
        $targetCodes = array_merge(
            array_column($this->parents, 'code'),
            array_column($this->children, 'code'),
        );

        // 2. Hapus PERMANEN semua menu yang tidak ada di struktur target.
        //    Ini menyapu menu lama, menu obsolete (parent digabung / kode
        //    di-rename / tanpa route), dan menu soft-deleted (SETTING,
        //    CHANGE_PASS, TRANSFER_FUND, dll).
        //
        //    PENTING: hanya menyentuh tabel `menu`. Tabel `role_menu`
        //    sepenuhnya ditangani RoleMenuSeeder (agar akses role tidak
        //    hilang sebelum sempat dipetakan).
        DB::table('menu')->whereNotIn('code', $targetCodes)->delete();

        // 3. Normalisasi: pastikan 1 baris per kode menu (jaga-jaga bila ada
        //    kode duplikat lama, mis. parent & child yang sama-sama 'BANK').
        $duplicates = DB::select('SELECT code FROM menu GROUP BY code HAVING COUNT(*) > 1');
        foreach ($duplicates as $duplicate) {
            $ids = DB::table('menu')->where('code', $duplicate->code)->orderBy('created_at')->pluck('id')->all();
            array_shift($ids); // simpan baris paling awal
            if ($ids !== []) {
                DB::table('menu')->whereIn('id', $ids)->delete();
            }
        }

        // 4. Upsert parent menu.
        foreach ($this->parents as $parent) {
            $this->upsertMenu($parent, '0', '#', $now);
        }

        // 5. Upsert child menu.
        foreach ($this->children as $child) {
            $this->upsertMenu($child, $child['parentCode'], $child['url'], $now);
        }
    }

    /**
     * Insert/update satu baris menu (termasuk reset deleted_at bila sebelumnya
     * di-soft-delete), tanpa menyentuh kolom yang tidak relevan.
     *
     * @param  array<string, mixed>  $menu
     */
    private function upsertMenu(array $menu, string $parentCode, string $url, Carbon $now): void
    {
        $payload = [
            'name' => $menu['name'],
            'nama' => $menu['nama'],
            'parentCode' => $parentCode,
            'url' => $url,
            'icon' => $menu['icon'] ?? null,
            'sort' => $menu['sort'],
            'deleted_at' => null,
            'updated_at' => $now,
        ];

        $exists = DB::table('menu')->where('code', $menu['code'])->exists();

        if ($exists) {
            DB::table('menu')->where('code', $menu['code'])->update($payload);
        } else {
            DB::table('menu')->insert(array_merge($payload, [
                'id' => (string) Str::uuid(),
                'code' => $menu['code'],
                'created_at' => $now,
            ]));
        }
    }
}
