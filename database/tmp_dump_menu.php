<?php

use Illuminate\Support\Facades\DB;

$rows = DB::table('menu')->orderBy('parentCode')->orderBy('sort')->get(['code', 'parentCode', 'name', 'nama', 'url', 'icon']);

foreach ($rows as $m) {
    echo str_pad($m->parentCode, 42) . ' | icon: ' . str_pad((string) $m->icon, 18) . ' | ' . str_pad($m->name, 34) . ' | ' . $m->url . PHP_EOL;
}
