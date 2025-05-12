<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('assets/images/logo/logo_total_kilat.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('assets/images/logo/logo_total_kilat.ico') }}" type="image/x-icon">
    <title>FMS - {{ $title ?? '' }}</title>

    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200;300;400;500;600;700;800&amp;display=swap"
        rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/font-awesome.css') }}">
    <!-- ico-font-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/icofont.css') }}">
    <!-- Themify icon-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/themify.css') }}">
    <!-- Flag icon-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/flag-icon.css') }}">
    <!-- Feather icon-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/feather-icon.css') }}">
    <!-- Plugins css start-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/slick.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/slick-theme.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/scrollbar.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/echart.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/date-picker.css') }}">

    @stack('style')

    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/bootstrap.css') }}">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}">
    {{-- <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/sidebar.css') }}"> --}}
    {{-- <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/sidebar-light.css') }}" id="sidebar-light"> --}}
    {{-- <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/sidebar-dark.css') }}" id="sidebar-dark"> --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.css') }}">

    <link id="color" rel="stylesheet" href="{{ asset('assets/css/color-1.css') }}" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive.css') }}">

</head>


<body>
    <!-- loader starts-->
    <div class="loader-wrapper">
        <div class="loader">
            <div class="loader4"></div>
        </div>
    </div>
    <!-- loader ends-->
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <!-- Page Header Start-->
        @include('layouts.header')
        <!-- Page Header Ends                              -->
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <!-- Page Sidebar Start-->
            <div class="sidebar-wrapper" data-layout="stroke-svg">
                <div class="logo-wrapper text-center"><a href="{{ url('/') }}"
                        class=" display-6 f-w-700 text-center"><img class="img-fluid "
                            src=" {{ asset('assets/images/logo/logo_total_kilat.png') }} " width="150"
                            alt="logo-light"></a>
                    <div class="back-btn"><i class="fa fa-angle-left"> </i></div>

                </div>
                <div class="logo-icon-wrapper"><a href="index.html"><img class="img-fluid"
                            src="{{ asset('assets/images/logo/logo-icon.png') }}" alt=""></a>
                </div>


                {{-- Sidebar KMS --}}
                @include('layouts.sidebar')

            </div>
            <!-- Page Sidebar Ends-->
            <div class="page-body">
                <div class="container-fluid">
                    <div class="page-title">
                        <div class="row">
                            <div class="col-6">
                                <h4>{{ $pageTitle ?? '' }}</h4>
                            </div>
                            <div class="col-6">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">
                                            <svg class="stroke-icon">
                                                <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}">
                                                </use>
                                            </svg></a></li>

                                    <li class="breadcrumb-item">{{ $firstSegment ?? '' }}</li>
                                    <li class="breadcrumb-item active">{{ $secondSegment ?? '' }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container-fluid">
                    <div class="row size-column">
                        @yield('content')
                    </div>
                </div>

            </div>
            <!-- footer start-->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 footer-copyright text-center">
                            <p class="mb-0">Copyright {{ now()->year }} © TOTAL KILAT SOLUTION </p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- latest jquery-->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <!-- Bootstrap js-->
    <script src="{{ asset('assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- feather icon js-->
    <script src="{{ asset('assets/js/icons/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/icons/feather-icon/feather-icon.js') }}"></script>
    <!-- scrollbar js-->
    <script src="{{ asset('assets/js/scrollbar/simplebar.js') }}"></script>
    <script src="{{ asset('assets/js/scrollbar/custom.js') }}"></script>
    <!-- Sidebar jquery-->
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <!-- Plugins JS start-->
    <script src="{{ asset('assets/js/sidebar-menu.js') }}"></script>
    <script src="{{ asset('assets/js/sidebar-pin.js') }}"></script>
    <script src="{{ asset('assets/js/slick/slick.min.js') }}"></script>
    <script src="{{ asset('assets/js/slick/slick.js') }}"></script>
    <script src="{{ asset('assets/js/header-slick.js') }}"></script>


    <!-- calendar js-->
    <script src="{{ asset('assets/js/datepicker/date-picker/datepicker.js') }}"></script>
    <script src="{{ asset('assets/js/datepicker/date-picker/datepicker.en.js') }}"></script>
    <script src="{{ asset('assets/js/datepicker/date-picker/datepicker.custom.js') }}"></script>

    @stack('script')

    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script src=" {{ asset('assets/js/dark-mode.js') }}"></script>

    {{-- <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.getElementById("simple-bar");

            // Restore last clicked menu item
            const lastActiveMenu = localStorage.getItem("activeMenu");
            if (lastActiveMenu) {
                const activeElement = document.querySelector(`a[href="${lastActiveMenu}"]`);
                if (activeElement) {
                    activeElement.classList.add("sidebar-link-active"); // Highlight active menu
                    activeElement.scrollIntoView({
                        behavior: "smooth",
                        block: "center"
                    });
                }
            }

            // Save menu click event
            document.querySelectorAll(".sidebar-link").forEach(item => {
                item.addEventListener("click", function() {
                    localStorage.setItem("activeMenu", this.getAttribute("href"));
                });
            });
        });
    </script> --}}



    {{-- <script>
        document.addEventListener("DOMContentLoaded", function() {

            if (window.location.pathname === "/" || window.location.pathname === "/home") {
                localStorage.removeItem("activeMenu"); // Hapus menu aktif
                document.querySelectorAll(".submenu").forEach(sub => {
                    localStorage.removeItem(sub.id); // Hapus state submenu
                });
            }

            // Ambil semua elemen dropdown sidebar
            document.querySelectorAll(".dropdown-toggle").forEach(item => {
                const menuCode = item.getAttribute("data-menu");
                const submenu = document.getElementById(`submenu-${menuCode}`);
                const icon = document.getElementById(`icon-${menuCode}`);

                // **Tampilkan dropdown jika sebelumnya dibuka (berdasarkan localStorage)**
                if (localStorage.getItem(`submenu-${menuCode}`) === "open") {
                    submenu.style.display = "block"; // Pastikan muncul
                    submenu.style.maxHeight = submenu.scrollHeight + "px"; // Efek slide down
                    submenu.style.overflow = "visible";
                    icon.classList.remove("icofont-caret-right");
                    icon.classList.add("icofont-caret-down");
                } else {
                    submenu.style.display = "none";
                    submenu.style.maxHeight = "0px";
                    submenu.style.overflow = "hidden";
                    icon.classList.remove("icofont-caret-down");
                    icon.classList.add("icofont-caret-right");
                }

                // **Event listener untuk klik dropdown**
                item.addEventListener("click", function() {
                    // Ambil semua submenu yang terbuka, kecuali submenu yang diklik
                    document.querySelectorAll(".submenu").forEach(sub => {
                        if (sub !== submenu) {
                            sub.style.maxHeight = "0px"; // Tutup yang lain
                            sub.style.overflow = "hidden";
                            sub.style.display = "none";
                            localStorage.setItem(sub.id, "closed");

                            // Reset ikon dropdown yang lain
                            const subIcon = document.getElementById(
                                `icon-${sub.id.replace('submenu-', '')}`);
                            if (subIcon) {
                                subIcon.classList.remove("icofont-caret-down");
                                subIcon.classList.add("icofont-caret-right");
                            }
                        }
                    });

                    // **Toggle submenu yang diklik**
                    if (submenu.style.display === "none" || submenu.style.maxHeight === "0px") {
                        submenu.style.display = "block";
                        setTimeout(() => {
                            submenu.style.maxHeight = submenu.scrollHeight +
                                "px"; // Efek animasi expand
                            submenu.style.overflow = "visible";
                        }, 10);
                        localStorage.setItem(`submenu-${menuCode}`, "open");

                        // Ubah ikon panah ke bawah
                        icon.classList.remove("icofont-caret-right");
                        icon.classList.add("icofont-caret-down");
                    } else {
                        submenu.style.maxHeight = "0px"; // Efek animasi collapse
                        submenu.style.overflow = "hidden";
                        // setTimeout(() => {
                        //     submenu.style.display =
                        //         "none"; // Sembunyikan setelah animasi selesai
                        // }, 1000);
                        localStorage.setItem(`submenu-${menuCode}`, "closed");

                        // Ubah ikon panah ke kanan
                        icon.classList.remove("icofont-caret-down");
                        icon.classList.add("icofont-caret-right");
                    }
                });
            });

            // **Restore active menu after reload**
            const lastActiveMenu = localStorage.getItem("activeMenu");
            if (lastActiveMenu) {
                const activeElement = document.querySelector(`a[href="${lastActiveMenu}"]`);
                if (activeElement) {
                    activeElement.classList.add("sidebar-link-active");
                    activeElement.scrollIntoView({
                        behavior: "smooth",
                        block: "center"
                    });
                }
            }

            // **Simpan active menu ketika diklik**
            document.querySelectorAll(".sidebar-link").forEach(item => {
                item.addEventListener("click", function(event) {
                    event.preventDefault(); // Mencegah reload halaman jika pakai anchor
                    localStorage.setItem("activeMenu", this.getAttribute("href"));

                    // Redirect ke halaman setelah delay untuk efek klik
                    setTimeout(() => {
                        window.location.href = this.getAttribute("href");
                    }, 300);
                });
            });
        });
    </script> --}}








</body>

</html>
