@extends('layouts.main', [
    'title' => $title,
    'pageTitle' => $title,
    'firstSegment' => $title,
    'secondSegment' => 'Add',
])

@push('style')
    <style>
        /* Styling untuk peta */
        #map {
            height: 600px;
            width: 100%;
            margin-top: 50px;
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
    </style>
@endpush

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ $title }} {{ __('general.add_data') }}</h4>

                <a href="{{ route($view . 'index') }}" class="btn btn-info">Back To List</a>

            </div>
            <div class="card-body col-md-12">
                <form class="row g-3" method="post" action="{{ route($view . 'store') }}">
                    @csrf
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="d-flex gap-5 justify-content-between">
                                <span class="h6">Origin</span>
                                <span class="h6">{{ $data->route->originLocation->name }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex gap-5 justify-content-between">
                                <span class="h6">Destination</span>
                                <span class="h6">{{ $data->route->destinationLocation->name }}</span>
                            </div>
                        </div>


                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="d-flex gap-5 justify-content-between">
                                <span class="h6">Distance</span>
                                {{-- <span class="h6">KM</span> --}}
                                <span class="h6">{{ $distance }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex gap-5 justify-content-between">
                                <span class="h6">Estimated Time</span>
                                {{-- <span class="h6">Hours</span> --}}

                                <span class="h6">{{ $duration }}</span>
                            </div>
                        </div>


                    </div>


                </form>

                <div class="col-md-12 mt-2">
                    <div class="progress mb-4">
                        <div class="progress-bar-animated progress-bar-striped bg-primary text-center" role="progressbar"
                            style="width: {{ $distancePercentage }}%;" aria-valuenow="{{ $distancePercentage }}"
                            aria-valuemin="0" aria-valuemax="100">
                            {{ $distancePercentage }}%
                        </div>
                    </div>
                </div>





                <div id="map"></div>

            </div>
        </div>
    </div>
@endsection

{{-- const beachFlagImg = document.createElement("img");

            beachFlagImg.src =
                "{{ asset('assets/images/truck/truck-1.png') }}";
            beachFlagImg.style.width = "40px"; --}}


@push('script')
    <!-- Pastikan library geometry tersedia -->
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

            console.log(decodedRoute);


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

            // ** Tambahkan event listener untuk buka/tutup InfoWindow saat marker diklik **
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

            destinationMarker.addListener("mouseover", function() {
                destinationInfoWindow.open(map, destinationMarker);
            });
            destinationMarker.addListener("mouseout", function() {
                destinationInfoWindow.close();
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
            return google.maps.geometry.poly.isLocationOnEdge(point, routePolyline, 0.000898);
        }

        // Assign initMap ke window agar Google Maps API dapat memanggilnya sebagai callback
        window.initMap = initMap;
    </script>
@endpush
