<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="DexignZone">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Owlio School Teacher Panel</title>

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Shared Head (CSS, favicon, etc) --}}
    @include('layouts.partials.teachers.head')

    {{-- Optional Page-Level CSS --}}
    @stack('styles')
</head>

<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

        <!--**********************************
            Nav header start
        ***********************************-->
        <div class="nav-header">
            <a href="{{ url('/teacher/dashboard') }}" class="brand-logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" height="40">
            </a>

            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span>
                    <span class="line"></span>
                    <span class="line"></span>
                </div>
            </div>
        </div>
        <!--**********************************
            Nav header end
        ***********************************-->

        {{-- Top Header --}}
        @include('layouts.partials.teachers.header')

        {{-- Sidebar --}}
        @include('layouts.partials.teachers.sidebar')

        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <div class="container-fluid px-4">
                @yield('content')
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->

        <!--**********************************
            Footer start
        ***********************************-->
        <div class="footer">
            <div class="copyright">
                <p>
                    Copyright © Designed & Developed by
                    <a href="http://dexignzone.com/" target="_blank">
                        DexignZone
                    </a> 2023
                </p>
            </div>
        </div>
        <!--**********************************
            Footer end
        ***********************************-->

    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    {{-- Shared Scripts --}}
    @include('layouts.partials.teachers.scripts')

    {{-- Optional Page-Level JS --}}
    @stack('scripts')

    <!-- Global Toast Container -->
    <div id="globalToastContainer"
        class="position-fixed top-0 end-0 p-3"
        style="z-index: 1080;">
    </div>

</body>
</html>