<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calling Agent Builder</title>
    <link rel="stylesheet" href="{{ asset('vendor/calling-agent/css/builder.css') }}">
</head>
<body>
    @yield('content')
    <script src="{{ asset('vendor/calling-agent/js/builder.js') }}"></script>
</body>
</html>
