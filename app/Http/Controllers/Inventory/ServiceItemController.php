<?php

namespace App\Http\Controllers\Inventory;

use App\Models\Inventory\Item;
use App\Services\Inventory\ItemCategoryService;
use App\Services\Inventory\ItemService;
use App\Services\Inventory\SupplierService;
use App\Services\Inventory\WarehouseService;
use App\Services\Master\UnitService;

class ServiceItemController extends ItemController
{
    protected string $itemType = Item::TYPE_JASA;

    protected string $routePrefix = 'inventory.jasa.';

    protected string $dtRoute = 'dt.jasa';

    protected string $entityLabel = 'Jasa';

    protected string $exportFilePrefix = 'Data-Jasa';

    protected string $pdfReportTitle = 'Laporan Master Jasa';

    public function __construct(
        ItemService $itemSvc,
        UnitService $unitSvc,
        ItemCategoryService $categorySvc,
        WarehouseService $warehouseSvc,
        SupplierService $supplierSvc
    ) {
        parent::__construct($itemSvc, $unitSvc, $categorySvc, $warehouseSvc, $supplierSvc);
        $this->title = 'Jasa';
    }
}
