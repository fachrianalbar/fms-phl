@php
    // Fallback icon feather untuk parent yang tidak punya icon di DB
    $parentIconFallbacks = [
        'SETTING' => 'settings',
        'FAKTUR' => 'file-plus',
    ];

    /*
     * Icon submenu per URL (Material Design Icons).
     * Icon pada tabel menu berformat Boxicons dan tidak tersedia CSS-nya,
     * jadi icon submenu dipetakan lokal berdasarkan URL menu.
     */
    $childIcons = [
        '' => 'mdi-lock-outline', // menu tanpa url (mis. Change Password)

        // Administrator
        'administrator/user' => 'mdi-account-outline',
        'administrator/role' => 'mdi-shield-account-outline',
        'administrator/company-setting' => 'mdi-tune',
        'administrator/activity-log' => 'mdi-history',

        // Bank
        'bank/bank-account' => 'mdi-bank-outline',
        'bank/user-bank' => 'mdi-account-cash-outline',
        'bank/config-bank' => 'mdi-cog-outline',
        'bank/transfer-fund' => 'mdi-swap-horizontal',
        'bank/expense' => 'mdi-cash-minus',
        'bank/bank-book' => 'mdi-book-open-variant',

        // Data
        'data/fleet-owner' => 'mdi-account-hard-hat',
        'data/drop-location' => 'mdi-map-marker-down',
        'data/pickup-location' => 'mdi-map-marker-up',
        'data/route' => 'mdi-routes',
        'data/tonase-bonus' => 'mdi-star-outline',

        // Faktur
        'invoice/create' => 'mdi-file-plus-outline',
        'invoice/unpaid' => 'mdi-cash-remove',
        'invoice/paid' => 'mdi-cash-check',
        'invoice/payment' => 'mdi-credit-card-outline',
        'invoice/payment-transaction' => 'mdi-receipt-text-check-outline',

        // Finance
        'finance/vendor-payment' => 'mdi-hand-coin-outline',
        'finance/order-payment' => 'mdi-cash-multiple',

        // Inventory
        'inventory/items' => 'mdi-package-variant-closed',
        'inventory/stock' => 'mdi-clipboard-list-outline',
        'inventory/transaction-stock' => 'mdi-card-text-outline',
        'inventory/warehouse' => 'mdi-warehouse',
        'inventory/supplier' => 'mdi-factory',
        'inventory/item-unit' => 'mdi-counter',
        'inventory/item-location' => 'mdi-map-marker-radius-outline',
        'inventory/item-category' => 'mdi-tag-multiple-outline',
        'inventory/stock-sync' => 'mdi-cloud-sync-outline',

        // Master
        'master/fleet-brand' => 'mdi-truck-flatbed',
        'master/fleet-type' => 'mdi-truck-cargo-container',
        'master/position' => 'mdi-badge-account-outline',
        'master/employee' => 'mdi-account-group-outline',
        'master/fleets' => 'mdi-truck-delivery-outline',
        'master/unit' => 'mdi-ruler-square',
        'master/customer' => 'mdi-account-multiple-outline',
        'master/cost-component' => 'mdi-currency-usd',
        'master/location' => 'mdi-map-marker-outline',
        'master/material' => 'mdi-cube-outline',
        'master/bank-sender' => 'mdi-bank-transfer',
        'master/bank-receiver' => 'mdi-bank-transfer-in',
        'master/due-date' => 'mdi-calendar-clock-outline',
        'master/fleet-company' => 'mdi-truck-check-outline',
        'master/transaction-type' => 'mdi-swap-horizontal-circle-outline',
        'master/company' => 'mdi-office-building-outline',
        'master/menu' => 'mdi-menu',
        'master/cost-component-price-log' => 'mdi-history',

        // Operational
        'operational/order' => 'mdi-clipboard-text-multiple-outline',
        'operational/monitoring-order' => 'mdi-truck-fast-outline',
        'operational/not-return-do' => 'mdi-file-cancel-outline',
        'operational/bon-ujt' => 'mdi-wallet-outline',
        'operational/return-do' => 'mdi-file-restore-outline',
        'operational/order-tax' => 'mdi-percent-outline',
        'operational/office-order' => 'mdi-briefcase-outline',
        'operational/down-payment' => 'mdi-cash-plus',

        // Purchasing
        'purchasing/purchase' => 'mdi-cart-outline',
        'purchasing/purchase-verification' => 'mdi-check-decagram-outline',
        'purchasing/purchase-confirmation' => 'mdi-check-all',
        'purchasing/purchase-payment' => 'mdi-cash-check',

        // Report
        'report/profit-loss' => 'mdi-finance',
        'report/order-detail' => 'mdi-chart-bar',
        'report/driver-salary' => 'mdi-cash',
        'report/driver-tonase' => 'mdi-weight-kilogram',
        'report/fleet-tonase' => 'mdi-scale-balance',
        'report/all-order-list' => 'mdi-format-list-bulleted',
        'report/maintenance-fleet' => 'mdi-wrench-clock',
        'report/maintenance-company-internal' => 'mdi-domain',
        'report/supplier' => 'mdi-factory',

        // Warehouse
        'warehouse/maintenance' => 'mdi-tools',
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
