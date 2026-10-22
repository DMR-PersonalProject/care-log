<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\CareEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DailyLogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $date = $this->dateFrom($request->query('date'));

        return view('log.index', [
            'date' => $date,
            'entries' => CareEntry::forDay($date)->get(),
            'newEntry' => new CareEntry([
                'entry_date' => $date,
                'entry_time' => now()->format('H:i'),
                'status' => Status::Done,
            ]),
        ]);
    }

    /**
     * A missing or invalid date (including ones like 2026-02-30) falls back to today.
     */
    private function dateFrom(mixed $value): Carbon
    {
        $date = is_string($value) ? rescue(fn () => Carbon::createFromFormat('!Y-m-d', $value), report: false) : null;

        return $date && $date->format('Y-m-d') === $value ? $date : today();
    }
}
