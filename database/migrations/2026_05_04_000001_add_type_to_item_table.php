<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToItemTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('item')) {
            Schema::table('item', function (Blueprint $table) {
                if (! Schema::hasColumn('item', 'type')) {
                    $table->string('type', 20)->default('part')->after('unitCode');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('item') && Schema::hasColumn('item', 'type')) {
            Schema::table('item', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
}
