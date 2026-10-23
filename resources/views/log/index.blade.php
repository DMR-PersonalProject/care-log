@extends('layouts.app')

@section('title', 'Daily Log '.$date->format('m/d/Y'))

@section('content')
    {{-- Day heading and navigation --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-lg text-stone-600">{{ $date->format('l') }}</p>
            <h1 class="flex flex-wrap items-center gap-3 text-4xl font-bold sm:text-5xl">
                {{ $date->format('m/d/Y') }}
                @if ($date->isToday())
                    <span class="rounded-full bg-sky-100 px-3 py-1 text-lg font-semibold text-sky-900">Today</span>
                @endif
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('log', ['date' => $date->copy()->subDay()->toDateString()]) }}" class="btn btn-secondary">← Previous</a>
            <a href="{{ route('log') }}" class="btn btn-secondary">Today</a>
            <a href="{{ route('log', ['date' => $date->copy()->addDay()->toDateString()]) }}" class="btn btn-secondary">Next →</a>

            <form method="GET" action="{{ route('log') }}">
                <label for="jump-date" class="sr-only">Go to a date</label>
                <input type="date" id="jump-date" name="date" value="{{ $date->toDateString() }}"
                       class="field-input w-auto" onchange="this.form.submit()">
            </form>
        </div>
    </div>

    {{-- Quick add --}}
    <section class="card mb-8" aria-labelledby="add-heading">
        <h2 id="add-heading" class="mb-4 text-2xl font-bold">Add entry</h2>
        @include('entries._form', [
            'entry' => $newEntry,
            'action' => route('entries.store'),
            'submitLabel' => 'Add entry',
        ])
    </section>

    {{-- The day's entries --}}
    <section aria-labelledby="entries-heading">
        <h2 id="entries-heading" class="mb-4 text-2xl font-bold">
            Entries for this day <span class="font-normal text-stone-600">({{ $entries->count() }})</span>
        </h2>

        @if ($entries->isEmpty())
            <p class="card text-stone-600">Nothing logged for this day yet.</p>
        @else
            @include('entries._list', ['entries' => $entries])
        @endif
    </section>
@endsection
