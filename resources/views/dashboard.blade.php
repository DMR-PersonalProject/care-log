@extends('layouts.app')

@section('title', 'Dashboard')

@push('scripts')
    @vite('resources/js/dashboard.js')
@endpush

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-lg text-stone-600">{{ today()->format('l') }}</p>
            <h1 class="text-4xl font-bold sm:text-5xl">Today, {{ today()->format('m/d/Y') }}</h1>
        </div>
        <a href="{{ route('log') }}" class="btn btn-primary">Add entry</a>
    </div>

    {{-- Today at a glance --}}
    <section aria-labelledby="glance-heading" class="mb-10">
        <h2 id="glance-heading" class="mb-4 text-2xl font-bold">Today at a glance</h2>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div class="card">
                <h3 class="text-lg font-semibold text-stone-600">Meals</h3>
                <p class="text-3xl font-bold">{{ $meals['done'] }} of 3 meals done</p>
                <ul class="mt-3 divide-y divide-stone-200">
                    @foreach ($meals['meals'] as $label => $entry)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <span class="font-semibold">{{ $label }}</span>
                            @if ($entry)
                                <span class="flex items-center gap-2">
                                    <span class="text-base text-stone-600">{{ $entry->displayTime() }}</span>
                                    <x-status-badge :status="$entry->status" />
                                </span>
                            @else
                                <span class="text-stone-500">Not logged yet</span>
                            @endif
                        </li>
                    @endforeach
                    <li class="flex justify-between gap-2 py-2">
                        <span class="font-semibold">Snacks</span>
                        <span>{{ $meals['snacks'] }}</span>
                    </li>
                </ul>
            </div>

            <div class="card">
                <h3 class="text-lg font-semibold text-stone-600">Medicine</h3>
                <p class="text-3xl font-bold">{{ $medicine->where('status', App\Enums\Status::Done)->count() }} given today</p>
                @if ($medicine->isEmpty())
                    <p class="mt-3 text-stone-500">None logged yet today.</p>
                @else
                    <ul class="mt-3 divide-y divide-stone-200">
                        @foreach ($medicine as $entry)
                            <li class="py-2">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <a href="{{ route('entries.show', $entry) }}" class="inline-flex min-h-11 items-center font-semibold text-sky-800 underline underline-offset-2">{{ $entry->displayTime() }}</a>
                                    <x-status-badge :status="$entry->status" />
                                </div>
                                @if ($entry->description)
                                    <p class="text-base text-stone-700 wrap-anywhere">{{ $entry->description }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @foreach ($lastEntries as $title => $entry)
                <div class="card">
                    <h3 class="text-lg font-semibold text-stone-600">{{ $title }}</h3>
                    @if ($entry)
                        <p class="text-2xl font-bold">
                            <a href="{{ route('entries.show', $entry) }}" class="inline-flex min-h-11 items-center hover:underline">{{ $entry->displayWhen() }}</a>
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <x-status-badge :status="$entry->status" />
                            @if ($entry->daysAgo() > 1)
                                <span class="text-stone-600">{{ $entry->daysAgo() }} days ago</span>
                            @endif
                        </div>
                        @if ($entry->description)
                            <p class="mt-2 text-base text-stone-700 wrap-anywhere">{{ $entry->description }}</p>
                        @endif
                    @else
                        <p class="text-2xl font-bold text-stone-500">Not logged yet</p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    {{-- Latest vitals --}}
    <section aria-labelledby="vitals-heading" class="mb-10">
        <h2 id="vitals-heading" class="mb-4 text-2xl font-bold">Latest vitals</h2>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($vitals as $tile)
                <div @class(['card', 'border-2 border-red-400 bg-red-50' => $tile['flag']])>
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <h3 class="text-lg font-semibold text-stone-600">{{ $tile['label'] }}</h3>
                        @if ($tile['flag'])
                            <span class="rounded-full bg-red-700 px-3 py-0.5 text-base font-bold text-white">⚠ {{ $tile['flag'] }}</span>
                        @endif
                    </div>

                    @if ($tile['entry'])
                        <p class="text-4xl font-bold">
                            {{ $tile['value'] }} <span class="text-xl font-semibold text-stone-600">{{ $tile['unit'] }}</span>
                        </p>
                        <p class="mt-1 text-base text-stone-700">{{ $tile['entry']->displayWhen() }}</p>
                    @else
                        <p class="text-2xl font-bold text-stone-500">No reading yet</p>
                    @endif
                    <p class="mt-1 text-base text-stone-600">Usual: {{ $tile['range'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Vitals trend --}}
    <section aria-labelledby="trend-heading" class="mb-10">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 id="trend-heading" class="text-2xl font-bold">Vitals over the last {{ $days }} days</h2>
            <div class="flex gap-2">
                @foreach ([7, 30] as $option)
                    <a href="{{ route('dashboard', $option === 7 ? [] : ['days' => $option]) }}#trend-heading"
                       @if ($days === $option) aria-current="true" @endif
                       @class(['btn', 'btn-primary' => $days === $option, 'btn-secondary' => $days !== $option])>
                        {{ $option }} days
                    </a>
                @endforeach
            </div>
        </div>

        <p class="mb-4 text-base text-stone-600">Every reading is shown. Red triangles are outside the usual range.</p>

        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ([
                ['chart-bp', 'Blood pressure (mmHg)', 'systolic'],
                ['chart-hr', 'Heart rate (bpm)', 'heart_rate'],
                ['chart-spo2', 'Oxygen (SpO₂ %)', 'spo2'],
                ['chart-temp', 'Temperature (°C)', 'temperature'],
            ] as [$id, $title, $field])
                <figure class="card min-w-0">
                    <figcaption class="mb-2 text-lg font-semibold">{{ $title }}</figcaption>
                    @if ($readings->whereNotNull($field)->isEmpty())
                        <p class="flex h-56 items-center justify-center text-stone-500">No readings in this period.</p>
                    @else
                        <div class="relative h-56">
                            <canvas id="{{ $id }}" role="img" aria-label="{{ $title }} chart. The same readings are in the table below."></canvas>
                        </div>
                    @endif
                </figure>
            @endforeach
        </div>

        @if ($readings->isNotEmpty())
            <details class="card mt-4">
                <summary class="min-h-11 cursor-pointer py-2 font-semibold text-sky-800">Show readings as a table</summary>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-left text-base">
                        <thead class="bg-stone-100 text-stone-700">
                            <tr>
                                <th scope="col" class="px-3 py-2">When</th>
                                <th scope="col" class="px-3 py-2">Blood pressure</th>
                                <th scope="col" class="px-3 py-2">Heart rate</th>
                                <th scope="col" class="px-3 py-2">Oxygen</th>
                                <th scope="col" class="px-3 py-2">Temperature</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200">
                            @foreach ($readings->reverse() as $entry)
                                <tr>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $entry->displayDate() }} {{ $entry->displayTime() }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        @if ($entry->systolic !== null || $entry->diastolic !== null)
                                            @include('partials.vital-reading', ['entry' => $entry, 'field' => 'systolic', 'empty' => '?'])
                                            /
                                            @include('partials.vital-reading', ['entry' => $entry, 'field' => 'diastolic', 'empty' => '?'])
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">@include('partials.vital-reading', ['entry' => $entry, 'field' => 'heart_rate'])</td>
                                    <td class="px-3 py-2">@include('partials.vital-reading', ['entry' => $entry, 'field' => 'spo2', 'suffix' => '%'])</td>
                                    <td class="px-3 py-2">@include('partials.vital-reading', ['entry' => $entry, 'field' => 'temperature', 'suffix' => '°C'])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        @endif

        <script type="application/json" id="vitals-data">@json($chartData)</script>
    </section>

    {{-- Recent entries --}}
    <section aria-labelledby="recent-heading">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 id="recent-heading" class="text-2xl font-bold">Recent entries</h2>
            <a href="{{ route('entries.index') }}" class="btn btn-secondary">See all entries</a>
        </div>

        @if ($recent->isEmpty())
            <p class="card text-stone-600">Nothing logged yet. Use "Add entry" to start.</p>
        @else
            @include('entries._list', [
                'entries' => $recent,
                'showDate' => true,
                'returnTo' => request()->getRequestUri(),
            ])
        @endif
    </section>
@endsection
