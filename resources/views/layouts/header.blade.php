@php
    $phlUser = auth()->user();
    $phlName = trim($phlUser->name ?? 'User');
    $phlParts = array_values(array_filter(preg_split('/\s+/', $phlName)));
    if (count($phlParts) >= 2) {
        $phlInitials = strtoupper(substr($phlParts[0], 0, 1) . substr(end($phlParts), 0, 1));
    } else {
        $phlInitials = strtoupper(substr($phlParts[0] ?? 'U', 0, 2));
    }
    $phlPalette = [
        ['#7669D3', '#6558C8'],
        ['#2f9e8f', '#1f7a6d'],
        ['#e08a3c', '#c96a1e'],
        ['#4a7fd4', '#3563ad'],
        ['#d45a7a', '#b23a5c'],
        ['#5aa24a', '#3f7d34'],
    ];
    $phlColor = $phlPalette[crc32($phlName) % count($phlPalette)];
    $phlAvatarStyle = "background: linear-gradient(135deg, {$phlColor[0]}, {$phlColor[1]});";
    $phlRoleName = $phlUser->role?->name;
@endphp
<div class="topbar-custom">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
                <li>
                    <button class="button-toggle-menu phl-icon-btn" aria-label="Toggle menu">
                        <i data-feather="menu" class="noti-icon"></i>
                    </button>
                </li>
                <li class="d-none d-lg-block">
                    <div class="phl-greeting">
                        <div class="phl-greeting-name"><span class="phl-greeting-hello">Hello,</span> {{ $phlName }}</div>
                        <div class="phl-greeting-date">{{ now()->translatedFormat('l, d F Y') }}</div>
                    </div>
                </li>
            </ul>

            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
                <!-- Button Trigger Customizer Offcanvas -->
                <li class="d-none d-sm-flex">
                    <button type="button" class="phl-icon-btn" data-toggle="fullscreen" aria-label="Fullscreen">
                        <i data-feather="maximize" class="align-middle fullscreen noti-icon"></i>
                    </button>
                </li>

                <!-- Light/Dark Mode Button Themes -->
                <li class="d-none d-sm-flex">
                    <button type="button" class="phl-icon-btn" id="light-dark-mode" aria-label="Toggle theme">
                        <i data-feather="moon" class="align-middle dark-mode"></i>
                        <i data-feather="sun" class="align-middle light-mode"></i>
                    </button>
                </li>

                {{-- Languange --}}
                <li class="dropdown notification-list topbar-dropdown">
                    <a class="phl-icon-btn" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false" aria-label="Language">
                        <i data-feather="flag" class="align-middle fullscreen noti-icon"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end profile-dropdown">
                        <!-- item-->
                        <form
                            class="dropdown-header noti-title {{ auth()->user()->languange == 'en' ? 'bg-primary' : '' }}"
                            method="POST" action="{{ route('general.change-languange') }}">
                            @csrf
                            <input type="hidden" name="languange" value="en">
                            <button type="submit"
                                class="btn btn-sm text-overflow m-0 {{ auth()->user()->languange == 'en' ? 'text-white' : '' }}">
                                Inggris
                            </button>
                        </form>

                        <div class="dropdown-divider"></div>

                        <form
                            class="dropdown-header noti-title {{ auth()->user()->languange == 'id' ? 'bg-primary' : '' }}"
                            method="POST" action="{{ route('general.change-languange') }}">
                            @csrf
                            <input type="hidden" name="languange" value="id">
                            <button type="submit"
                                class="btn btn-sm text-overflow m-0 {{ auth()->user()->languange == 'id' ? 'text-white' : '' }}">
                                Indonesia
                            </button>
                        </form>
                    </div>
                </li>

                <!-- User Dropdown -->
                <li class="dropdown notification-list topbar-dropdown">
                    <a class="nav-link dropdown-toggle phl-user-chip" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="phl-avatar" style="{{ $phlAvatarStyle }}" aria-hidden="true">{{ $phlInitials }}</span>
                        <span class="phl-user-meta">
                            <span class="phl-user-name">{{ $phlName }}</span>
                            @if ($phlRoleName)
                                <span class="phl-user-role">{{ $phlRoleName }}</span>
                            @endif
                        </span>
                        <i class="mdi mdi-chevron-down phl-user-chevron"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end profile-dropdown">
                        <!-- item-->
                        <div class="phl-dropdown-user">
                            <span class="phl-avatar phl-avatar-lg" style="{{ $phlAvatarStyle }}" aria-hidden="true">{{ $phlInitials }}</span>
                            <div>
                                <div class="phl-dropdown-user-name">{{ $phlName }}</div>
                                <div class="phl-dropdown-user-sub">{{ '@' . $phlUser->username }}{{ $phlRoleName ? ' · ' . $phlRoleName : '' }}</div>
                            </div>
                        </div>

                        <div class="dropdown-divider"></div>

                        <!-- item-->
                        <a href="{{ route('change-password.index') }}" class="dropdown-item notify-item">
                            <i class="mdi mdi-lock-outline fs-16 align-middle"></i>
                            <span>{{ __('change_password.change_password') }}</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <!-- item-->
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item notify-item">
                                <i class="mdi mdi-location-exit fs-16 align-middle"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
