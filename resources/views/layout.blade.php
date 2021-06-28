<!DOCTYPE html>
<html lang="{{ Lang::locale() }}" dir="ltr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}{{ Lang::has('menu.'.($activeMenu ?? '')) ? (' : '.__('menu.'.$activeMenu)) : '' }}</title>

        <link rel="stylesheet" href="{{ asset('libs/bootstrap-select/bootstrap-select.min.css') }}">
        <link rel="stylesheet" href="{{ asset('libs/c3/c3.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/icons.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/app.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
        @stack('styles')
    </head>
    <body>
        <div id="wrapper">
            <div class="left-side-menu">
                <div id="sidebar-menu" class="mm-active">
                    <form action="{{ route('search', $repository) }}" method="get">
                        <div class="app-search-box">
                            <input type="text" value="{{ request()->input('q', '') }}" name="q" class="form-control" placeholder="Search...">
                        </div>
                    </form>
                    <ul class="metismenu mm-show" id="side-menu">
                        @include('partials.sideNamespace', [
                            'files' => $repository->file_namespace
                        ])
                    </ul>
                </div>
            </div>
            @yield('content')
        </div>
        <script src="{{ asset('js/vendor.min.js') }}" charset="utf-8"></script>
        <script src="{{ asset('libs/bootstrap-select/bootstrap-select.min.js') }}" charset="utf-8"></script>
        <script src="{{ asset('libs/c3/c3.min.js') }}" charset="utf-8"></script>
        <script src="{{ asset('libs/d3/d3.min.js') }}" charset="utf-8"></script>
        <script src="{{ asset('js/pages/dashboard.init.js') }}" charset="utf-8"></script>
        <script src="{{ asset('js/app.min.js') }}" charset="utf-8"></script>
        @stack('scripts')
    </body>
</html>
