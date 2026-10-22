<?php

use App\Enums\Activity;
use App\Enums\Status;
use App\Models\CareEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function entryInput(array $overrides = []): array
{
    return [
        'entry_date' => '2026-10-20',
        'entry_time' => '07:30',
        'activity' => 'breakfast',
        'status' => 'done',
        'description' => 'Porridge and a banana.',
        ...$overrides,
    ];
}

it('adds an entry and goes back to that day', function () {
    $this->post(route('entries.store'), entryInput())
        ->assertRedirect(route('log', ['date' => '2026-10-20']))
        ->assertSessionHas('status', 'Entry added.');

    $entry = CareEntry::sole();
    expect($entry->activity)->toBe(Activity::Breakfast)
        ->and($entry->entry_time)->toBe('07:30:00')
        ->and($entry->hasVitals())->toBeFalse();
});

it('allows the optional fields to be left blank', function () {
    $this->post(route('entries.store'), entryInput(['description' => '', 'systolic' => '', 'temperature' => '']))
        ->assertSessionHasNoErrors();

    expect(CareEntry::sole()->description)->toBeNull();
});

it('saves vitals with any entry', function () {
    $this->post(route('entries.store'), entryInput([
        'systolic' => 120, 'diastolic' => 80, 'heart_rate' => 72, 'spo2' => 97, 'temperature' => '36.8',
    ]))->assertSessionHasNoErrors();

    expect(CareEntry::sole()->vitalsSummary())->toBe('BP 120/80 · HR 72 · SpO₂ 97% · 36.8°C');
});

it('accepts vitals right at the edge of the allowed ranges', function (array $vitals) {
    $this->post(route('entries.store'), entryInput($vitals))->assertSessionHasNoErrors();
})->with([
    'lowest' => [['systolic' => 50, 'diastolic' => 30, 'heart_rate' => 20, 'spo2' => 50, 'temperature' => 30]],
    'highest' => [['systolic' => 250, 'diastolic' => 150, 'heart_rate' => 250, 'spo2' => 100, 'temperature' => 45]],
]);

it('rejects vitals outside the allowed ranges', function (string $field, mixed $value) {
    $this->post(route('entries.store'), entryInput([$field => $value]))->assertSessionHasErrors($field);

    expect(CareEntry::count())->toBe(0);
})->with([
    ['systolic', 49], ['systolic', 251],
    ['diastolic', 29], ['diastolic', 151],
    ['heart_rate', 19], ['heart_rate', 251],
    ['spo2', 49], ['spo2', 101],
    ['temperature', 29.9], ['temperature', 45.1], ['temperature', '37.25'],
]);

it('explains range errors in plain words', function () {
    $this->post(route('entries.store'), entryInput(['systolic' => 300]))
        ->assertSessionHasErrors(['systolic' => 'Top blood pressure number must be between 50 and 250.']);
});

it('requires at least one reading for a vitals check', function () {
    $this->post(route('entries.store'), entryInput(['activity' => 'vitals']))
        ->assertSessionHasErrors(['vitals' => 'Please enter at least one reading for a vitals check.']);

    $this->post(route('entries.store'), entryInput(['activity' => 'vitals', 'spo2' => 96]))
        ->assertSessionHasNoErrors();
});

it('rejects missing or unknown values', function (string $field, mixed $value) {
    $this->post(route('entries.store'), entryInput([$field => $value]))->assertSessionHasErrors($field);
})->with([
    ['activity', ''], ['activity', 'dancing'],
    ['status', 'maybe'],
    ['entry_date', ''], ['entry_date', '2026-02-30'],
    ['entry_time', '25:00'],
]);

it('shows an entry', function () {
    $entry = CareEntry::factory()->create([
        'entry_date' => '2026-10-20', 'entry_time' => '16:00:00',
        'activity' => Activity::Bath, 'status' => Status::Partial, 'description' => 'Sponge bath only.',
    ]);

    $this->get(route('entries.show', $entry))
        ->assertOk()
        ->assertSee(['Bath', 'Partial', 'Sponge bath only.', '10/20/2026', '4:00 PM']);
});

it('shows the edit form filled in', function () {
    $entry = CareEntry::factory()->create(['entry_time' => '16:00:00', 'description' => 'Sponge bath only.']);

    $this->get(route('entries.edit', $entry))
        ->assertOk()
        ->assertSee('value="16:00"', false)
        ->assertSee('Sponge bath only.');
});

it('updates an entry and goes back to its new day', function () {
    $entry = CareEntry::factory()->withVitals()->create(['entry_date' => '2026-10-20']);

    // The form always sends every vitals field; clearing them sends blanks.
    $blankVitals = array_fill_keys(CareEntry::VITAL_FIELDS, '');

    $this->put(route('entries.update', $entry), entryInput(['entry_date' => '2026-10-19', 'status' => 'refused', ...$blankVitals]))
        ->assertRedirect(route('log', ['date' => '2026-10-19']))
        ->assertSessionHas('status', 'Changes saved.');

    $entry->refresh();
    expect($entry->entry_date->toDateString())->toBe('2026-10-19')
        ->and($entry->status)->toBe(Status::Refused)
        ->and($entry->hasVitals())->toBeFalse();
});

it('deletes an entry and goes back to its day', function () {
    $entry = CareEntry::factory()->create(['entry_date' => '2026-10-20']);

    $this->delete(route('entries.destroy', $entry))
        ->assertRedirect(route('log', ['date' => '2026-10-20']))
        ->assertSessionHas('status', 'Entry deleted.');

    $this->assertModelMissing($entry);
});

it('asks before deleting', function () {
    CareEntry::factory()->create(['entry_date' => '2026-10-20']);

    $this->get(route('log', ['date' => '2026-10-20']))->assertSee('Delete this entry?');
});
