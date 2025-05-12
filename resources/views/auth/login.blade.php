<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="{{ asset('assets/images/logo/logo_total_kilat.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('assets/images/logo/logo_total_kilat.png') }}" type="image/x-icon">
    <title>FMS - Login</title>
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
    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/bootstrap.css') }}">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}">
    <link id="color" rel="stylesheet" href="{{ asset('assets/css/color-1.css') }}" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive.css') }}">
</head>

<body>
    <!-- login page start-->
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-6"><img class="bg-img-cover bg-center"
                    src="{{ asset('assets/images/login/truck_old.jpg') }}" alt="loginpage"></div>
            <div class="col-xl-6 p-0">
                <div class="login-card login-dark">
                    <div>
                        <div><a class="logo" href="{{ url('/') }}"><img class="img-fluid for-dark"
                                    src="{{ asset('assets/images/logo/logo_total_kilat.png') }}" width="50%"
                                    alt="looginpage"><img class="img-fluid for-light"
                                    src="{{ asset('assets/images/logo/logo_total_kilat.png') }}" width="50%"
                                    alt="looginpage"></a>
                        </div>
                        @include('partials.alert')

                        <div class="login-main">
                            <form class="theme-form" method="post" action="{{ route('authenticate') }}">
                                @csrf
                                <h4>Sign in to account </h4>
                                <p>Enter your username & password to login</p>
                                <div class="form-group">
                                    <label class="col-form-label">Username</label>
                                    <input class="form-control" type="text" name="username" required="">
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Password</label>
                                    <div class="form-input position-relative">
                                        <input class="form-control" type="password" name="password" required="">
                                        <div class="show-hide"><span class="show"> </span></div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <div class="checkbox p-0">
                                        <input id="checkbox1" type="checkbox">
                                        <label class="text-muted" for="checkbox1">Remember password</label>
                                    </div>
                                    <button class="btn btn-primary btn-block w-100" type="submit">Sign in</button>

                                    <div class="text-center mt-3">
                                        <small class="text-muted">
                                            🧭 Just want to track your shipment?
                                            <p>
                                                <a href="{{ url('/') }}"
                                                    class="ms-1 text-decoration-underline">Continue as Guest</a>
                                            </p>

                                        </small>
                                    </div>
                                </div>

                                {{-- <p class="mt-4 mb-0 text-center">Don't have account?<a class="ms-2"
                                        href="sign-up.html">Create Account</a></p> --}}
                            </form>
                        </div>
                    </div>
                </div>
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
        <!-- Sidebar jquery-->
        <script src="{{ asset('assets/js/config.js') }}"></script>
        <!-- Plugins JS start-->
        <!-- calendar js-->
        <!-- Plugins JS Ends-->
        <!-- Theme js-->
        <script src="{{ asset('assets/js/script.js') }}"></script>
    </div>
</body>

</html>
