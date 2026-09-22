<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Menu yang dihapus karena tidak dipakai.
     */
    private array $menuCodes = [
        'FLEET_OWNER',
        'ITEM_LOCATION',
        'BANK_SENDER',
        'BANK_RECEIVER',
        'DUE_DATE',
        'ORDER_MONITORING',
        'ORDER_OFFICE',
        'DOWN_PAYMENT',
        'PURCHASE_VERIFICATION',
        'PURCHASE_CONFIRMATION',
    ];

    /**
     * Tabel yang ikut di-drop (fitur terkait sudah dihapus dari kode).
     */
    private array $tables = [
        'bank_sender',
        'bank_receiver',
        'due_date',
        'down_payment',
        'down_payment_detail',
        'fleet_driver',
        'item_location',
    ];

    /**
     * Snapshot menu untuk restore pada down().
     */
    private array $menuSnapshot = [
        ['code' => 'FLEET_OWNER', 'name' => 'Fleet Owner', 'nama' => 'Pemilik Armada', 'parentCode' => 'DATA', 'url' => 'data/fleet-owner', 'icon' => 'labour', 'sort' => 1],
        ['code' => 'ITEM_LOCATION', 'name' => 'Item Location', 'nama' => 'Lokasi Barang', 'parentCode' => 'INVENTORY', 'url' => 'inventory/item-location', 'icon' => 'location-arrow', 'sort' => 7],
        ['code' => 'BANK_SENDER', 'name' => 'Bank Sender', 'nama' => 'Pengirim Bank', 'parentCode' => 'MASTER', 'url' => 'master/bank-sender', 'icon' => 'bank-alt', 'sort' => 12],
        ['code' => 'BANK_RECEIVER', 'name' => 'Bank Receiver', 'nama' => 'Penerima Bank', 'parentCode' => 'MASTER', 'url' => 'master/bank-receiver', 'icon' => 'building-alt', 'sort' => 13],
        ['code' => 'DUE_DATE', 'name' => 'Due Date', 'nama' => 'Jatuh Tempo', 'parentCode' => 'MASTER', 'url' => 'master/due-date', 'icon' => null, 'sort' => 14],
        ['code' => 'ORDER_MONITORING', 'name' => 'Order Monitoring', 'nama' => 'Monitoring Pesanan', 'parentCode' => 'OPERATIONAL', 'url' => 'operational/monitoring-order', 'icon' => 'truck', 'sort' => 2],
        ['code' => 'ORDER_OFFICE', 'name' => 'Order Office', 'nama' => 'Kantor Pemesanan', 'parentCode' => 'OPERATIONAL', 'url' => 'operational/office-order', 'icon' => 'ebook', 'sort' => 5],
        ['code' => 'DOWN_PAYMENT', 'name' => 'Down Payment', 'nama' => 'Uang Muka', 'parentCode' => 'OPERATIONAL', 'url' => 'operational/down-payment', 'icon' => 'hand-drag1', 'sort' => 6],
        ['code' => 'PURCHASE_VERIFICATION', 'name' => 'Purchase Verif', 'nama' => 'Verifikasi Pembelian', 'parentCode' => 'PURCHASING', 'url' => 'purchasing/purchase-verification', 'icon' => null, 'sort' => 2],
        ['code' => 'PURCHASE_CONFIRMATION', 'name' => 'Purchase Confirm', 'nama' => 'Konfirmasi Pembelian', 'parentCode' => 'PURCHASING', 'url' => 'purchasing/purchase-confirmation', 'icon' => null, 'sort' => 3],
    ];

    public function up(): void
    {
        // 1. Hapus akses role ke menu yang dihapus
        DB::table('role_menu')->whereIn('menuCode', $this->menuCodes)->delete();

        // 2. Hapus baris menu
        DB::table('menu')->whereIn('code', $this->menuCodes)->delete();

        // 3. Drop tabel fitur yang dihapus
        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }
    }

    /**
     * Reverse: mengembalikan baris menu saja.
     * Tabel yang sudah di-drop tidak dibuat ulang (data historis tidak bisa dipulihkan).
     * Permission role perlu di-grant ulang manual bila diperlukan.
     */
    public function down(): void
    {
        $now = Carbon::now();

        foreach ($this->menuSnapshot as $menu) {
            $exists = DB::table('menu')->where('code', $menu['code'])->exists();
            if (! $exists) {
                DB::table('menu')->insert([
                    'id' => (string) Str::uuid(),
                    'code' => $menu['code'],
                    'name' => $menu['name'],
                    'nama' => $menu['nama'],
                    'parentCode' => $menu['parentCode'],
                    'url' => $menu['url'],
                    'icon' => $menu['icon'],
                    'sort' => $menu['sort'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
