<?php

use App\Enums\Activity;
use App\Enums\Status;
use App\Models\CareEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates entries from the factory', function () {
    CareEntry::factory()->count(5)->create();

    expect(CareEntry::count())->toBe(5);
});

it('round-trips enums, date and time through the database', function () {
    $entry = CareEntry::factory()->create([
        'entry_date' => '2026-10-21',
        'entry_time' => '07:30:00',
        'activity' => Activity::Pedal,
        'status' => Status::NeedsAttention,
    ])->fresh();

    expect($entry->activity)->toBe(Activity::Pedal)
        ->and($entry->status)->toBe(Status::NeedsAttention)
        ->and($entry->entry_date->format('m/d/Y'))->toBe('10/21/2026')
        ->and($entry->entry_time)->toBe('07:30:00')
        ->and($entry->getRawOriginal('activity'))->toBe('pedal');
});

it('keeps one decimal place on temperature', function () {
    $entry = CareEntry::factory()->create(['temperature' => 37])->fresh();

    expect($entry->temperature)->toBe('37.0');
});

it('allows an entry with no vitals', function () {
    $entry = CareEntry::factory()->create([
        'activity' => Activity::Bath,
        'systolic' => null, 'diastolic' => null, 'heart_rate' => null, 'spo2' => null, 'temperature' => null,
    ])->fresh();

    expect($entry->hasVitals())->toBeFalse();
});

it('knows when an entry has vitals', function () {
    expect(CareEntry::factory()->withVitals()->create()->fresh()->hasVitals())->toBeTrue();
});

it('seeds a realistic week of entries', function () {
    $this->seed();

    expect(CareEntry::count())->toBeGreaterThan(50)
        ->and(CareEntry::where('activity', Activity::Vitals)->whereNull('systolic')->count())->toBe(0);
});
