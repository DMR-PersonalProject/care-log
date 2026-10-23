<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(ReportRequest $request): View|RedirectResponse
    {
        $report = $request->report();
        $entries = $report->entries();

        // e.g. after deleting the only entry on the last page.
        if ($entries->isEmpty() && $entries->currentPage() > 1) {
            return redirect($entries->url($entries->lastPage()));
        }

        return view('reports.index', [
            'report' => $report,
            'entries' => $entries,
            'days' => $entries->getCollection()->groupBy(fn ($entry) => $entry->entry_date->toDateString()),
            'counts' => $report->countsByActivity(),
            'averages' => $report->averageVitals(),
        ]);
    }
}
