{{-- Rendered by dompdf, which cannot use Tailwind, so the styles are plain CSS here. --}}
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>{{ config('app.name') }} Report {{ $report->rangeLabel(' - ') }}</title>
        <style>
            @page { margin: 36pt 36pt 48pt 36pt; }
            body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #1c1917; }
            h1 { font-size: 18pt; margin: 0; }
            h2 { font-size: 13pt; margin: 16pt 0 6pt; }
            h3 { font-size: 11pt; margin: 12pt 0 4pt; page-break-after: avoid; }
            .range { font-size: 14pt; font-weight: bold; margin: 2pt 0; }
            .muted { color: #57534e; margin: 2pt 0; }
            .header { border-bottom: 1.5pt solid #1c1917; padding-bottom: 8pt; }

            table { width: 100%; border-collapse: collapse; }
            th, td { border: 0.75pt solid #a8a29e; padding: 4pt 6pt; text-align: left; vertical-align: top; }
            thead th { background: #e7e5e4; }
            tr { page-break-inside: avoid; }
            .number { text-align: right; }

            .layout, .layout > tbody > tr > td { border: 0; padding: 0; }
            .layout > tbody > tr > td { width: 50%; }

            .entries { table-layout: fixed; }
            /* Long descriptions (even long unbroken words or links) wrap inside the cell. */
            .wrap { word-wrap: break-word; overflow-wrap: anywhere; white-space: pre-line; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>{{ config('app.name') }} Report</h1>
            <p class="range">{{ $report->rangeLabel() }}</p>
            <p class="muted">
                {{ $report->activity?->label() ?? 'All activities' }} · {{ $report->status?->label() ?? 'All statuses' }}
                · {{ $entries->count() }} {{ Str::plural('entry', $entries->count()) }}
            </p>
            <p class="muted">Created {{ now()->format('m/d/Y g:i A') }}</p>
        </div>

        @if ($entries->isEmpty())
            <p>No entries match these choices.</p>
        @else
            <table class="layout">
                <tbody>
                    <tr>
                        <td style="padding-right: 12pt;">
                            <h2>Entries per activity</h2>
                            <table>
                                <tbody>
                                    @foreach ($counts as $label => $count)
                                        <tr>
                                            <td>{{ $label }}</td>
                                            <td class="number"><strong>{{ $count }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                        <td style="padding-left: 12pt;">
                            <h2>Average vitals</h2>
                            <table>
                                <tbody>
                                    @foreach ($averages as $average)
                                        <tr>
                                            <td>{{ $average['label'] }}</td>
                                            <td class="number">
                                                @if ($average['value'] !== null)
                                                    <strong>{{ $average['value'] }} {{ $average['unit'] }}</strong>
                                                    ({{ $average['readings'] }} {{ Str::plural('reading', $average['readings']) }})
                                                @else
                                                    No readings
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h2>Entries</h2>

            @foreach ($days as $date => $dayEntries)
                <h3>{{ $dayEntries->first()->entry_date->format('l') }}, {{ $dayEntries->first()->displayDate() }}</h3>

                <table class="entries">
                    <thead>
                        <tr>
                            <th style="width: 10%;">Time</th>
                            <th style="width: 13%;">Activity</th>
                            <th style="width: 12%;">Status</th>
                            <th style="width: 43%;">Description</th>
                            <th style="width: 22%;">Vitals</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dayEntries as $entry)
                            <tr>
                                <td><strong>{{ $entry->displayTime() }}</strong></td>
                                <td>{{ $entry->activity->label() }}</td>
                                <td>{{ $entry->status->label() }}</td>
                                <td class="wrap">{{ $entry->description }}</td>
                                <td class="wrap">{{ $entry->vitalsSummary() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endif
    </body>
</html>
