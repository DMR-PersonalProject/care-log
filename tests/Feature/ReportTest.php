<?php

use App\Enums\Activity;
use App\Enums\Status;
use App\Models\CareEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->travelTo('2026-10-23 15:00:00'));

function reportEntry(string $date, Activity $activity = Activity::Other, array $attributes = []): CareEntry
{
    return CareEntry::factory()->create([
        'entry_date' => $date,
        'entry_time' => '08:00:00',
        'activity' => $activity,
        'status' => Status::Done,
        'description' => null,
        'systolic' => null, 'diastolic' => null, 'heart_rate' => null, 'spo2' => null, 'temperature' => null,
        ...$attributes,
    ]);
}

it('is linked from the menu', function () {
    $this->get(route('dashboard'))->assertSee(route('reports'), false)->assertSee('Reports');
});

it('covers the last 7 days by default', function () {
    reportEntry('2026-10-16', attributes: ['description' => 'Eight days ago']);
    reportEntry('2026-10-17', attributes: ['description' => 'Six days ago']);
    reportEntry('2026-10-23', attributes: ['description' => 'Today']);
    reportEntry('2026-10-24', attributes: ['description' => 'Tomorrow']);

    $this->get(route('reports'))
        ->assertOk()
        ->assertSee('10/17/2026 – 10/23/2026')
        ->assertSee('value="2026-10-17"', false)
        ->assertSee('2 entries')
        ->assertSeeInOrder(['Six days ago', 'Today'])
        ->assertDontSee('Eight days ago')
        ->assertDontSee('Tomorrow');
});

it('includes both ends of the chosen date range', function () {
    reportEntry('2026-09-30', attributes: ['description' => 'Before']);
    reportEntry('2026-10-01', attributes: ['description' => 'First day']);
    reportEntry('2026-10-05', attributes: ['description' => 'Last day']);
    reportEntry('2026-10-06', attributes: ['description' => 'After']);

    $this->get(route('reports', ['from' => '2026-10-01', 'to' => '2026-10-05']))
        ->assertSee('10/01/2026 – 10/05/2026')
        ->assertSee(['First day', 'Last day'])
        ->assertDontSee('Before')
        ->assertDontSee('After');
});

it('runs from the start date to today when no end date is chosen', function () {
    $this->get(route('reports', ['from' => '2026-10-01']))->assertSee('10/01/2026 – 10/23/2026');
});

it('filters by activity and by status, alone or together', function () {
    reportEntry('2026-10-22', Activity::Lunch, ['status' => Status::Refused, 'description' => 'Refused lunch']);
    reportEntry('2026-10-22', Activity::Lunch, ['description' => 'Ate lunch']);
    reportEntry('2026-10-22', Activity::Dinner, ['status' => Status::Refused, 'description' => 'Refused dinner']);

    $this->get(route('reports', ['activity' => 'lunch']))
        ->assertSee(['Refused lunch', 'Ate lunch'])->assertDontSee('Refused dinner');

    $this->get(route('reports', ['status' => 'refused']))
        ->assertSee(['Refused lunch', 'Refused dinner'])->assertDontSee('Ate lunch');

    $this->get(route('reports', ['activity' => 'lunch', 'status' => 'refused']))
        ->assertSee('Lunch · Refused')
        ->assertSee('Refused lunch')
        ->assertDontSee('Ate lunch')
        ->assertDontSee('Refused dinner');
});

it('counts the matching entries per activity, in the usual order', function () {
    reportEntry('2026-10-20', Activity::Medicine);
    reportEntry('2026-10-21', Activity::Medicine);
    reportEntry('2026-10-21', Activity::Breakfast);
    reportEntry('2026-10-01', Activity::Bath);

    $response = $this->get(route('reports'));

    $counts = Str::between($response->getContent(), 'Entries per activity', 'Average vitals');
    expect($counts)->toContain('Breakfast')->toContain('Medicine')->not->toContain('Bath')
        ->and(strpos($counts, 'Breakfast'))->toBeLessThan(strpos($counts, 'Medicine'));
    $response->assertSeeInOrder(['Breakfast', '1', 'Medicine', '2']);
});

it('averages the vitals of the matching entries only', function () {
    reportEntry('2026-10-21', Activity::Vitals, ['systolic' => 120, 'diastolic' => 80, 'heart_rate' => 70, 'temperature' => 36.5]);
    reportEntry('2026-10-22', Activity::Vitals, ['systolic' => 131, 'diastolic' => 85, 'temperature' => 37.0]);
    reportEntry('2026-10-22', Activity::Breakfast);
    reportEntry('2026-09-01', Activity::Vitals, ['systolic' => 200, 'diastolic' => 120]);

    $this->get(route('reports'))
        ->assertSeeInOrder(['Average vitals', 'Blood pressure', '126/83 mmHg', '(2 readings)'])
        ->assertSeeInOrder(['Heart rate', '70 bpm', '(1 reading)'])
        ->assertSeeInOrder(['Oxygen (SpO₂)', 'No readings'])
        ->assertSeeInOrder(['Temperature', '36.8 °C', '(2 readings)']);
});

it('groups the entries by day, oldest first', function () {
    reportEntry('2026-10-22', attributes: ['entry_time' => '18:00:00', 'description' => 'Thursday evening']);
    reportEntry('2026-10-21', attributes: ['description' => 'Wednesday morning']);
    reportEntry('2026-10-22', attributes: ['entry_time' => '07:00:00', 'description' => 'Thursday morning']);

    $this->get(route('reports'))
        ->assertSeeInOrder(['Wednesday, 10/21/2026', 'Wednesday morning', 'Thursday, 10/22/2026', 'Thursday morning', 'Thursday evening'])
        ->assertSee(route('log', ['date' => '2026-10-21']), false);
});

it('shows 15 entries per page and keeps the filters in the page links', function () {
    foreach (range(1, 17) as $minute) {
        reportEntry('2026-10-22', Activity::Urination, ['entry_time' => sprintf('06:%02d:00', $minute), 'description' => "Trip {$minute}."]);
    }

    $this->get(route('reports', ['activity' => 'urination']))
        ->assertSee('Showing 1–15 of 17.')
        ->assertSee('Trip 15.')
        ->assertDontSee('Trip 16.')
        ->assertSee(e(route('reports', ['activity' => 'urination', 'page' => 2])), false)
        // The summary still counts all 17, not just this page.
        ->assertSeeInOrder(['Entries per activity', 'Urination', '17']);

    $this->get(route('reports', ['activity' => 'urination', 'page' => 2]))
        ->assertSee(['Trip 16.', 'Trip 17.'])
        ->assertDontSee('Trip 15.');
});

it('says so when nothing matches', function () {
    $this->get(route('reports'))
        ->assertOk()
        ->assertSee('0 entries')
        ->assertSee('No entries match these choices.')
        ->assertDontSee('Entries per activity');
});

it('explains a bad choice in plain words and keeps what was typed', function (array $query, string $field, string $message) {
    $this->get(route('reports', $query))
        ->assertRedirect(route('reports'))
        ->assertSessionHasErrors([$field => $message])
        ->assertSessionHasInput($field, $query[$field]);
})->with([
    'end before start' => [['from' => '2026-10-10', 'to' => '2026-10-01'], 'to', 'The end date must be the same as or after the start date.'],
    'not a date' => [['from' => '2026-02-30'], 'from', 'Please choose a valid date.'],
    'unknown activity' => [['activity' => 'dancing'], 'activity', 'Please choose an activity from the list.'],
    'unknown status' => [['status' => 'maybe'], 'status', 'Please choose a status from the list.'],
]);

it('shows the error on the report form after a bad choice', function () {
    $this->followingRedirects()
        ->get(route('reports', ['from' => '2026-10-10', 'to' => '2026-10-01']))
        ->assertOk()
        ->assertSee('The end date must be the same as or after the start date.')
        ->assertSee('value="2026-10-01"', false);
});
