<div class="topbar-custom">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
                <li>
                    <button class="button-toggle-menu nav-link">
                        <i data-feather="menu" class="noti-icon"></i>
                    </button>
                </li>
                <li class="d-none d-lg-block">
                    <h5 class="mb-0">Hello {{ auth()->user()->name }}</h5>
                </li>
            </ul>

            <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
                {{-- <li class="d-none d-lg-block">
                    <form class="app-search d-none d-md-block me-auto">
                        <div class="position-relative topbar-search">
                            <input type="text" class="form-control ps-4" placeholder="Search..." />
                            <i
                                class="mdi mdi-magnify fs-16 position-absolute text-muted top-50 translate-middle-y ms-2"></i>
                        </div>
                    </form>
                </li> --}}

                <!-- Button Trigger Customizer Offcanvas -->
                <li class="d-none d-sm-flex">
                    <button type="button" class="btn nav-link" data-toggle="fullscreen">
                        <i data-feather="maximize" class="align-middle fullscreen noti-icon"></i>
                    </button>
                </li>

                <!-- Light/Dark Mode Button Themes -->
                <li class="d-none d-sm-flex">
                    <button type="button" class="btn nav-link" id="light-dark-mode">
                        <i data-feather="moon" class="align-middle dark-mode"></i>
                        <i data-feather="sun" class="align-middle light-mode"></i>
                    </button>
                </li>

                {{-- Languange --}}
                <li class="dropdown notification-list topbar-dropdown">
                    <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
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


                        <!-- item-->
                        {{-- <a href="pages-profile.html" class="dropdown-item notify-item">
                            <i class="mdi mdi-account-circle-outline fs-16 align-middle"></i>
                            <span>My Account</span>
                        </a>

                        <!-- item-->
                        <a href="auth-lock-screen.html" class="dropdown-item notify-item">
                            <i class="mdi mdi-lock-outline fs-16 align-middle"></i>
                            <span>Lock Screen</span>
                        </a> --}}


                        <!-- item-->
                        {{-- <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item notify-item">
                                <i class="mdi mdi-location-exit fs-16 align-middle"></i>
                                <span>Logout</span>
                            </button>
                        </form> --}}

                    </div>
                </li>

                <!-- User Dropdown -->
                <li class="dropdown notification-list topbar-dropdown">
                    <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <img src="{{ asset('assets/images/users/user-13.jpg') }}" alt="user-image"
                            class="rounded-circle" />
                        <span class="pro-user-name ms-1">{{ auth()->user()->name }} <i
                                class="mdi mdi-chevron-down"></i></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end profile-dropdown">
                        <!-- item-->
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Welcome !</h6>
                        </div>

                        <!-- item-->
                        {{-- <a href="pages-profile.html" class="dropdown-item notify-item">
                            <i class="mdi mdi-account-circle-outline fs-16 align-middle"></i>
                            <span>My Account</span>
                        </a> --}}

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
