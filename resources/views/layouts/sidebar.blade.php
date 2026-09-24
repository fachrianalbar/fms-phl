@php
    // Fallback icon feather untuk parent yang tidak punya icon di DB
    $parentIconFallbacks = [
        'FAKTUR' => 'file-text',
    ];

    /*
     * Icon submenu per URL (Material Design Icons).
     * Icon pada tabel menu berformat Boxicons dan tidak tersedia CSS-nya,
     * jadi icon submenu dipetakan lokal berdasarkan URL menu.
     */
    $childIcons = [
        // Administrator
        'administrator/user' => 'mdi-account-outline',
        'administrator/role' => 'mdi-shield-account-outline',
        'administrator/activity-log' => 'mdi-history',
        'master/menu' => 'mdi-menu',

        // Bank
        'bank/bank-account' => 'mdi-bank-outline',
        'bank/user-bank' => 'mdi-account-cash-outline',
        'bank/bank-book' => 'mdi-book-open-variant',

        // Faktur (Piutang / AR)
        'invoice/create' => 'mdi-file-plus-outline',
        'invoice/unpaid' => 'mdi-cash-remove',
        'invoice/partial' => 'mdi-cash-clock',
        'invoice/paid' => 'mdi-cash-check',
        'invoice/payment' => 'mdi-credit-card-outline',
        'invoice/payment-transaction' => 'mdi-receipt-text-check-outline',

        // Vendor (Hutang / AP)
        'vendor/order/waiting' => 'mdi-tray-full',
        'vendor/invoice/unpaid' => 'mdi-cash-remove',
        'vendor/invoice/partial' => 'mdi-cash-clock',
        'vendor/invoice/paid' => 'mdi-cash-check',
        'vendor/payment' => 'mdi-credit-card-outline',

        // Pembayaran Langsung
        'direct-payment/order/unpaid' => 'mdi-cash-remove',
        'direct-payment/order/paid' => 'mdi-cash-check',
        'direct-payment/payment' => 'mdi-credit-card-outline',

        // Inventory & Gudang
        'inventory/items' => 'mdi-package-variant-closed',
        'inventory/item-category' => 'mdi-tag-multiple-outline',
        'inventory/item-unit' => 'mdi-counter',
        'inventory/warehouse' => 'mdi-warehouse',
        'inventory/stock' => 'mdi-clipboard-list-outline',
        'inventory/transaction-stock' => 'mdi-card-text-outline',
        'inventory/stock-sync' => 'mdi-cloud-sync-outline',
        'warehouse/maintenance' => 'mdi-tools',

        // Master Data
        'master/customer' => 'mdi-account-multiple-outline',
        'master/fleet-brand' => 'mdi-truck-flatbed',
        'master/fleet-type' => 'mdi-truck-cargo-container',
        'master/fleets' => 'mdi-truck-delivery-outline',
        'master/fleet-company' => 'mdi-truck-check-outline',
        'master/employee' => 'mdi-account-group-outline',
        'master/location' => 'mdi-map-marker-outline',
        'data/route' => 'mdi-routes',
        'master/material' => 'mdi-cube-outline',
        'master/unit' => 'mdi-ruler-square',
        'master/cost-component' => 'mdi-currency-usd',
        'master/cost-component-price-log' => 'mdi-history',
        'master/company' => 'mdi-office-building-outline',

        // Operational
        'operational/order' => 'mdi-clipboard-text-multiple-outline',
        'operational/not-return-do' => 'mdi-file-cancel-outline',
        'operational/return-do' => 'mdi-file-restore-outline',

        // Supplier
        'inventory/supplier' => 'mdi-factory',
        'purchasing/purchase' => 'mdi-cart-outline',
        'purchasing/purchase-payment' => 'mdi-cash-remove',
        'purchasing/purchase-paid' => 'mdi-cash-check',
        'report/supplier' => 'mdi-chart-timeline-variant',

        // Report
        'report/order-detail' => 'mdi-chart-bar',
        'report/profit-loss' => 'mdi-finance',
        'report/driver-salary' => 'mdi-cash',
        'report/maintenance-fleet' => 'mdi-wrench-clock',
        'report/maintenance-company-internal' => 'mdi-domain',
    ];
@endphp

<ul id="side-menu" class="phl-nav">

    <li class="menu-title">Menu</li>

    <li class="{{ request()->is('home', 'home/*') ? 'menuitem-active' : '' }}">
        <a href="{{ url('/home') }}" class="tp-link {{ request()->is('home', 'home/*') ? 'active' : '' }}">
            <span class="phl-tile"><i data-feather="home"></i></span>
            <span class="phl-label">{{ auth()->user()->languange == 'id' ? 'Beranda' : 'Home' }}</span>
        </a>
    </li>

    @foreach ($menus->where('parentCode', '0') as $parent)
        @php
            $children = $menus->where('parentCode', $parent->code);
            $hasSubMenu = $children->isNotEmpty();
            $hasActiveChild = $children->contains(function ($child) {
                $childUrl = trim($child->url, '/');
                return request()->is($childUrl) || request()->is($childUrl . '/*') || request()->is($childUrl . '-edit/*');
            });
            $parentUrl = trim($parent->url, '/');
            $isParentActive = ($parentUrl !== '' && (request()->is($parentUrl) || request()->is($parentUrl . '/*') || request()->is($parentUrl . '-edit/*'))) || $hasActiveChild;
            $parentIcon = $parent->icon ?: ($parentIconFallbacks[$parent->code] ?? '');
        @endphp

        <li class="{{ $isParentActive ? 'menuitem-active' : '' }}">
            <a
                @if ($hasSubMenu)
                    href="#menu-{{ $parent->code }}"
                    data-bs-toggle="collapse"
                    aria-expanded="{{ $hasActiveChild ? 'true' : 'false' }}"
                @else
                    href="{{ url('/' . $parent->url) }}"
                @endif
                class="{{ $isParentActive && !$hasSubMenu ? 'active' : '' }}">
                <span class="phl-tile">
                    @if ($parentIcon !== '')
                        <i data-feather="{{ $parentIcon }}"></i>
                    @endif
                </span>
                <span class="phl-label">{{ auth()->user()->languange == 'id' ? $parent->nama : $parent->name }}</span>
                @if ($hasSubMenu)
                    <span class="menu-arrow"></span>
                @endif
            </a>

            @if ($hasSubMenu)
                <div class="collapse {{ $hasActiveChild ? 'show' : '' }}" id="menu-{{ $parent->code }}">
                    <ul class="nav-second-level">
                        @foreach ($children as $child)
                            @php
                                $childUrl = trim($child->url, '/');
                                $isChildActive = request()->is($childUrl) || request()->is($childUrl . '/*') || request()->is($childUrl . '-edit/*');
                            @endphp
                            <li class="{{ $isChildActive ? 'menuitem-active' : '' }}">
                                <a href="{{ url('/' . $child->url) }}" class="tp-link {{ $isChildActive ? 'active' : '' }}">
                                    <i class="mdi phl-sub-icon {{ $childIcons[$childUrl] ?? 'mdi-circle-small' }}"></i>
                                    <span class="phl-label">{{ auth()->user()->languange == 'id' ? $child->nama : $child->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </li>
    @endforeach

</ul>
