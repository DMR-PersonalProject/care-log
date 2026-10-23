<?php

use App\Enums\Activity;
use App\Enums\Status;
use App\Models\CareEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->travelTo('2026-10-23 15:00:00'));

function logEntry(Activity $activity, array $attributes = []): CareEntry
{
    return CareEntry::factory()->create([
        'entry_date' => '2026-10-23',
        'entry_time' => '08:00:00',
        'activity' => $activity,
        'status' => Status::Done,
        'description' => null,
        'systolic' => null, 'diastolic' => null, 'heart_rate' => null, 'spo2' => null, 'temperature' => null,
        ...$attributes,
    ]);
}

it('is the home page, with links to the other screens', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Today, 10/23/2026')
        ->assertSee('Friday')
        ->assertSee(route('log'), false)
        ->assertSee(route('entries.index'), false)
        ->assertSee('aria-current="page"', false);
});

it('works with nothing logged yet', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('0 of 3 meals done')
        ->assertSee('0 given today')
        ->assertSee('None logged yet today.')
        ->assertSee('No reading yet')
        ->assertSee('No readings in this period.')
        ->assertSee('Nothing logged yet.')
        ->assertDontSee('Show readings as a table');
});

it('counts today\'s meals', function () {
    logEntry(Activity::Breakfast, ['entry_time' => '07:30:00']);
    logEntry(Activity::Lunch, ['entry_time' => '12:00:00', 'status' => Status::Refused]);
    logEntry(Activity::Snack);
    logEntry(Activity::Snack);
    logEntry(Activity::Dinner, ['entry_date' => '2026-10-22']);

    $this->get('/')
        ->assertSee('1 of 3 meals done')
        ->assertSeeInOrder(['Breakfast', '7:30 AM', 'Done', 'Lunch', '12:00 PM', 'Refused', 'Dinner', 'Not logged yet', 'Snacks', '2']);
});

it('counts the medicine given today and shows refused doses', function () {
    logEntry(Activity::Medicine, ['entry_time' => '08:00:00', 'description' => 'Morning pills']);
    logEntry(Activity::Medicine, ['entry_time' => '12:00:00', 'status' => Status::Refused, 'description' => 'Spat out the noon pill']);
    logEntry(Activity::Medicine, ['entry_time' => '13:00:00']);
    logEntry(Activity::Medicine, ['entry_date' => '2026-10-22']);

    $this->get('/')
        ->assertSee('2 given today')
        ->assertSee('Spat out the noon pill');
});

it('shows the latest bath, urination and dentures entries', function () {
    logEntry(Activity::Bath, ['entry_date' => '2026-10-19', 'entry_time' => '16:00:00', 'description' => 'Older bath']);
    logEntry(Activity::Bath, ['entry_date' => '2026-10-20', 'entry_time' => '16:00:00', 'description' => 'Sponge bath']);
    logEntry(Activity::Bath, ['entry_date' => '2026-10-30', 'description' => 'Typed with the wrong date']);
    logEntry(Activity::Urination, ['entry_time' => '10:45:00']);
    logEntry(Activity::Dentures, ['entry_date' => '2026-10-22', 'entry_time' => '20:00:00', 'description' => 'Removed and soaked']);

    $response = $this->get('/')
        ->assertSeeInOrder(['Last bath', 'Tue 10/20/2026, 4:00 PM', '3 days ago', 'Sponge bath'])
        ->assertSeeInOrder(['Last urination', 'Today, 10:45 AM'])
        ->assertSeeInOrder(['Last dentures entry', 'Yesterday, 8:00 PM', 'Removed and soaked']);

    // The recent entries list further down shows everything, so only look at the cards.
    $glance = Str::between($response->getContent(), 'Today at a glance', 'Latest vitals');
    expect($glance)->not->toContain('Older bath')->not->toContain('Typed with the wrong date');
});

it('says when a routine item has never been logged', function () {
    $this->get('/')->assertSeeInOrder(['Last bath', 'Not logged yet', 'Last urination', 'Not logged yet']);
});

it('shows the latest reading of each vital and flags unusual ones', function () {
    logEntry(Activity::Vitals, ['entry_date' => '2026-10-22', 'systolic' => 120, 'diastolic' => 80, 'heart_rate' => 70, 'spo2' => 97, 'temperature' => 36.8]);
    logEntry(Activity::Other, ['entry_time' => '09:00:00', 'systolic' => 158, 'diastolic' => 96, 'temperature' => 37.9]);
    logEntry(Activity::Vitals, ['entry_time' => '10:00:00', 'spo2' => 92]);

    $response = $this->get('/');

    $response->assertSeeInOrder(['Blood pressure', '⚠ High', '158/96', 'Today, 9:00 AM', 'Usual: 90–140 / 60–90'])
        ->assertSeeInOrder(['Heart rate', '70', 'Yesterday, 8:00 AM'])
        ->assertSeeInOrder(['Oxygen (SpO₂)', '⚠ Low', '92', 'Today, 10:00 AM'])
        ->assertSeeInOrder(['Temperature', '⚠ High', '37.9']);

    expect(substr_count($response->getContent(), '⚠ '))->toBe(3);
});

it('charts the readings from the last 7 days by default', function () {
    logEntry(Activity::Vitals, ['entry_date' => '2026-10-16', 'heart_rate' => 61]);
    logEntry(Activity::Vitals, ['entry_date' => '2026-10-17', 'heart_rate' => 62]);
    logEntry(Activity::Vitals, ['entry_date' => '2026-10-23', 'heart_rate' => 110]);
    logEntry(Activity::Breakfast);

    $response = $this->get('/')->assertSee('Vitals over the last 7 days');

    $points = json_decode(Str::between($response->getContent(), '<script type="application/json" id="vitals-data">', '</script>'), true);

    expect($points)->toHaveCount(2)
        ->and($points[0])->toMatchArray(['label' => '10/17 8:00 AM', 'heart_rate' => 62, 'systolic' => null, 'flags' => []])
        ->and($points[1]['flags'])->toBe(['heart_rate' => 'High']);

    $response->assertSee('<canvas id="chart-hr"', false)->assertDontSee('<canvas id="chart-bp"', false);
});

it('can chart the last 30 days instead', function () {
    logEntry(Activity::Vitals, ['entry_date' => '2026-09-23', 'spo2' => 96]);
    logEntry(Activity::Vitals, ['entry_date' => '2026-09-24', 'spo2' => 97]);

    $response = $this->get('/?days=30')->assertSee('Vitals over the last 30 days');

    $points = json_decode(Str::between($response->getContent(), '<script type="application/json" id="vitals-data">', '</script>'), true);
    expect(array_column($points, 'label'))->toBe(['09/24 8:00 AM']);
});

it('falls back to 7 days for any other choice', function () {
    $this->get('/?days=9999')->assertOk()->assertSee('Vitals over the last 7 days');
});

it('lists the readings in a table with unusual ones marked', function () {
    logEntry(Activity::Vitals, ['systolic' => 158, 'diastolic' => 80, 'temperature' => 37.0]);

    $this->get('/')
        ->assertSee('Show readings as a table')
        ->assertSee('158 (High)')
        ->assertSee('37.0°C')
        ->assertDontSee('80 (');
});

it('shows the 8 most recent entries', function () {
    foreach (range(1, 9) as $hour) {
        logEntry(Activity::Other, ['entry_time' => sprintf('%02d:00:00', $hour), 'description' => "Note at hour {$hour}."]);
    }

    $this->get('/')
        ->assertSeeInOrder(['Recent entries', 'Note at hour 9.', 'Note at hour 2.'])
        ->assertDontSee('Note at hour 1.');
});
