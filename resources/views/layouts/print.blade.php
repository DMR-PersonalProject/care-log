<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- The browser suggests this as the file name when saving as PDF, so no app name suffix. --}}
        <title>@yield('title')</title>

        @fonts
        @vite(['resources/css/app.css'])
    </head>
    <body class="print-page min-h-screen bg-stone-200 text-lg text-stone-900 antialiased print:bg-white">
        @yield('content')
    </body>
</html>
