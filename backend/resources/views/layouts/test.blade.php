<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.student.head')
</head>

<body>

    {{-- Preloader --}}
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>

    <div id="main-wrapper">

        {{-- Header --}}
        @include('layouts.partials.student.header')

        {{-- Sidebar --}}
        @include('layouts.partials.student.sidebar')

        {{-- Content --}}
        <div class="content-body">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>

        {{-- Footer --}}
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

    </div>

    {{-- Scripts --}}
    @include('layouts.partials.student.scripts')

    @stack('scripts')

</body>
</html>