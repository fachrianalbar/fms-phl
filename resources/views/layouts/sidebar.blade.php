<ul id="side-menu">


    <li class="menu-title">Menu</li>

    <li>
        <a href="#sidebarDashboards" data-bs-toggle="collapse">
            <i data-feather="home"></i>
            <span> Dashboard </span>
            <span class="menu-arrow"></span>
        </a>
        <div class="collapse" id="sidebarDashboards">
            <ul class="nav-second-level">
                <li>
                    <a href="/home" class="tp-link">{{ auth()->user()->languange == 'id' ? 'Beranda' : 'Home' }}</a>
                </li>
            </ul>
        </div>
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
        @endphp

        <li class="{{ $isParentActive ? 'menuitem-active' : '' }}">
            <a
                @if ($hasSubMenu) href="#menu-{{ $parent->code }}" 
                    data-bs-toggle="collapse"
                    aria-expanded="{{ $hasActiveChild ? 'true' : 'false' }}"
                @else
                    href="{{ url('/' . $parent->url) }}" @endif
                class="{{ $isParentActive && !$hasSubMenu ? 'active' : '' }}">
                <i data-feather="{{ $parent->icon }}"></i>
                <span>{{ auth()->user()->languange == 'id' ? $parent->nama : $parent->name }}</span>
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
                                <a href="{{ url('/' . $child->url) }}" class="tp-link {{ $isChildActive ? 'active' : '' }}"> <span class="h4">- </span>
                                    {{ auth()->user()->languange == 'id' ? $child->nama : $child->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </li>
    @endforeach

</ul>
