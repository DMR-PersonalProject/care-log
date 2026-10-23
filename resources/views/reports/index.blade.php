@extends('layouts.app')

@use('App\Enums\Activity')
@use('App\Enums\Status')

@section('title', 'Report '.$report->rangeLabel())

@section('content')
    <h1 class="mb-6 text-3xl font-bold sm:text-4xl">Reports</h1>

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports') }}" class="card mb-8">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="from" class="field-label">From</label>
                <input type="date" id="from" name="from" class="field-input"
                       value="{{ old('from', $report->from->toDateString()) }}">
                <x-field-error name="from" />
            </div>

            <div>
                <label for="to" class="field-label">To</label>
                <input type="date" id="to" name="to" class="field-input"
                       value="{{ old('to', $report->to->toDateString()) }}">
                <x-field-error name="to" />
            </div>

            <div>
                <label for="activity" class="field-label">Activity</label>
                <select id="activity" name="activity" class="field-input">
                    <option value="">All activities</option>
                    @foreach (Activity::options() as $value => $label)
                        <option value="{{ $value }}" @selected(old('activity', $report->activity?->value) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-field-error name="activity" />
            </div>

            <div>
                <label for="status" class="field-label">Status</label>
                <select id="status" name="status" class="field-input">
                    <option value="">All statuses</option>
                    @foreach (Status::options() as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $report->status?->value) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-field-error name="status" />
            </div>
        </div>

        <div class="mt-5 flex flex-wrap gap-3">
            <button type="submit" class="btn btn-primary">Show report</button>
            <a href="{{ route('reports') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    {{-- Summary --}}
    <section aria-labelledby="summary-heading" class="mb-8">
        <h2 id="summary-heading" class="text-2xl font-bold">{{ $report->rangeLabel() }}</h2>
        <p class="mb-4 text-lg text-stone-700">
            {{ $report->activity?->label() ?? 'All activities' }} · {{ $report->status?->label() ?? 'All statuses' }}
            · <strong>{{ $entries->total() }} {{ Str::plural('entry', $entries->total()) }}</strong>
        </p>

        @if ($entries->total() > 0)
            <div class="grid gap-4 lg:grid-cols-2">
                <div class="card">
                    <h3 class="mb-3 text-xl font-bold">Entries per activity</h3>
                    <dl class="divide-y divide-stone-200">
                        @foreach ($counts as $label => $count)
                            <div class="flex justify-between gap-3 py-2">
                                <dt>{{ $label }}</dt>
                                <dd class="font-semibold">{{ $count }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="card">
                    <h3 class="mb-3 text-xl font-bold">Average vitals</h3>
                    <dl class="divide-y divide-stone-200">
                        @foreach ($averages as $average)
                            <div class="flex flex-wrap justify-between gap-x-3 py-2">
                                <dt>{{ $average['label'] }}</dt>
                                <dd>
                                    @if ($average['value'] !== null)
                                        <span class="font-semibold">{{ $average['value'] }} {{ $average['unit'] }}</span>
                                        <span class="text-base text-stone-600">({{ $average['readings'] }} {{ Str::plural('reading', $average['readings']) }})</span>
                                    @else
                                        <span class="text-stone-500">No readings</span>
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        @endif
    </section>

    {{-- Matching entries, by day --}}
    <section aria-labelledby="report-entries-heading">
        <h2 id="report-entries-heading" class="mb-4 text-2xl font-bold">Entries</h2>

        @if ($entries->isEmpty())
            <p class="card text-stone-600">No entries match these choices. Try a longer date range or "All activities".</p>
        @else
            <p class="mb-4 text-lg text-stone-700">Showing {{ $entries->firstItem() }}–{{ $entries->lastItem() }} of {{ $entries->total() }}.</p>

            <div class="space-y-6">
                @foreach ($days as $date => $dayEntries)
                    <div>
                        <h3 class="mb-2 text-xl font-bold">
                            <a href="{{ route('log', ['date' => $date]) }}" class="hover:underline">
                                {{ $dayEntries->first()->entry_date->format('l') }}, {{ $dayEntries->first()->displayDate() }}
                            </a>
                        </h3>
                        @include('entries._list', [
                            'entries' => $dayEntries,
                            'returnTo' => request()->getRequestUri(),
                        ])
                    </div>
                @endforeach
            </div>

            {{ $entries->links('partials.pagination') }}
        @endif
    </section>
@endsection
