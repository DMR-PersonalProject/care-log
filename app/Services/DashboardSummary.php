<?php

namespace App\Services;

use App\Enums\Activity;
use App\Enums\Status;
use App\Models\CareEntry;
use App\Support\Vitals;
use Illuminate\Support\Collection;

class DashboardSummary
{
    /**
     * Today's breakfast, lunch and dinner (null if not logged) and how many snacks.
     * If a meal was logged twice, the later entry counts.
     *
     * @return array{meals: array<string, ?CareEntry>, done: int, snacks: int}
     */
    public function mealsToday(): array
    {
        $entries = CareEntry::forDay(today())->whereIn('activity', Activity::meals())->get();

        $meals = collect([Activity::Breakfast, Activity::Lunch, Activity::Dinner])
            ->mapWithKeys(fn (Activity $meal) => [$meal->label() => $entries->where('activity', $meal)->last()]);

        return [
            'meals' => $meals->all(),
            'done' => $meals->filter(fn (?CareEntry $entry) => $entry?->status === Status::Done)->count(),
            'snacks' => $entries->where('activity', Activity::Snack)->count(),
        ];
    }

    /** @return Collection<int, CareEntry> */
    public function medicineToday(): Collection
    {
        return CareEntry::forDay(today())->where('activity', Activity::Medicine)->get();
    }

    /** @return array<string, ?CareEntry> card title => the most recent entry */
    public function lastEntries(): array
    {
        return collect([
            'Last bath' => Activity::Bath,
            'Last urination' => Activity::Urination,
            'Last dentures entry' => Activity::Dentures,
        ])->map(fn (Activity $activity) => CareEntry::where('activity', $activity)->upToToday()->newestFirst()->first())->all();
    }

    /**
     * The most recent reading of each vital, for the dashboard tiles.
     *
     * @return list<array{label: string, value: ?string, unit: string, range: string, flag: ?string, entry: ?CareEntry}>
     */
    public function latestVitals(): array
    {
        $latest = fn (string $field) => CareEntry::whereNotNull($field)->upToToday()->newestFirst()->first();

        $bp = $latest('systolic');
        $heartRate = $latest('heart_rate');
        $spo2 = $latest('spo2');
        $temperature = $latest('temperature');

        return [
            [
                'label' => 'Blood pressure',
                'value' => $bp ? $bp->systolic.'/'.($bp->diastolic ?? '?') : null,
                'unit' => 'mmHg',
                'range' => Vitals::range('systolic').' / '.Vitals::range('diastolic'),
                'flag' => $bp ? (collect([$bp->vitalFlag('systolic'), $bp->vitalFlag('diastolic')])->filter()->unique()->implode(' / ') ?: null) : null,
                'entry' => $bp,
            ],
            $this->tile('Heart rate', 'heart_rate', 'bpm', $heartRate),
            $this->tile('Oxygen (SpO₂)', 'spo2', '%', $spo2),
            $this->tile('Temperature', 'temperature', '°C', $temperature),
        ];
    }

    /**
     * Entries with any vitals from the last $days days (today included), oldest first.
     *
     * @return Collection<int, CareEntry>
     */
    public function vitalsReadings(int $days): Collection
    {
        return CareEntry::query()
            ->whereBetween('entry_date', [today()->subDays($days - 1)->toDateString(), today()->toDateString()])
            ->where(function ($query) {
                foreach (CareEntry::VITAL_FIELDS as $field) {
                    $query->orWhereNotNull($field);
                }
            })
            ->orderBy('entry_date')
            ->orderBy('entry_time')
            ->orderBy('id')
            ->get();
    }

    /**
     * The readings as plain data for the charts.
     *
     * @param  Collection<int, CareEntry>  $readings
     */
    public function chartData(Collection $readings): array
    {
        return $readings->map(fn (CareEntry $entry) => [
            'label' => $entry->entry_date->format('m/d').' '.$entry->displayTime(),
            ...collect(CareEntry::VITAL_FIELDS)->mapWithKeys(fn (string $field) => [
                $field => $entry->{$field} === null ? null : (float) $entry->{$field},
            ]),
            'flags' => (object) collect(CareEntry::VITAL_FIELDS)
                ->mapWithKeys(fn (string $field) => [$field => $entry->vitalFlag($field)])
                ->filter()
                ->all(),
        ])->values()->all();
    }

    private function tile(string $label, string $field, string $unit, ?CareEntry $entry): array
    {
        return [
            'label' => $label,
            'value' => $entry ? (string) $entry->{$field} : null,
            'unit' => $unit,
            'range' => Vitals::range($field),
            'flag' => $entry?->vitalFlag($field),
            'entry' => $entry,
        ];
    }
}
