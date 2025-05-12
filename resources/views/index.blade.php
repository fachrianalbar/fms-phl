<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Riho admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
    <meta name="keywords"
        content="admin template, Riho admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="pixelstrap">
    <link rel="icon" href="{{ asset('assets/images/logo/logo_total_kilat.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('assets/images/logo/logo_total_kilat.ico') }}" type="image/x-icon">
    <title>Total Kilat Solution</title>
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap"
        rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/font-awesome.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/icofont.css') }}">
    <link rel="icon" href="{{ asset('assets/svg/landing-icons.svg') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/slick.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/slick-theme.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/animate.css') }}">
    <!-- ico-font-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/icofont.css') }}">
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/bootstrap.css') }}">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/icofont.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">


    <style>
        .landing-page div canvas:first-child {
            display: none !important;
        }

        .suggestion-box {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 10px 10px;
            max-height: 250px;
            overflow-y: auto;
            z-index: 1050;
        }

        .suggestion-item {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .suggestion-item:hover {
            background-color: #f7f7f7;
        }

        .suggestion-icon {
            color: #888;
            font-size: 18px;
        }
    </style>


    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive.css') }}">
</head>

<body class="landing-page">
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->
    <!-- page-wrapper Start-->
    <div class="landing-page"><span class="cursor"><span class="cursor-move-inner"><span
                    class="cursor-inner"></span></span><span class="cursor-move-outer"><span class="cursor-outer">
                </span></span></span>
        <!-- Page Body Start-->
        <div class="landing-home" id="home">
            <div class="container-fluid">
                <div class="sticky-header">
                    <header>
                        <nav class="navbar navbar-b navbar-dark navbar-trans navbar-expand-xl fixed-top nav-padding"
                            id="sidebar-menu"><a class="navbar-brand p-0" href="{{ route('guest.home') }}"><img
                                    class="img-fluid responsive-logo" width="150"
                                    src="{{ asset('assets/images/logo/logo_total_kilat.png') }}
" alt=""></a>
                            <button class="navbar-toggler navabr_btn-set custom_nav" type="button"
                                data-bs-toggle="collapse" data-bs-target="#navbarDefault" aria-controls="navbarDefault"
                                aria-expanded="false"
                                aria-label="Toggle navigation"><span></span><span></span><span></span></button>
                            <div class="navbar-collapse justify-content-center collapse hidenav" id="navbarDefault">
                                {{-- <ul class="navbar-nav navbar_nav_modify" id="scroll-spy">
                                    <li class="nav-item"><a class="nav-link active" href="#home">Home</a></li>
                                    <li class="nav-item"> <a class="nav-link" id="page" href="#demo">Page</a>
                                    </li>
                                    <li class="nav-item"><a class="nav-link" id="Feature"
                                            href="#framework">Feature</a></li>
                                    <li class="nav-item"><a class="nav-link" id="Portfolio"
                                            href="https://themeforest.net/user/pixelstrap/portfolio"
                                            target="_blank">Portfolio</a></li>
                                    <li class="nav-item"><a class="nav-link"
                                            href="https://docs.google.com/forms/d/e/1FAIpQLSe6hKUXw_By-pg7yabL0FxoTM02ZPTxoXy8PE3yboRuUCuyeA/viewform"
                                            target="_blank">Hire Us</a></li>
                                    <li class="nav-item"><a class="nav-link" id="documentation"
                                            href="https://docs.pixelstrap.net/admin/riho/document/"
                                            target="_blank">Documentation</a></li>
                                </ul> --}}
                            </div>
                            @if (auth()->user())
                                <li class="btn-group">
                                    <span class="h6 btn dropdown-toggle text-black" style="font-size: 18px"
                                        data-bs-toggle="dropdown" aria-expanded="false"
                                        data-bs-auto-close="outside">Welcome
                                        {{ auth()->user()->name }}</span>
                                    <form class="dropdown-menu p-1 form-wrapper dark-form">
                                        <div class="mb-3 d-flex justify-content-center align-items-center mt-3">


                                <li> <a href="{{ route('dashboard') }}"> <span
                                            class="btn btn-pill btn-outline-primary btn-sm">Go
                                            Home</span></a></li>

                </div>
                </form>

                </li>
            @else
                <div class="buy-btn"> <a class="nav-link js-scroll" href="{{ route('login') }}">Login</a>
                </div>
                @endif
                </nav>
                </header>
            </div>
            <div class="row justify-content-center " style="margin-bottom: 1000px">
                <div class="col-lg-8 col-sm-10">
                    <div class="best-selling"><img class="img-fluid"
                            src="../assets/images/landing/selling-product.png" alt="selling-product">
                        <div class="img-shadow"></div>
                    </div>
                    <div class="nft-marketplace"> <img class="img-fluid"
                            src="../assets/images/landing/nft-marketplace.png" alt="nft-marketplace">
                        <div class="nft-marketplace-shadow"></div>
                    </div>
                    <div class="content text-center">
                        @include('partials.alert')

                        <div>
                            <h1 class="text-center">Total Kilat Solution Tracking
                                {{-- <span
                                        class="d-flex align-items-center justify-content-center pt-2 sub-content"><span>Admin</span>
                                        <button class="animate-button-slide"><span class="notification-slider"><span
                                                    class="d-flex h-100"><span class="mb-0 f-w-400"> <span
                                                            class="font-primary">Ecommerce</span></span><i
                                                        class="icon-arrow-top-right f-light"> </i></span><span
                                                    class="d-flex h-100"><span class="mb-0 f-w-400"><span
                                                            class="f-light">PROJECT</span></span></span><span
                                                    class="d-flex h-100"> <span class="mb-0 f-w-400"><span
                                                            class="f-light">Default</span></span></span></span></button><span>HTML
                                            Template</span></span> --}}
                            </h1>
                            <div class="arrow-animate">
                                <svg>
                                    <use href="../assets/svg/icon-sprite.svg#animated-arrow"> </use>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="user-content">
                        {{-- <p class="text-center ">Established in 2010, Total Kilat is dedicated to provide Fuel
                                control system and Fuel consumption monitoring solution and GPS-GSM/GPRS vehicle
                                monitoring and fleet management solutions that has a transformative effect on the way
                                business optimize their fleet and mobile assets.</p> --}}



                        <p class="h3 font-weight-bold mt-3">Track your shipment</p>



                        <form id="shipmentForm"
                            class="d-flex justify-content-center align-items-center gap-2 position-relative w-100"
                            style="max-width: 600px; margin: 0 auto;">

                            <div class="position-relative w-100">
                                <input type="text" class="form-control input-air-primary h2 px-4"
                                    name="shipmentNumber" id="shipmentNumber" placeholder="Enter Shipment"
                                    autocomplete="off">
                                <div id="shipmentSuggestions" class="suggestion-box  shadow-sm rounded-3 d-none">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-dark btn-md px-4"><i
                                    class="icofont icofont-search-alt-2"></i></button>
                        </form>


                    </div>


                    {{-- <div class="star-animate"> <img class="img-fluid" src="../assets/images/landing/Vector.png"
                                alt="Vector"></div> --}}
                    <div class="screen-1"> <img class="img-fluid"
                            src="{{ asset('assets/images/total_kilat/banner.jpg') }}" alt="dashboard-img"></div>
                    {{-- <div class="screen-2"> <img class="img-fluid sidebar-cuts-image"
                                src="../assets/images/landing/sidebarcuts.png" alt="sidebarcuts">
                            <div class="screen-sidebar"></div> --}}
                    {{-- </div>
                    <div class="total-revenue-img"><img class="img-fluid"
                            src="../assets/images/landing/totalrevenue.png" alt="totalrevenue">
                        <div class="total-revenue-shadow"> </div>
                    </div>
                    <div class="star-img"> <img class="img-fluid start-animate fa-spin"
                            src="../assets/images/landing/star.png" alt="star"></div>
                    <div class="new-user-img"><img class="img-fluid" src="../assets/images/landing/newuser.png"
                            alt="new-user">
                        <div class="new-user-shadow"> </div>
                    </div>
                    <div class="star-img-left"> <img class="img-fluid start-animate-rotate fa-spin"
                            src="../assets/images/landing/star.png" alt="star"></div> --}}
                </div>
            </div>
        </div>
    </div>
    <!-- demo section  -->

    {{-- <section class="landing-footer section-py-space" id="footer">
            <div class="custom-container">
                <div class="row p-0 m-0">
                    <div class="col-12">
                        <div class="footer-contain">
                            <div class="rating-wrraper"><img class="img-fluid" src="../assets/images/logo/logo.png"
                                    alt="logo"></div>
                            <h2 class="f-w-600">"Riho Globally Trusted HTML Admin Theme"</h2>
                            <p class="f-w-500">Copyright 2024-25 © Riho All rights reserved.</p>
                            <ul class="star-rate">
                                <li><i class="fa fa-star font-warning"></i></li>
                                <li><i class="fa fa-star font-warning"></i></li>
                                <li><i class="fa fa-star font-warning"></i></li>
                                <li><i class="fa fa-star font-warning"> </i></li>
                                <li> <i class="fa fa-star font-warning"> </i></li>
                            </ul>
                            <div class="btn-footer"> <a class="btn btn-lg btn-primary" target="_blank"
                                    href="index.html" data-bs-original-title="" title="">Check Now</a><a
                                    class="btn btn-lg btn-secondary" target="_blank"
                                    href=" https://themeforest.net/user/pixelstrap/portfolio"
                                    data-bs-original-title="" title="">Buy Now</a><a
                                    class="btn btn-lg btn-success" target="_blank"
                                    href="https://themeforest.net/user/pixelstrap/portfolio" data-bs-original-title=""
                                    title="">Rate Us</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </section> --}}
    </div>
    <!-- latest jquery-->
    <script src="{{ asset('assets/js/jquery-3.5.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/cursor/stats.min.js') }}"></script>
    <!-- Bootstrap js-->
    <script src="{{ asset('assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- feather icon js-->
    <script src="{{ asset('assets/js/icons/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/icons/feather-icon/feather-icon.js') }}"></script>
    <!-- Plugins JS start-->
    <script src="{{ asset('assets/js/tooltip-init.js') }}"></script>
    <script src="{{ asset('assets/js/animation/wow/wow.min.js') }}"></script>
    <script src="{{ asset('assets/js/landing_sticky.js') }}"></script>
    <script src="{{ asset('assets/js/landing.js') }}"></script>
    <script src="{{ asset('assets/js/slick/slick.min.js') }}"></script>
    <script src="{{ asset('assets/js/slick/slick.js') }}"></script>
    <script src="{{ asset('assets/js/landing-slick.js') }}"></script>
    <script src="{{ asset('assets/js/header-slick.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>


    <script>
        $(document).ready(function() {
            $("#shipmentForm").submit(function(event) {
                event.preventDefault(); // Mencegah form dikirim langsung

                let shipmentNumber = $("#shipmentNumber").val();

                if (shipmentNumber === "") {
                    swal({
                        title: "Warning",
                        text: "Shipment number is required!",
                        icon: "warning",
                    })
                    return;
                }

                $.ajax({
                    url: "{{ route('ajax.guest-order-shipment', '') }}/" + shipmentNumber,
                    type: "GET",
                    success: function(response) {
                        if (!response || response.length === 0) {
                            swal({
                                title: "Warning",
                                text: "Shipment number not found!",
                                icon: "warning",
                            })
                        } else {
                            window.location.href =
                                "{{ route('guest.order-track') }}?shipmentNumber=" +
                                shipmentNumber;
                        }
                    },
                    error: function() {
                        alert("An error occurred. Please try again.");
                        swal({
                            title: "Danger",
                            text: "An error occurred. Please try again.",
                            icon: "danger",
                        })
                    }
                });
            });

            $('#shipmentNumber').on('input', function() {
                let input = $(this).val();
                let box = $('#shipmentSuggestions');

                if (input.length >= 3) {
                    $.ajax({
                        url: "{{ route('ajax.guest-order-shipment-suggestion') }}",
                        method: "GET",
                        data: {
                            query: input
                        },
                        success: function(data) {
                            box.empty().removeClass('d-none');

                            if (data.length > 0) {
                                data.forEach(item => {
                                    box.append(`
                            <div class="suggestion-item" data-value="${item}">
                                <i class="bi bi-search suggestion-icon"></i> ${item}
                            </div>
                        `);
                                });
                            } else {
                                box.append(
                                    '<div class="suggestion-item text-muted"></div>'
                                );
                            }
                        }
                    });
                } else {
                    box.addClass('d-none').empty();
                }
            });

            // Optional: klik suggestion untuk isi input
            $(document).on('click', '.suggestion-item', function() {
                const cleanText = $(this).data('value');

                $('#shipmentNumber').val(cleanText);
                $('#shipmentSuggestions').addClass('d-none').empty();
            });

        });
    </script>

    <!-- Plugins JS Ends-->
</body>

</html>
