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
            {{-- Table on larger screens --}}
            <div class="hidden overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm md:block">
                <table class="w-full text-left">
                    <thead class="bg-stone-100 text-base text-stone-700">
                        <tr>
                            <th scope="col" class="px-4 py-3">Time</th>
                            <th scope="col" class="px-4 py-3">Activity</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-4 py-3">Description</th>
                            <th scope="col" class="px-4 py-3">Vitals</th>
                            <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200">
                        @foreach ($entries as $entry)
                            <tr class="align-top">
                                <td class="px-4 py-3 font-semibold whitespace-nowrap">{{ $entry->displayTime() }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('entries.show', $entry) }}" class="font-semibold text-sky-800 underline underline-offset-2">
                                        {{ $entry->activity->label() }}
                                    </a>
                                </td>
                                <td class="px-4 py-3"><x-status-badge :status="$entry->status" /></td>
                                <td class="px-4 py-3 whitespace-pre-line">{{ $entry->description }}</td>
                                <td class="px-4 py-3 text-base">{{ $entry->vitalsSummary() }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('entries.edit', $entry) }}" class="btn btn-secondary">Edit</a>
                                        <x-confirm-delete :entry="$entry" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Stacked cards on phones --}}
            <ul class="space-y-3 md:hidden">
                @foreach ($entries as $entry)
                    <li class="card">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xl font-bold">{{ $entry->displayTime() }}</p>
                                <a href="{{ route('entries.show', $entry) }}" class="text-lg font-semibold text-sky-800 underline underline-offset-2">
                                    {{ $entry->activity->label() }}
                                </a>
                            </div>
                            <x-status-badge :status="$entry->status" />
                        </div>

                        @if ($entry->description)
                            <p class="mt-2 whitespace-pre-line">{{ $entry->description }}</p>
                        @endif
                        @if ($entry->hasVitals())
                            <p class="mt-2 text-base text-stone-700">{{ $entry->vitalsSummary() }}</p>
                        @endif

                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('entries.edit', $entry) }}" class="btn btn-secondary">Edit</a>
                            <x-confirm-delete :entry="$entry" />
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
