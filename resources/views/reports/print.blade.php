@extends('layouts.print')

@section('title', config('app.name').' Report '.$report->rangeLabel(' - '))

@section('content')
    {{-- Shown on screen only --}}
    <div class="print-hidden mx-auto flex max-w-5xl flex-wrap gap-3 px-4 pt-6">
        <button type="button" onclick="window.print()" class="btn btn-primary">Print</button>
        @if ($entries->isNotEmpty())
            <a href="{{ route('reports.pdf', request()->query()) }}" class="btn btn-secondary">Download PDF</a>
        @endif
        <a href="{{ route('reports', request()->query()) }}" class="btn btn-secondary">Back to report</a>
    </div>

    <article class="print-sheet mx-auto my-6 max-w-5xl bg-white p-6 shadow-sm sm:p-10">
        <header class="mb-6 border-b-2 border-stone-800 pb-4">
            <h1 class="text-3xl font-bold">{{ config('app.name') }} Report</h1>
            <p class="text-2xl font-semibold">{{ $report->rangeLabel() }}</p>
            <p class="mt-1 text-stone-700">
                {{ $report->activity?->label() ?? 'All activities' }} · {{ $report->status?->label() ?? 'All statuses' }}
                · {{ $entries->count() }} {{ Str::plural('entry', $entries->count()) }}
            </p>
            <p class="text-base text-stone-600">Printed {{ now()->format('m/d/Y g:i A') }}</p>
        </header>

        @if ($entries->isEmpty())
            <p>No entries match these choices.</p>
        @else
            <section class="print-summary mb-8 grid gap-6 sm:grid-cols-2 print:grid-cols-2">
                <div>
                    <h2 class="mb-2 text-xl font-bold">Entries per activity</h2>
                    <table class="print-table w-full">
                        <tbody>
                            @foreach ($counts as $label => $count)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-right font-semibold">{{ $count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div>
                    <h2 class="mb-2 text-xl font-bold">Average vitals</h2>
                    <table class="print-table w-full">
                        <tbody>
                            @foreach ($averages as $average)
                                <tr>
                                    <td>{{ $average['label'] }}</td>
                                    <td class="text-right">
                                        @if ($average['value'] !== null)
                                            <span class="font-semibold">{{ $average['value'] }} {{ $average['unit'] }}</span>
                                            ({{ $average['readings'] }} {{ Str::plural('reading', $average['readings']) }})
                                        @else
                                            No readings
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-bold">Entries</h2>

                @foreach ($days as $date => $dayEntries)
                    <div class="print-day mb-6">
                        <h3 class="print-day-heading mb-2 text-lg font-bold">
                            {{ $dayEntries->first()->entry_date->format('l') }}, {{ $dayEntries->first()->displayDate() }}
                        </h3>

                        {{-- Fixed column widths so a long description wraps in its own cell instead of widening the table. --}}
                        <div class="overflow-x-auto print:overflow-visible">
                            <table class="print-table print-entries w-full min-w-[40rem] table-fixed text-left print:min-w-0">
                                <colgroup>
                                    <col class="w-[12%]">
                                    <col class="w-[14%]">
                                    <col class="w-[15%]">
                                    <col>
                                    <col class="w-[22%]">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th scope="col">Time</th>
                                        <th scope="col">Activity</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Vitals</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dayEntries as $entry)
                                        <tr class="align-top">
                                            <td class="font-semibold">{{ $entry->displayTime() }}</td>
                                            <td>{{ $entry->activity->label() }}</td>
                                            <td>{{ $entry->status->label() }}</td>
                                            <td class="print-wrap whitespace-pre-line">{{ $entry->description }}</td>
                                            <td class="print-wrap">{{ $entry->vitalsSummary() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </section>
        @endif
    </article>
@endsection
