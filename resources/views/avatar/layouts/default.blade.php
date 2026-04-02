<!DOCTYPE html>
<html lang="{{ str_replace('_','-',strtolower(app()->getLocale())) }}">
@include('avatar.layouts._header')
<body class="avatar-theme" data-layout="topnav">
    <div class="wrapper avatar-wrapper">
        <div class="content-page">
            <div class="content">
                @include('avatar.layouts._nav')
                <div class="container avatar-page">
                    @yield('content')
                </div>
            </div><!-- content -->
            @include('avatar.layouts._footer')
        </div><!-- content-page -->
    </div><!-- wrapper -->
    @include('avatar.layouts._script')
    @section('js')
    @show
</body>
</html>
