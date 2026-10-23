<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use Illuminate\View\View;

class PrintReportController extends Controller
{
    public function __invoke(ReportRequest $request): View
    {
        $report = $request->report();
        $entries = $report->allEntries();

        return view('reports.print', [
            'report' => $report,
            'entries' => $entries,
            'days' => $entries->groupBy(fn ($entry) => $entry->entry_date->toDateString()),
            'counts' => $report->countsByActivity(),
            'averages' => $report->averageVitals(),
        ]);
    }
}
