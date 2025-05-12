<nav class="sidebar-main">
    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
    <div id="sidebar-menu">
        <ul class="sidebar-links" id="simple-bar">
            <li class="back-btn"><a href="index.html"><img class="img-fluid" src="../assets/images/logo/logo-icon.png"
                        alt=""></a>
                <div class="mobile-back text-end"> <span>Back </span><i class="fa fa-angle-right ps-2"
                        aria-hidden="true"></i></div>
            </li>
            <li class="pin-title sidebar-main-title">
                <div>
                    <h6>Pinned</h6>
                </div>
            </li>
            <li class="sidebar-main-title">
                {{-- <div>
                    <h6 class="lan-1">Menu</h6>
                </div> --}}
            </li>
            <li class="sidebar-list"><i class="fa fa-thumb-tack"> </i><a class="sidebar-link sidebar-title"
                    href="#">
                    <i class="text-white icofont h5 icofont-home"></i>

                    </svg><span class="lan-3">Dashboard </span></a>
                <ul class="sidebar-submenu">
                    <li><a href="/home" style="font-size: 15px">Home</a></li>


                    @foreach ($menus as $menu)
                        @if ($menu->parentCode == '0')
                            @php
                                $sub = $menus->where('parentCode', $menu->code);
                            @endphp

                            @foreach ($sub as $item)
                                @if ($item->code == 'ORDER_MONITORING')
                                    <li><a style="font-size: 15px"
                                            href="{{ url('/' . $item->url) }}">{{ $item->name }}</a></li>
                                @else
                                    @continue;
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </ul>
            </li>

            @foreach ($menus as $menu)
                @if ($menu->parentCode == '0')
                    @php
                        $sub = $menus->where('parentCode', $menu->code);
                    @endphp

                    <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title"
                            href="#">

                            <i class="text-white icofont h5 icofont-{{ $menu->icon }}"></i>

                            <span style="font-size: 16px">{{ $menu->name }} </span>
                        </a>
                        <ul class="sidebar-submenu">
                            @foreach ($sub as $item)
                                @if ($item->code == 'ORDER_MONITORING')
                                    @continue;
                                @else
                                    <li><a style="font-size: 15px"
                                            href="{{ url('/' . $item->url) }}">{{ $item->name }}</a></li>
                                @endif
                            @endforeach

                        </ul>
                    </li>
                @endif
            @endforeach

    </div>
</nav>
