{{--
    The entries table (larger screens) and stacked cards (phones).
    $entries: the entries to show
    $showDate: add a Date column (for lists that span several days)
    $words: search words to highlight
    $returnTo: where to come back to after deleting
--}}
@php
    $showDate ??= false;
    $words ??= [];
    $returnTo ??= null;
    $mark = fn (?string $text) => App\Support\Highlight::html($text, $words);
@endphp

{{-- Table on desktops (with the Date column and buttons it is too wide for a tablet) --}}
<div class="hidden overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm lg:block">
    <table class="w-full text-left">
        <thead class="bg-stone-100 text-base text-stone-700">
            <tr>
                @if ($showDate)
                    <th scope="col" class="px-4 py-3">Date</th>
                @endif
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
                    @if ($showDate)
                        <td class="px-4 py-3 whitespace-nowrap">
                            <a href="{{ route('log', ['date' => $entry->entry_date->toDateString()]) }}" class="-my-2 inline-flex min-h-11 items-center text-sky-800 underline underline-offset-2">
                                {{ $entry->displayDate() }}
                            </a>
                        </td>
                    @endif
                    <td class="px-4 py-3 font-semibold whitespace-nowrap">{{ $entry->displayTime() }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('entries.show', $entry) }}" class="-my-2 inline-flex min-h-11 items-center font-semibold text-sky-800 underline underline-offset-2">
                            {{ $mark($entry->activity->label()) }}
                        </a>
                    </td>
                    <td class="px-4 py-3"><x-status-badge :status="$entry->status" :words="$words" /></td>
                    <td class="px-4 py-3 whitespace-pre-line wrap-anywhere">{{ $mark($entry->description) }}</td>
                    <td class="px-4 py-3 text-base">{{ $entry->vitalsSummary() }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('entries.edit', $entry) }}" class="btn btn-secondary">Edit</a>
                            <x-confirm-delete :entry="$entry" :return-to="$returnTo" />
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Stacked cards on phones and tablets --}}
<ul class="space-y-3 lg:hidden">
    @foreach ($entries as $entry)
        <li class="card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    @if ($showDate)
                        <a href="{{ route('log', ['date' => $entry->entry_date->toDateString()]) }}" class="inline-flex min-h-11 items-center text-base text-sky-800 underline underline-offset-2">
                            {{ $entry->entry_date->format('l') }}, {{ $entry->displayDate() }}
                        </a>
                    @endif
                    <p class="text-xl font-bold">{{ $entry->displayTime() }}</p>
                    <a href="{{ route('entries.show', $entry) }}" class="inline-flex min-h-11 items-center text-lg font-semibold text-sky-800 underline underline-offset-2">
                        {{ $mark($entry->activity->label()) }}
                    </a>
                </div>
                <x-status-badge :status="$entry->status" :words="$words" />
            </div>

            @if ($entry->description)
                <p class="mt-2 whitespace-pre-line wrap-anywhere">{{ $mark($entry->description) }}</p>
            @endif
            @if ($entry->hasVitals())
                <p class="mt-2 text-base text-stone-700">{{ $entry->vitalsSummary() }}</p>
            @endif

            <div class="mt-3 flex gap-2">
                <a href="{{ route('entries.edit', $entry) }}" class="btn btn-secondary">Edit</a>
                <x-confirm-delete :entry="$entry" :return-to="$returnTo" />
            </div>
        </li>
    @endforeach
</ul>
