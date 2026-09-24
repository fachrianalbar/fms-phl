<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>PHL - {{ $title ?? '' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PHL LOGISTIC FMS" />
    <meta name="author" content="Zoyothemes" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/logo-phl.png') }}">

    <!-- App css -->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />
    <!-- Sidebar custom style -->
    <link href="{{ asset('assets/css/sidebar-custom.css') }}" rel="stylesheet" type="text/css" />

    <!-- Icons -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Flatpickr CSS shim (loads actual css from libs) -->
    <link href="{{ asset('assets/css/vendors/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- DataTables CSS -->
    <link href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />

    <script src="{{ asset('assets/js/head.js') }}"></script>


    <style>
        /* Global: Posisi semua modal di atas (bukan di tengah layar) */
        .modal:not(.modal-fullscreen) .modal-dialog:not(.modal-fullscreen) {
            margin-top: 2rem !important;
            margin-bottom: 2rem !important;
        }
        .modal-dialog-centered {
            align-items: flex-start !important;
            min-height: auto !important;
        }
    </style>

    @stack('style')
    <link href="{{ asset('assets/css/dark-mode-overrides.css') }}?v=20260923-9" rel="stylesheet" type="text/css" />
    <style>
        /* Dark alert surfaces need an explicit foreground because app h2 rules are global. */
        html[data-bs-theme="dark"] body .swal2-popup,
        html[data-bs-theme="dark"] body .swal2-popup h2.swal2-title,
        html[data-bs-theme="dark"] body .swal2-popup .swal2-html-container,
        html[data-bs-theme="dark"] body .swal2-popup .swal2-footer,
        html[data-bs-theme="dark"] body .swal-modal,
        html[data-bs-theme="dark"] body .swal-modal .swal-title,
        html[data-bs-theme="dark"] body .swal-modal .swal-text,
        html[data-bs-theme="dark"] body .swal-modal .swal-content,
        html[data-bs-theme="dark"] body .swal-modal .swal-footer,
        body .swal2-popup.swal-dark-popup,
        body .swal2-popup.swal-dark-popup h2.swal2-title,
        body .swal2-popup.swal-dark-popup .swal2-html-container,
        body .swal2-popup.swal-dark-popup .swal2-footer,
        body .swal-modal.swal-dark-mode,
        body .swal-modal.swal-dark-mode .swal-title,
        body .swal-modal.swal-dark-mode .swal-text,
        body .swal-modal.swal-dark-mode .swal-content,
        body .swal-modal.swal-dark-mode .swal-footer {
            color: #f8fafc !important;
            -webkit-text-fill-color: #f8fafc !important;
        }

        html[data-bs-theme="dark"] body .swal2-popup,
        html[data-bs-theme="dark"] body .swal-modal,
        body .swal2-popup.swal-dark-popup,
        body .swal-modal.swal-dark-mode {
            background-color: #1f2028 !important;
        }

        html[data-bs-theme="dark"] body .swal2-popup .swal2-cancel,
        html[data-bs-theme="dark"] body .swal-modal .swal-button--cancel,
        body .swal2-popup.swal-dark-popup .swal2-cancel,
        body .swal-modal.swal-dark-mode .swal-button--cancel {
            color: #1f2937 !important;
            -webkit-text-fill-color: #1f2937 !important;
        }
    </style>

<!-- body start -->
<body data-menu-color="light" data-sidebar="default">

    <!-- Begin page -->
    <div id="app-layout">

        <!-- Topbar Start -->
        @include('layouts.header')
        <!-- end Topbar -->

        <!-- Left Sidebar Start -->
        <div class="app-sidebar-menu">
            <div class="h-100" data-simplebar>

                <!--- Sidemenu -->
                <div id="sidebar-menu">

                    <div class="logo-box">
                        <a href="{{ url('/') }}" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="{{ asset('assets/images/logo-phl.png') }}" alt="" height="50">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/images/logo-phl.png') }}" alt="" height="50">
                            </span>
                        </a>
                        <a href="{{ url('/') }}" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="{{ asset('assets/images/logo-phl.png') }}" alt="" height="50">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/images/logo-phl.png') }}" alt="" height="50">
                            </span>
                        </a>
                    </div>

                    {{-- Sidebar menu --}}
                    @include('layouts.sidebar')


                </div>
                <!-- End Sidebar -->

                <div class="clearfix"></div>

            </div>
        </div>
        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">
                    <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-18 fw-semibold m-0">{{ $pageTitle ?? '' }}</h4>
                        </div>

                        <div class="text-end">
                            <ol class="breadcrumb m-0 py-0">
                                <li class="breadcrumb-item"><a
                                        href="javascript: void(0);">{{ $firstSegment ?? '' }}</a></li>
                                <li class="breadcrumb-item active">{{ $secondSegment ?? '' }}</li>
                            </ol>
                        </div>
                    </div>

                    @yield('content')



                </div> <!-- container-fluid -->
            </div> <!-- content -->

            <!-- Footer Start -->
            {{-- <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col fs-13 text-muted text-center">
                            &copy;
                            <script>
                                document.write(new Date().getFullYear())
                            </script> - Made with <span class="mdi mdi-heart text-danger"></span> by
                            <a href="#!" class="text-reset fw-semibold">Zoyothemes</a>
                        </div>
                    </div>
                </div>
            </footer> --}}
            <!-- end Footer -->

        </div>
        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->

    <!-- Vendor -->
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/libs/waypoints/lib/jquery.waypoints.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jquery.counterup/jquery.counterup.min.js') }}"></script>
    <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>



    <!-- jQuery -->
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <!-- DataTables JS -->
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>



    <!-- Global shims/helpers -->
    <script src="{{ asset('assets/js/flat-pickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/flat-pickr/custom-flatpickr.js') }}"></script>
    <script src="{{ asset('assets/js/helpers/delete-cost.js') }}"></script>

    @stack('script')

    <script>
        (function syncGlobalAlertTheme() {
            const sync = function() {
                let savedTheme = null;
                try {
                    savedTheme = JSON.parse(localStorage.getItem('__CONFIG__') || '{}').theme;
                } catch (error) {
                    savedTheme = null;
                }

                const isDarkTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' || savedTheme === 'dark';

                document.querySelectorAll('.swal-modal, .swal2-popup').forEach(function(element) {
                    const background = getComputedStyle(element).backgroundColor.match(/\d+(?:\.\d+)?/g) || [];
                    const isDarkSurface = isDarkTheme || (background.length >= 3 && (
                        Number(background[0]) + Number(background[1]) + Number(background[2]) < 260
                    ));

                    if (!isDarkSurface) {
                        return;
                    }

                    element.style.setProperty('color', '#f8fafc', 'important');
                    element.style.setProperty('background-color', '#1f2028', 'important');
                    element.querySelectorAll('*:not(button)').forEach(function(child) {
                        child.style.setProperty('color', '#f8fafc', 'important');
                        child.style.setProperty('-webkit-text-fill-color', '#f8fafc', 'important');
                    });
                });
            };

            if (document.body) {
                new MutationObserver(sync).observe(document.body, { childList: true, subtree: true });
                window.setInterval(sync, 100);
            }
            new MutationObserver(sync).observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-bs-theme']
            });
            sync();
        }());
    </script>



    <!-- App js -->
    <script>
        $.extend(true, $.fn.dataTable.defaults, {
            pageLength: 25
        });
    </script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/confirm.js') }}"></script>

    <style id="global-alert-dark-mode-final">
        html[data-bs-theme="dark"] body .swal2-container .swal2-popup,
        html[data-bs-theme="dark"] body .swal2-container .swal2-popup h2.swal2-title,
        html[data-bs-theme="dark"] body .swal2-container .swal2-popup div.swal2-html-container,
        html[data-bs-theme="dark"] body .swal2-container .swal2-popup div.swal2-footer,
        body .swal2-popup.swal-readable-dark,
        body .swal2-popup.swal-readable-dark h2.swal2-title,
        body .swal2-popup.swal-readable-dark div.swal2-html-container,
        body .swal2-popup.swal-readable-dark div.swal2-footer,
        body.swal2-shown .swal2-container > .swal2-popup.swal-readable-dark,
        body.swal2-shown .swal2-container > .swal2-popup.swal-readable-dark h2.swal2-title,
        body.swal2-shown .swal2-container > .swal2-popup.swal-readable-dark div.swal2-html-container,
        body.swal2-shown .swal2-container > .swal2-popup.swal-readable-dark div.swal2-footer,
        html[data-bs-theme="dark"] body .swal-overlay .swal-modal,
        html[data-bs-theme="dark"] body .swal-overlay .swal-modal .swal-title,
        html[data-bs-theme="dark"] body .swal-overlay .swal-modal .swal-text,
        html[data-bs-theme="dark"] body .swal-overlay .swal-modal .swal-content,
        html[data-bs-theme="dark"] body .swal-overlay .swal-modal .swal-footer {
            color: #f8fafc !important;
            -webkit-text-fill-color: #f8fafc !important;
        }
    </style>

</body>

</html>
