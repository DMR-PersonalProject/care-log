<?php

namespace App\Models;

use App\Enums\Activity;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareEntry extends Model
{
    /** @use HasFactory<\Database\Factories\CareEntryFactory> */
    use HasFactory;

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

    public function hasVitals(): bool
    {
        return collect(['systolic', 'diastolic', 'heart_rate', 'spo2', 'temperature'])
            ->contains(fn (string $field) => $this->{$field} !== null);
    }
}
