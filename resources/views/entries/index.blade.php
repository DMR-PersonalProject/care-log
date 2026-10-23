@extends('layouts.app')

@section('title', $search !== '' ? 'Search: '.$search : 'All entries')

@section('content')
    <h1 class="mb-6 text-3xl font-bold sm:text-4xl">All entries</h1>

    <form method="GET" action="{{ route('entries.index') }}" role="search" class="card mb-6">
        <label for="search" class="field-label">Search</label>
        <p id="search-hint" class="mb-2 text-base text-stone-600">Type words from the notes, an activity, or a status, like "refused lunch".</p>
        <div class="flex flex-wrap gap-2">
            <input type="search" id="search" name="q" value="{{ $search }}" maxlength="100"
                   aria-describedby="search-hint" class="field-input min-w-0 flex-1 basis-56">
            <button type="submit" class="btn btn-primary">Search</button>
            @if ($search !== '')
                <a href="{{ route('entries.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <p class="mb-4 text-lg text-stone-700" role="status">
        @if ($entries->total() === 0)
            @if ($search !== '')
                No entries match "<strong>{{ $search }}</strong>". Try a different or shorter word.
            @else
                No entries yet. Add one from the <a href="{{ route('log') }}" class="text-sky-800 underline underline-offset-2">Daily Log</a>.
            @endif
        @else
            Showing {{ $entries->firstItem() }}–{{ $entries->lastItem() }} of {{ $entries->total() }} {{ Str::plural('entry', $entries->total()) }}@if ($search !== '') matching "<strong>{{ $search }}</strong>"@endif.
        @endif
    </p>

    @if ($entries->isNotEmpty())
        @include('entries._list', [
            'entries' => $entries,
            'showDate' => true,
            'words' => $words,
            'returnTo' => request()->getRequestUri(),
        ])

        {{ $entries->links('partials.pagination') }}
    @endif
@endsection
