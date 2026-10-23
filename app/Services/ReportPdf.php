<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as Document;

/** The report as a landscape Letter PDF, with the date range and page numbers in every page's footer. */
class ReportPdf
{
    public static function make(Report $report): Document
    {
        $pdf = Pdf::loadView('reports.pdf', ['report' => $report, ...$report->printData()])
            ->setPaper('letter', 'landscape')
            ->setOption(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false]);

        // The page count is only known once the layout is done, so the footer is drawn afterwards.
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $size = 8;
        $grey = [0.35, 0.35, 0.35];
        $y = $canvas->get_height() - 28;
        $pageText = 'Page {PAGE_NUM} of {PAGE_COUNT}';
        $pageTextWidth = $dompdf->getFontMetrics()->getTextWidth('Page 99 of 99', $font, $size);

        $canvas->page_text(36, $y, config('app.name').' Report · '.$report->rangeLabel(), $font, $size, $grey);
        $canvas->page_text($canvas->get_width() - 36 - $pageTextWidth, $y, $pageText, $font, $size, $grey);

        return $pdf;
    }
}
