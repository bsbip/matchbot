<!DOCTYPE html>
<html>
    <head>
        <base href="/">
        <title>Matchbot</title>
        <meta name="robots" content="noindex, nofollow" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="{{ url('favicon.ico') }}" />
        <link rel="stylesheet" href="{{ url('components/normalize-css/normalize.css') }}" />
        <link rel="stylesheet" href="{{ url('components/foundation-sites/dist/css/foundation.min.css') }}" />
        <link rel="stylesheet" href="{{ url('css/app.css') }}" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Arima+Madurai:500" />

        <script src="{{ url('components/jquery/dist/jquery.min.js') }}"></script>
        <script src="{{ url('components/foundation-sites/dist/js/foundation.min.js') }}"></script>

        <script src="{{ url('assets/js/angular.js') }}"></script>
    </head>
    <body>
        <app></app>

        <script src="{{ url('assets/js/angular/bundle.js') }}"></script>
    </body>
</html>
