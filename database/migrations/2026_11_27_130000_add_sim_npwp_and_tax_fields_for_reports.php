<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TAX_COLUMNS = [
        'tax_invoice_number',
        'withholding_tax_number',
        'withholding_tax_amount',
        'withholding_tax_date',
    ];

    public function up(): void
    {
        if (! Schema::hasColumn('employee', 'sim')) {
            Schema::table('employee', function (Blueprint $table) {
                $table->string('sim', 50)->nullable()->after('nik');
            });
        }

        if (! Schema::hasColumn('fleet_company', 'npwp')) {
            Schema::table('fleet_company', function (Blueprint $table) {
                $table->string('npwp', 50)->nullable()->after('name');
            });
        }

        foreach (['invoice', 'vendor_payment'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'tax_invoice_number')) {
                    $table->string('tax_invoice_number', 100)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'withholding_tax_number')) {
                    $table->string('withholding_tax_number', 100)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'withholding_tax_amount')) {
                    $table->decimal('withholding_tax_amount', 15, 2)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'withholding_tax_date')) {
                    $table->date('withholding_tax_date')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['invoice', 'vendor_payment'] as $tableName) {
            $existing = array_values(array_filter(
                self::TAX_COLUMNS,
                fn (string $column) => Schema::hasColumn($tableName, $column)
            ));

            if ($existing !== []) {
                Schema::table($tableName, function (Blueprint $table) use ($existing) {
                    $table->dropColumn($existing);
                });
            }
        }

        if (Schema::hasColumn('fleet_company', 'npwp')) {
            Schema::table('fleet_company', function (Blueprint $table) {
                $table->dropColumn('npwp');
            });
        }

        if (Schema::hasColumn('employee', 'sim')) {
            Schema::table('employee', function (Blueprint $table) {
                $table->dropColumn('sim');
            });
        }
    }
};
