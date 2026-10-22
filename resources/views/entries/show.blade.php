@extends('layouts.app')

@section('title', $entry->activity->label().' '.$entry->displayDate())

@section('content')
    <a href="{{ route('log', ['date' => $entry->entry_date->toDateString()]) }}" class="btn btn-secondary mb-6">
        ← Back to {{ $entry->displayDate() }}
    </a>

    <section class="card">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-lg text-stone-600">{{ $entry->entry_date->format('l') }}, {{ $entry->displayDate() }} at {{ $entry->displayTime() }}</p>
                <h1 class="text-3xl font-bold sm:text-4xl">{{ $entry->activity->label() }}</h1>
            </div>
            <x-status-badge :status="$entry->status" />
        </div>

        <dl class="mt-6 grid gap-x-8 gap-y-4 sm:grid-cols-[max-content_1fr]">
            <dt class="font-semibold text-stone-700">Description</dt>
            <dd class="whitespace-pre-line">{{ $entry->description ?: '—' }}</dd>

            <dt class="font-semibold text-stone-700">Vitals</dt>
            <dd>{{ $entry->vitalsSummary() }}</dd>
        </dl>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('entries.edit', $entry) }}" class="btn btn-primary">Edit</a>
            <x-confirm-delete :entry="$entry" />
        </div>
    </section>
@endsection
