<?php

use App\Enums\Activity;
use App\Enums\Status;
use App\Models\CareEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function entryWith(array $attributes): CareEntry
{
    return CareEntry::factory()->create([
        'activity' => Activity::Other,
        'status' => Status::Done,
        'description' => null,
        ...$attributes,
    ]);
}

it('links to all entries from the menu', function () {
    $this->get(route('log'))->assertSee(route('entries.index'), false)->assertSee('All Entries');
});

it('lists entries newest first, with their dates', function () {
    entryWith(['entry_date' => '2026-10-19', 'entry_time' => '09:00:00', 'description' => 'Oldest']);
    entryWith(['entry_date' => '2026-10-20', 'entry_time' => '08:00:00', 'description' => 'Middle']);
    entryWith(['entry_date' => '2026-10-20', 'entry_time' => '19:00:00', 'description' => 'Newest']);

    $this->get(route('entries.index'))
        ->assertOk()
        ->assertSee('10/19/2026')
        ->assertSee(route('log', ['date' => '2026-10-20']), false)
        ->assertSeeInOrder(['Newest', 'Middle', 'Oldest'])
        ->assertSee('Showing 1–3 of 3 entries.');
});

it('shows 15 entries per page', function () {
    foreach (range(1, 17) as $day) {
        entryWith(['entry_date' => sprintf('2026-09-%02d', $day), 'description' => "Day number {$day}."]);
    }

    $this->get(route('entries.index'))
        ->assertSee('Day number 17.')
        ->assertSee('Day number 3.')
        ->assertDontSee('Day number 2.')
        ->assertSee('Page 1 of 2')
        ->assertSee('Showing 1–15 of 17 entries.');

    $this->get(route('entries.index', ['page' => 2]))
        ->assertSee('Day number 2.')
        ->assertSee('Day number 1.')
        ->assertDontSee('Day number 3.')
        ->assertSee('Page 2 of 2');
});

it('goes to the last page when the page asked for is past the end', function () {
    entryWith([]);

    $this->get(route('entries.index', ['page' => 5]))->assertRedirect(route('entries.index', ['page' => 1]));
});

it('finds entries by words in the description, ignoring case', function () {
    entryWith(['description' => 'Ate all her PORRIDGE today.']);
    entryWith(['description' => 'Soup for lunch.']);

    $this->get(route('entries.index', ['q' => 'porridge']))
        ->assertSee('Showing 1–1 of 1 entry matching')
        ->assertSee('her <mark class="rounded bg-yellow-200 px-0.5 text-inherit">PORRIDGE</mark> today.', false)
        ->assertDontSee('Soup for lunch.');
});

it('finds entries by the activity name shown on screen', function () {
    entryWith(['activity' => Activity::Pedal, 'description' => 'Ten minutes.']);
    entryWith(['activity' => Activity::Bath, 'description' => 'Sponge bath.']);

    $this->get(route('entries.index', ['q' => 'exercise']))
        ->assertSee('Ten minutes.')
        ->assertDontSee('Sponge bath.')
        ->assertSee('Pedal (<mark class="rounded bg-yellow-200 px-0.5 text-inherit">exercise</mark>)', false);
});

it('finds entries by the status name shown on screen', function () {
    entryWith(['status' => Status::NeedsAttention, 'description' => 'Restless at bedtime.']);
    entryWith(['status' => Status::Done, 'description' => 'Slept well.']);

    $this->get(route('entries.index', ['q' => 'needs attention']))
        ->assertSee('Restless at bedtime.')
        ->assertDontSee('Slept well.');
});

it('only finds entries that match every word', function () {
    entryWith(['activity' => Activity::Lunch, 'status' => Status::Refused, 'description' => 'Did not want to eat.']);
    entryWith(['activity' => Activity::Lunch, 'status' => Status::Done, 'description' => 'Ate everything.']);
    entryWith(['activity' => Activity::Dinner, 'status' => Status::Refused, 'description' => 'Pushed the plate away.']);

    $this->get(route('entries.index', ['q' => '  Refused   lunch ']))
        ->assertSee('Did not want to eat.')
        ->assertDontSee('Ate everything.')
        ->assertDontSee('Pushed the plate away.')
        ->assertSee('value="Refused   lunch"', false);
});

it('treats % and _ as ordinary characters', function () {
    entryWith(['description' => 'Ate 50% of dinner.']);
    entryWith(['description' => 'Ate 500 grams.']);

    $this->get(route('entries.index', ['q' => '50%']))
        ->assertSee('Ate <mark class="rounded bg-yellow-200 px-0.5 text-inherit">50%</mark> of dinner.', false)
        ->assertDontSee('Ate 500 grams.');
});

it('never shows notes as raw HTML, even when highlighting', function () {
    entryWith(['description' => 'Said <b>hello</b> & smiled.']);

    $this->get(route('entries.index', ['q' => 'hello']))
        ->assertSee('Said &lt;b&gt;<mark class="rounded bg-yellow-200 px-0.5 text-inherit">hello</mark>&lt;/b&gt; &amp; smiled.', false)
        ->assertDontSee('<b>hello', false);
});

it('keeps the search words in the page links', function () {
    foreach (range(1, 16) as $i) {
        entryWith(['description' => "Medicine given, round {$i}."]);
    }

    $this->get(route('entries.index', ['q' => 'round']))
        ->assertSee(e(route('entries.index', ['q' => 'round', 'page' => 2])), false);
});

it('says so when nothing matches', function () {
    entryWith(['description' => 'Soup.']);

    $this->get(route('entries.index', ['q' => 'xylophone']))
        ->assertOk()
        ->assertSee('No entries match')
        ->assertDontSee('Soup.');
});

it('ignores a search that is not plain text', function () {
    entryWith(['description' => 'Soup.']);

    $this->get('/entries?q[]=x')->assertOk()->assertSee('Soup.');
});

it('comes back to the same search after deleting from it', function () {
    $entry = entryWith(['entry_date' => '2026-10-20']);

    $this->delete(route('entries.destroy', $entry), ['return_to' => '/entries?q=soup&page=2'])
        ->assertRedirect('/entries?q=soup&page=2')
        ->assertSessionHas('status', 'Entry deleted.');

    expect(CareEntry::count())->toBe(0);
});

it('goes to the entry day instead of leaving the app after deleting', function (string $returnTo) {
    $entry = entryWith(['entry_date' => '2026-10-20']);

    $this->delete(route('entries.destroy', $entry), ['return_to' => $returnTo])
        ->assertRedirect(route('log', ['date' => '2026-10-20']));
})->with(['https://example.com/', '//example.com/', '/\\example.com/', 'entries']);
