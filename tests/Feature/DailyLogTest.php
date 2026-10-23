<?php

use App\Models\CareEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows only the chosen day, sorted by time', function () {
    CareEntry::factory()->create(['entry_date' => '2026-10-20', 'entry_time' => '18:00:00', 'description' => 'Evening soup']);
    CareEntry::factory()->create(['entry_date' => '2026-10-20', 'entry_time' => '07:00:00', 'description' => 'Morning porridge']);
    CareEntry::factory()->create(['entry_date' => '2026-10-21', 'description' => 'Something the next day']);

    $this->get(route('log', ['date' => '2026-10-20']))
        ->assertOk()
        ->assertSee('10/20/2026')
        ->assertSee('7:00 AM')
        ->assertSeeInOrder(['Morning porridge', 'Evening soup'])
        ->assertDontSee('Something the next day');
});

it('links to the previous and next days', function () {
    $this->get(route('log', ['date' => '2026-03-01']))
        ->assertSee(route('log', ['date' => '2026-02-28']), false)
        ->assertSee(route('log', ['date' => '2026-03-02']), false);
});

it('shows today when the date is missing or invalid', function (?string $date) {
    $this->get(route('log', array_filter(['date' => $date])))
        ->assertOk()
        ->assertSee(today()->format('m/d/Y'))
        ->assertSee('Today');
})->with([null, 'nonsense', '2026-02-30', '10/22/2026']);

it('says so when nothing is logged that day', function () {
    $this->get(route('log', ['date' => '2026-10-20']))
        ->assertSee('Nothing logged for this day yet.');
});

it('pre-fills the add form with the viewed day and the current time', function () {
    $this->travelTo(now()->setTime(14, 5));

    $this->get(route('log', ['date' => '2026-10-20']))
        ->assertSee('value="2026-10-20"', false)
        ->assertSee('value="14:05"', false);
});
