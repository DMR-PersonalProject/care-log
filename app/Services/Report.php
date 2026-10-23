<?php

namespace App\Services;

use App\Enums\Activity;
use App\Enums\Status;
use App\Models\CareEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * The entries matching a date range and optional activity/status, with a summary.
 * Shared by the report page, its print view and the PDF.
 */
class Report
{
    public function __construct(
        public readonly Carbon $from,
        public readonly Carbon $to,
        public readonly ?Activity $activity = null,
        public readonly ?Status $status = null,
    ) {}

    /** @return Builder<CareEntry> */
    public function query(): Builder
    {
        return CareEntry::query()
            ->whereBetween('entry_date', [$this->from->toDateString(), $this->to->toDateString()])
            ->when($this->activity, fn (Builder $query, Activity $activity) => $query->where('activity', $activity))
            ->when($this->status, fn (Builder $query, Status $status) => $query->where('status', $status));
    }

    public function entries(int $perPage = 15): LengthAwarePaginator
    {
        return $this->ordered()->paginate($perPage)->withQueryString();
    }

    /** Every matching entry on one list, for printing. */
    public function allEntries(): Collection
    {
        return $this->ordered()->get();
    }

    /** Oldest first, like reading the notebook. */
    private function ordered(): Builder
    {
        return $this->query()->orderBy('entry_date')->orderBy('entry_time')->orderBy('id');
    }

    public function total(): int
    {
        return $this->query()->count();
    }

    /** @return array<string, int> activity label => number of entries, in the usual activity order */
    public function countsByActivity(): array
    {
        $counts = $this->query()->toBase()
            ->selectRaw('activity, count(*) as total')
            ->groupBy('activity')
            ->pluck('total', 'activity');

        return collect(Activity::cases())
            ->filter(fn (Activity $activity) => $counts->has($activity->value))
            ->mapWithKeys(fn (Activity $activity) => [$activity->label() => (int) $counts[$activity->value]])
            ->all();
    }

    /**
     * Averages over every matching entry that has the reading.
     *
     * @return list<array{label: string, value: ?string, unit: string, readings: int}>
     */
    public function averageVitals(): array
    {
        $row = $this->query()->toBase()->selectRaw(
            'avg(systolic) as systolic, avg(diastolic) as diastolic, count(systolic) as bp_readings,
             avg(heart_rate) as heart_rate, count(heart_rate) as heart_rate_readings,
             avg(spo2) as spo2, count(spo2) as spo2_readings,
             avg(temperature) as temperature, count(temperature) as temperature_readings'
        )->first();

        $round = fn (mixed $value, int $decimals = 0) => $value === null ? null : number_format((float) $value, $decimals);

        return [
            [
                'label' => 'Blood pressure',
                'value' => $row->systolic === null ? null : $round($row->systolic).'/'.($round($row->diastolic) ?? '?'),
                'unit' => 'mmHg',
                'readings' => (int) $row->bp_readings,
            ],
            ['label' => 'Heart rate', 'value' => $round($row->heart_rate), 'unit' => 'bpm', 'readings' => (int) $row->heart_rate_readings],
            ['label' => 'Oxygen (SpO₂)', 'value' => $round($row->spo2), 'unit' => '%', 'readings' => (int) $row->spo2_readings],
            ['label' => 'Temperature', 'value' => $round($row->temperature, 1), 'unit' => '°C', 'readings' => (int) $row->temperature_readings],
        ];
    }

    /** e.g. "10/17/2026 – 10/23/2026" */
    public function rangeLabel(string $separator = ' – '): string
    {
        return $this->from->isSameDay($this->to)
            ? $this->from->format('m/d/Y')
            : $this->from->format('m/d/Y').$separator.$this->to->format('m/d/Y');
    }
}
