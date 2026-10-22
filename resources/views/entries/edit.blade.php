@extends('layouts.app')

@section('title', 'Edit entry')

@section('content')
    <h1 class="mb-6 text-3xl font-bold sm:text-4xl">Edit entry</h1>

    <section class="card">
        @include('entries._form', [
            'entry' => $entry,
            'action' => route('entries.update', $entry),
            'method' => 'PUT',
            'submitLabel' => 'Save',
            'cancelUrl' => route('log', ['date' => $entry->entry_date->toDateString()]),
        ])
    </section>
@endsection
