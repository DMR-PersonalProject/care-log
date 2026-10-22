<?php

namespace App\Models;

use App\Enums\Activity;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CareEntry extends Model
{
    /** @use HasFactory<\Database\Factories\CareEntryFactory> */
    use HasFactory;

    public const VITAL_FIELDS = ['systolic', 'diastolic', 'heart_rate', 'spo2', 'temperature'];

    protected $fillable = [
        'entry_date',
        'entry_time',
        'activity',
        'status',
        'description',
        'systolic',
        'diastolic',
        'heart_rate',
        'spo2',
        'temperature',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date:Y-m-d',
            'activity' => Activity::class,
            'status' => Status::class,
            'systolic' => 'integer',
            'diastolic' => 'integer',
            'heart_rate' => 'integer',
            'spo2' => 'integer',
            'temperature' => 'decimal:1',
        ];
    }

    /**
     * Forms send HH:MM; store HH:MM:SS so times always sort and compare the same way.
     */
    protected function entryTime(): Attribute
    {
        return Attribute::set(fn (?string $value) => $value !== null && strlen($value) === 5 ? $value.':00' : $value);
    }

    public function scopeForDay(Builder $query, Carbon|string $date): void
    {
        $query->whereDate('entry_date', $date instanceof Carbon ? $date->toDateString() : $date)
            ->orderBy('entry_time')
            ->orderBy('id');
    }

    public function displayDate(): string
    {
        return $this->entry_date->format('m/d/Y');
    }

    public function displayTime(): string
    {
        return Carbon::parse($this->entry_time)->format('g:i A');
    }

    public function hasVitals(): bool
    {
        return collect(self::VITAL_FIELDS)->contains(fn (string $field) => $this->{$field} !== null);
    }

    /** e.g. "BP 120/80 · HR 72 · SpO₂ 97% · 36.8°C" */
    public function vitalsSummary(): string
    {
        $parts = array_filter([
            $this->systolic || $this->diastolic ? 'BP '.($this->systolic ?? '?').'/'.($this->diastolic ?? '?') : null,
            $this->heart_rate ? 'HR '.$this->heart_rate : null,
            $this->spo2 ? 'SpO₂ '.$this->spo2.'%' : null,
            $this->temperature !== null ? $this->temperature.'°C' : null,
        ]);

        return $parts ? implode(' · ', $parts) : '—';
    }
}
