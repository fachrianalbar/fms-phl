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
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/bootstrap.css') }}">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/icofont.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">


    <!-- Responsive css-->
    {{-- <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive.css') }}"> --}}

    <style>
        /* Styling untuk peta */
        #map {
            height: 800px;
            width: 80%;
            margin: 0 auto;

            margin-top: -350px;
        }

        .origin-label {
            transform: translateY(-20px);
            /* Geser label ke atas */
        }

        .destination-label {
            transform: translateY(-20px);
        }

        .gm-style .gm-style-iw {
            text-align: center;
            color: black !important;
            font-weight: bold
                /* Warna teks tetap hitam */
        }

        /* Pastikan juga warna border InfoWindow tetap terlihat */
        .gm-style .gm-style-iw-d {
            background: white !important;
            font-weight: bold
                /* Latar belakang tetap putih */
                color: black !important;
        }

        .gm-ui-hover-effect {
            display: none !important;
        }

        div canvas:first-child {
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
</head>




<body>


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
                                    src="{{ asset('assets/images/logo/logo_total_kilat.png') }}" alt=""></a>
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
                    <div class="content text-center mt-5">
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

                    <div class="table-responsive custom-scrollbar">


                        @php
                            use Carbon\Carbon;
                        @endphp
                        <table class="display table">
                            <thead>
                                <tr>
                                    <th>Order Date</th>
                                    <th>Fleet</th>
                                    <th>Shipment No</th>
                                    <th>Origin</th>
                                    <th>Destination</th>
                                    <th>Status</th>
                                    <th>Estimated Time</th>
                                    <th>Distance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($data->orderDate)->format('d-m-Y') }}</td>
                                    <td>{{ $data->fleet?->plateNumber }}</td>
                                    <td>{{ $data->shipmentNumber }}</td>
                                    <td>{{ $data->route?->originLocation?->name }}</td>
                                    <td>{{ $data->route?->destinationLocation?->name }}</td>
                                    <td>{{ $data->orderStatus?->name }}</td>
                                    <td>{{ $data->estimatedTime }}</td>
                                    <td>{{ $data->distance }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="col-md-12 mt-3">
                            <div class="progress mb-4">
                                <div class="progress-bar-animated progress-bar-striped bg-primary text-center"
                                    role="progressbar" style="width: {{ $distancePercentage }}%;"
                                    aria-valuenow="{{ $distancePercentage }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $distancePercentage }}%
                                </div>
                            </div>
                        </div>
                    </div>




                    {{-- <div class="star-animate"> <img class="img-fluid" src="../assets/images/landing/Vector.png"
                    alt="Vector"></div> --}}
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

    <div id="map"></div>

    </div>





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
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>

    <!-- dataTables.bootstrap5 -->
    <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>

    <!-- dataTables.keyTable -->
    <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable-bs5/js/keyTable.bootstrap5.min.js') }}"></script>

    <!-- dataTable.responsive -->
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>

    <!-- dataTables.select -->
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select-bs5/js/select.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>




    <script>
        $(document).ready(function() {
            $("#shipmentForm").submit(function(event) {
                event.preventDefault(); // Mencegah form dikirim langsung

                let shipmentNumber = $("#shipmentNumber").val();

                if (shipmentNumber === "") {
                    swal({
                        title: "{{ __('general.warning') }}",
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
                                title: "{{ __('general.warning') }}",
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


    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAqbjxyIJhHovu-x_Pn9dPlDilIKWTMYpE
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               &v=weekly
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               &libraries=geometry,marker
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               &callback=initMap"
        async defer></script>

    <script>
        let map;
        let originMarker;
        let destinationMarker;
        let polylines = [];
        let originInfoWindow;
        let destinationInfoWindow;

        // Status InfoWindow (Untuk toggle buka/tutup)
        let isOriginInfoWindowOpen = true;
        let isDestinationInfoWindowOpen = true;



        // Terima data dari controller
        // const encodedPolyline = "{{ $encodedPolyline }}";
        const origin = {
            lat: parseFloat("{{ $originLatitude }}"),
            lng: parseFloat("{{ $originLongitude }}")
        };
        const destination = {
            lat: parseFloat("{{ $destinationLatitude }}"),
            lng: parseFloat("{{ $destinationLongitude }}")
        };

        const truck = {
            lat: parseFloat("{{ $truckLatitude }}"),
            lng: parseFloat("{{ $truckLongitude }}")
        };

        const mapCenter = {
            lat: parseFloat("{{ $destinationLatitude }}"),
            lng: parseFloat("{{ $destinationLongitude }}")
        };

        // History posisi truk (array objek dengan properti latitude dan longitude)
        const truckPositions = @json($historyPosition);

        function initMap() {
            // Inisialisasi peta dengan center di origin (atau sesuaikan)
            map = new google.maps.Map(document.getElementById("map"), {
                center: mapCenter,
                zoom: 9,
                mapId: '9a48a35de3c19dd3'
            });

            // Decode polyline dari Google Directions API
            // const decodedRoute = google.maps.geometry.encoding.decodePath(encodedPolyline);

            const decodedRoute = @json($decodedPolyline);


            // Gambar rute utama (garis biru)
            new google.maps.Polyline({
                path: decodedRoute,
                strokeColor: '#3674B5',
                strokeOpacity: 1.0,
                strokeWeight: 4,
                map: map
            });

            // Tambahkan marker untuk origin
            originMarker = new google.maps.Marker({
                position: origin,
                map: map,
                title: "{{ $data->route->originLocation->name }}",
                icon: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png",
            });

            // ** InfoWindow untuk Origin **
            originInfoWindow = new google.maps.InfoWindow({
                content: `<b style="text-align: center  ">
                {{ $data->route->originLocation->name }}
                <br>
                <p>{{ $data->route->originLocation->address }}</p>
                <br>
                <p>(Lat = {{ $data->route->originLocation->latitude }}, Long = {{ $data->route->originLocation->longitude }} )  </p>
                 </b>
                `,
                maxWidth: 350,
                disableAutoPan: true
            });

            // ** Langsung tampilkan InfoWindow untuk Origin **
            // originInfoWindow.open(map, originMarker);

            // // ** Tambahkan event listener untuk buka/tutup InfoWindow saat marker diklik **
            // originMarker.addListener("click", function() {
            //     if (isOriginInfoWindowOpen) {
            //         originInfoWindow.close();
            //     } else {
            //         originInfoWindow.open(map, originMarker);
            //     }
            //     isOriginInfoWindowOpen = !isOriginInfoWindowOpen;
            // });

            originMarker.addListener("mouseover", function() {
                originInfoWindow.open(map, originMarker);
            });

            originMarker.addListener("mouseout", function() {
                originInfoWindow.close();
            });

            // ** DESTINATION MARKER **
            const destination = {
                lat: parseFloat("{{ $destinationLatitude }}"),
                lng: parseFloat("{{ $destinationLongitude }}")
            };

            destinationMarker = new google.maps.Marker({
                position: destination,
                map: map,
                title: "{{ $data->route->destinationLocation->name }}",
            });

            // ** InfoWindow untuk Destination **
            destinationInfoWindow = new google.maps.InfoWindow({
                content: `<b>
                {{ $data->route->destinationLocation->name }}
                <br>
                <p>{{ $data->route->destinationLocation->address }}</p>
                <br>
                <p>(Lat = {{ $data->route->destinationLocation->latitude }}, Long = {{ $data->route->destinationLocation->longitude }})  </p>
                 </b>
                `,
                maxWidth: 350,
                disableAutoPan: true
            });

            // ** Langsung tampilkan InfoWindow untuk Destination **
            // destinationInfoWindow.open(map, destinationMarker);

            // ** Tambahkan event listener untuk buka/tutup InfoWindow saat marker diklik **
            // destinationMarker.addListener("click", function() {
            //     if (isDestinationInfoWindowOpen) {
            //         destinationInfoWindow.close();
            //     } else {
            //         destinationInfoWindow.open(map, destinationMarker);
            //     }
            //     isDestinationInfoWindowOpen = !isDestinationInfoWindowOpen;
            // });

            destinationMarker.addListener("mouseover", function() {
                destinationInfoWindow.open(map, destinationMarker);
            });
            destinationMarker.addListener("mouseout", function() {
                destinationInfoWindow.close();
            });

            // Gambar history jalur truk dengan pengecekan on-route/off-route
            drawTruckHistory(decodedRoute);
        }

        function drawTruckHistory(decodedRoute) {
            // Bersihkan polyline sebelumnya jika ada
            polylines.forEach(poly => poly.setMap(null));
            polylines = [];

            // Konversi data truckPositions ke array koordinat {lat, lng}
            const truckCoords = truckPositions.map(pos => {
                return {
                    lat: parseFloat(pos.latitude),
                    lng: parseFloat(pos.longitude)
                };
            });

            // Loop tiap segmen antara dua titik berurutan
            for (let i = 0; i < truckCoords.length - 1; i++) {
                const start = truckCoords[i];
                const end = truckCoords[i + 1];

                // Cek apakah titik 'end' berada di dalam jalur (on-route)
                const isOnRoute = checkIfOnRoute(end, decodedRoute);

                // Warna segmen: biru jika on-route, merah jika off-route
                const color = isOnRoute ? '#1F7D53' : '#FF0000';

                // Gambar segmen polyline
                const segmentPolyline = new google.maps.Polyline({
                    path: [start, end],
                    strokeColor: color,
                    strokeOpacity: 1.0,
                    strokeWeight: 5,
                    map: map
                });
                polylines.push(segmentPolyline);
            }

            // (Opsional) Tambahkan marker di posisi terakhir truk
            if (truckCoords.length > 0) {
                // new google.maps.Marker({
                //     position: truckCoords[truckCoords.length - 1],
                //     map: map,
                //     icon: {
                //         url: "{{ asset('assets/images/truck/truck-1.png') }}"
                //     },
                //     title: "Last Truck Position"
                // });

                const truckImg = document.createElement("img");

                truckImg.src =
                    "{{ asset('assets/images/truck/truck-1.png') }}";
                truckImg.style.width = "40px";

                // Menambahkan marker untuk lokasi truk
                const marker = new google.maps.Marker({
                    map: map,
                    position: truckCoords[truckCoords.length - 1],
                    title: "Truck's Current Location",
                    icon: {
                        url: "{{ asset('assets/images/truck/truck-1.png') }}",
                        scaledSize: new google.maps.Size(40, 40) // Adjust the size if needed
                    }
                });

            }
        }

        // Fungsi untuk mengecek apakah titik berada di jalur rute menggunakan toleransi
        function checkIfOnRoute(latLng, routeCoords) {
            if (!routeCoords || routeCoords.length === 0) return false;
            const point = new google.maps.LatLng(latLng.lat, latLng.lng);
            const routePolyline = new google.maps.Polyline({
                path: routeCoords
            });
            // Toleransi sekitar 0.00045 derajat (~50 meter), sesuaikan bila perlu
            return google.maps.geometry.poly.isLocationOnEdge(point, routePolyline, 0.00045);
        }

        // Assign initMap ke window agar Google Maps API dapat memanggilnya sebagai callback
        window.initMap = initMap;
    </script>
</body>

</html>
