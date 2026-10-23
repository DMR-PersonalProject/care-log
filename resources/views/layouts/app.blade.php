<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@hasSection('title')@yield('title') · @endif{{ config('app.name') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('scripts')
    </head>
    <body class="min-h-screen bg-stone-50 text-lg text-stone-900 antialiased">
        <header class="border-b border-stone-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3">
                <a href="{{ route('dashboard') }}" class="text-2xl font-bold text-sky-800">{{ config('app.name') }}</a>

                <nav class="flex flex-wrap gap-1">
                    @foreach ([
                        ['Dashboard', route('dashboard'), request()->routeIs('dashboard')],
                        ['Daily Log', route('log'), request()->routeIs('log', 'entries.show', 'entries.edit')],
                        ['All Entries', route('entries.index'), request()->routeIs('entries.index')],
                    ] as [$label, $url, $active])
                        <a href="{{ $url }}" @if ($active) aria-current="page" @endif
                           @class(['btn', 'bg-sky-100 text-sky-900' => $active, 'text-stone-700 hover:bg-stone-100' => ! $active])>
                            {{ $label }}
                        </a>
                    @endforeach
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
