<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use Illuminate\View\View;

class PrintReportController extends Controller
{
    public function __invoke(ReportRequest $request): View
    {
        $report = $request->report();

        return view('reports.print', ['report' => $report, ...$report->printData()]);
    }
}
