<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Services\ReportPdf;
use Illuminate\Http\Response;

class DownloadReportPdfController extends Controller
{
    public function __invoke(ReportRequest $request): Response
    {
        $report = $request->report();

        return ReportPdf::make($report)->download($report->fileName('pdf'));
    }
}
