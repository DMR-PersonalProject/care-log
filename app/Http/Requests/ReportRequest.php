<?php

namespace App\Http\Requests;

use App\Enums\Activity;
use App\Enums\Status;
use App\Services\Report;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
{
    /** On a bad choice, go back to the plain report form (with the error) rather than to the previous page. */
    protected $redirectRoute = 'reports';

    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', Rule::when($this->filled('from'), 'after_or_equal:from')],
            'activity' => ['nullable', Rule::enum(Activity::class)],
            'status' => ['nullable', Rule::enum(Status::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'date_format' => 'Please choose a valid date.',
            'to.after_or_equal' => 'The end date must be the same as or after the start date.',
            'activity.enum' => 'Please choose an activity from the list.',
            'status.enum' => 'Please choose a status from the list.',
        ];
    }

    /**
     * Without dates, the report covers the last 7 days up to today.
     * With only a start date, it runs to today (or just that day, if it is in the future).
     */
    public function report(): Report
    {
        $to = $this->filled('to') ? Carbon::createFromFormat('!Y-m-d', $this->validated('to')) : today();
        $from = $this->filled('from') ? Carbon::createFromFormat('!Y-m-d', $this->validated('from')) : $to->copy()->subDays(6);

        if ($from->greaterThan($to)) {
            $to = $from->copy();
        }

        return new Report(
            $from,
            $to,
            Activity::tryFrom((string) $this->validated('activity')),
            Status::tryFrom((string) $this->validated('status')),
        );
    }
}
