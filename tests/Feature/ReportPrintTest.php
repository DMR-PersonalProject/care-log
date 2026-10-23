<?php

use App\Enums\Activity;
use App\Enums\Status;
use App\Models\CareEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->travelTo('2026-10-23 15:00:00'));

function printEntry(string $date, Activity $activity = Activity::Other, array $attributes = []): CareEntry
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

it('is linked from the report page with the same choices', function () {
    printEntry('2026-10-22', Activity::Lunch);

    $this->get(route('reports', ['from' => '2026-10-01', 'activity' => 'lunch', 'page' => 1]))
        ->assertSee('Print report')
        ->assertSee(e(route('reports.print', ['from' => '2026-10-01', 'activity' => 'lunch'])), false);
});

it('has no print link when nothing matches', function () {
    $this->get(route('reports'))->assertDontSee('Print report');
});

it('shows a header with the date range and choices, and a useful page title', function () {
    printEntry('2026-10-20', Activity::Lunch, ['status' => Status::Refused]);

    $this->get(route('reports.print', ['from' => '2026-10-16', 'activity' => 'lunch', 'status' => 'refused']))
        ->assertOk()
        ->assertSee('<title>Care Log Report 10/16/2026 - 10/23/2026</title>', false)
        ->assertSee('10/16/2026 – 10/23/2026')
        ->assertSeeInOrder(['Lunch · Refused', '· 1 entry'])
        ->assertSee('Printed 10/23/2026 3:00 PM');
});

it('uses a single date in the title for a one-day report', function () {
    $this->get(route('reports.print', ['from' => '2026-10-20', 'to' => '2026-10-20']))
        ->assertSee('<title>Care Log Report 10/20/2026</title>', false);
});

it('has no menu, links or entry buttons', function () {
    $entry = printEntry('2026-10-22', attributes: ['description' => 'Walked in the garden']);

    $this->get(route('reports.print'))
        ->assertSee('Walked in the garden')
        ->assertDontSee('All Entries')
        ->assertDontSee('Daily Log')
        ->assertDontSee(route('entries.edit', $entry), false)
        ->assertDontSee(route('entries.show', $entry), false)
        ->assertDontSee('Delete');
});

it('follows the same filters as the report page', function () {
    printEntry('2026-10-22', Activity::Lunch, ['status' => Status::Refused, 'description' => 'Refused lunch']);
    printEntry('2026-10-22', Activity::Lunch, ['description' => 'Ate lunch']);
    printEntry('2026-10-22', Activity::Dinner, ['status' => Status::Refused, 'description' => 'Refused dinner']);
    printEntry('2026-10-01', Activity::Lunch, ['status' => Status::Refused, 'description' => 'Too early']);

    $this->get(route('reports.print', ['activity' => 'lunch', 'status' => 'refused']))
        ->assertSee('Refused lunch')
        ->assertDontSee('Ate lunch')
        ->assertDontSee('Refused dinner')
        ->assertDontSee('Too early');
});

it('prints every matching entry on one list, grouped by day', function () {
    foreach (range(1, 20) as $minute) {
        printEntry('2026-10-22', Activity::Urination, ['entry_time' => sprintf('06:%02d:00', $minute), 'description' => "Trip {$minute}."]);
    }
    printEntry('2026-10-21', attributes: ['description' => 'The day before']);

    $this->get(route('reports.print'))
        ->assertSee('21 entries')
        ->assertSeeInOrder(['Wednesday, 10/21/2026', 'The day before', 'Thursday, 10/22/2026', 'Trip 1.', 'Trip 20.'])
        ->assertDontSee('Showing')
        ->assertSeeInOrder(['Entries per activity', 'Urination', '20']);
});

it('includes the average vitals and each entry\'s vitals', function () {
    printEntry('2026-10-21', Activity::Vitals, ['systolic' => 120, 'diastolic' => 80, 'heart_rate' => 72]);

    $this->get(route('reports.print'))
        ->assertSeeInOrder(['Average vitals', 'Blood pressure', '120/80 mmHg', '(1 reading)'])
        ->assertSee('BP 120/80 · HR 72');
});

it('keeps long descriptions whole in a wrapping cell', function () {
    $long = str_repeat('She talked about her garden and the old house. ', 30).'https://example.com/'.str_repeat('a', 200);
    printEntry('2026-10-22', attributes: ['description' => $long]);

    $this->get(route('reports.print'))
        ->assertSee($long)
        ->assertSee('print-wrap whitespace-pre-line', false)
        ->assertSee('table-fixed', false);
});

it('says so when nothing matches', function () {
    $this->get(route('reports.print'))->assertOk()->assertSee('0 entries')->assertSee('No entries match these choices.');
});

it('sends a bad choice back to the report form with the message', function () {
    $this->get(route('reports.print', ['from' => '2026-10-10', 'to' => '2026-10-01']))
        ->assertRedirect(route('reports'))
        ->assertSessionHasErrors(['to' => 'The end date must be the same as or after the start date.']);
});
