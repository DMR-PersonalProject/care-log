<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@hasSection('title')@yield('title') · @endif{{ config('app.name') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-50 text-lg text-stone-900 antialiased">
        <header class="border-b border-stone-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
                <a href="{{ route('home') }}" class="text-2xl font-bold text-sky-800">{{ config('app.name') }}</a>

                <nav class="flex gap-1">
                    <a href="{{ route('log') }}"
                       @class(['btn', 'bg-sky-100 text-sky-900' => request()->routeIs('log', 'entries.*'), 'text-stone-700 hover:bg-stone-100' => ! request()->routeIs('log', 'entries.*')])>
                        Daily Log
                    </a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-6">
            @if (session('status'))
                <div role="status" class="mb-6 rounded-lg border border-green-300 bg-green-50 px-4 py-3 font-semibold text-green-900">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </body>
</html>
