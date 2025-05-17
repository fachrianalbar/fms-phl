<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Log In | PHL</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PHL LOGISTIC FMS" />
    <meta name="author" content="Zoyothemes" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/logo-phl.png') }}">

    <!-- App css -->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

    <script src="{{ asset('assets/js/head.js') }}"></script>
</head>

<body>
    <div class="account-page">
        <div class="container-fluid p-0">
            <div class="row align-items-center g-0 px-3 py-3 vh-100">

                <div class="col-xl-5">
                    <div class="row">
                        <div class="col-md-8 mx-auto">
                            <div class="card">
                                <div class="card-body">
                                    <div class="p-lg-3">
                                        <div class="p-md-4 p-lg-0">
                                            <div class="text-lg-start text-center mb-4">
                                                <div class="auth-brand">
                                                    <a href="{{ url('/') }}" class="logo logo-light">
                                                        <span class="logo-lg">
                                                            <img src="{{ asset('assets/images/logo-phl.png') }}"
                                                                alt="" height="100">
                                                        </span>
                                                    </a>
                                                    <a href="{{ url('/') }}" class="logo logo-dark">
                                                        <span class="logo-lg">
                                                            <img src="{{ asset('assets/images/logo-phl.png') }}"
                                                                alt="" height="100">
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="auth-title-section text-lg-start text-center mb-4">
                                                <h3 class="text-dark fw-semibold mb-3">Sign in to account</h3>
                                                <p class="text-muted fs-14 mb-0">Enter your username & password to login
                                                </p>
                                            </div>

                                            <form method="POST" action="{{ route('authenticate') }}" class="my-4">
                                                @csrf
                                                <div class="form-group mb-3">
                                                    <label for="username" class="form-label">Username</label>
                                                    <input class="form-control" type="text" name="username"
                                                        id="username" required placeholder="Enter your username">
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label for="password" class="form-label">Password</label>
                                                    <input class="form-control" type="password" name="password"
                                                        id="password" required placeholder="Enter your password">
                                                </div>

                                                <div class="form-group mb-0 row">
                                                    <div class="col-12">
                                                        <div class="d-grid">
                                                            <button class="btn btn-primary fw-semibold"
                                                                type="submit">Sign in</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-7 d-none d-xl-inline-block">
                    <div class="account-page-bg rounded-4">
                        <!-- Carousel as-is, left untouched -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor -->
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/libs/waypoints/lib/jquery.waypoints.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jquery.counterup/jquery.counterup.min.js') }}"></script>
    <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>

</html>
