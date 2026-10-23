<?php

namespace App\Http\Controllers;

use App\Models\CareEntry;
use App\Services\DashboardSummary;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardSummary $summary): View
    {
        $days = $request->query('days') === '30' ? 30 : 7;
        $readings = $summary->vitalsReadings($days);

        return view('dashboard', [
            'meals' => $summary->mealsToday(),
            'medicine' => $summary->medicineToday(),
            'lastEntries' => $summary->lastEntries(),
            'vitals' => $summary->latestVitals(),
            'days' => $days,
            'readings' => $readings,
            'chartData' => $summary->chartData($readings),
            'recent' => CareEntry::newestFirst()->limit(8)->get(),
        ]);
    }
}
