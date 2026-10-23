<?php

use App\Enums\Activity;
use App\Enums\Status;
use App\Models\CareEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->travelTo('2026-10-23 15:00:00'));

function pdfEntry(string $date, Activity $activity = Activity::Other, array $attributes = []): CareEntry
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

/**
 * PDF text is compressed, so download the PDF and return the HTML that dompdf was given.
 */
function pdfHtml(array $query = []): string
{
    $data = null;
    View::composer('reports.pdf', function ($view) use (&$data) {
        $data ??= $view->getData();
    });

    test()->get(route('reports.pdf', $query))->assertOk();

    return view('reports.pdf', $data)->render();
}

it('downloads a PDF named after the date range', function () {
    pdfEntry('2026-10-20');

    $response = $this->get(route('reports.pdf', ['from' => '2026-10-16']));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload('care-log-report-2026-10-16-to-2026-10-23.pdf');
    expect($response->getContent())->toStartWith('%PDF');
});

it('uses a single date in the file name for a one-day report', function () {
    $this->get(route('reports.pdf', ['from' => '2026-10-20', 'to' => '2026-10-20']))
        ->assertDownload('care-log-report-2026-10-20.pdf');
});

it('is linked from the report page and the print view with the same choices', function () {
    pdfEntry('2026-10-22', Activity::Lunch);
    $link = e(route('reports.pdf', ['from' => '2026-10-01', 'activity' => 'lunch']));

    $this->get(route('reports', ['from' => '2026-10-01', 'activity' => 'lunch', 'page' => 1]))
        ->assertSee('Download PDF')
        ->assertSee($link, false);

    $this->get(route('reports.print', ['from' => '2026-10-01', 'activity' => 'lunch']))
        ->assertSee('Download PDF')
        ->assertSee($link, false);
});

it('has no PDF link when nothing matches', function () {
    $this->get(route('reports'))->assertDontSee('Download PDF');
    $this->get(route('reports.print'))->assertDontSee('Download PDF');
});

it('shows a header with the date range and choices', function () {
    pdfEntry('2026-10-20', Activity::Lunch, ['status' => Status::Refused]);

    $html = pdfHtml(['from' => '2026-10-16', 'activity' => 'lunch', 'status' => 'refused']);

    expect($html)
        ->toContain('Care Log Report')
        ->toContain('10/16/2026 – 10/23/2026')
        ->toContain('Lunch · Refused')
        ->toContain('1 entry')
        ->toContain('Created 10/23/2026 3:00 PM');
});

it('follows the same filters as the report page', function () {
    pdfEntry('2026-10-22', Activity::Lunch, ['status' => Status::Refused, 'description' => 'Refused lunch']);
    pdfEntry('2026-10-22', Activity::Lunch, ['description' => 'Ate lunch']);
    pdfEntry('2026-10-22', Activity::Dinner, ['status' => Status::Refused, 'description' => 'Refused dinner']);
    pdfEntry('2026-10-01', Activity::Lunch, ['status' => Status::Refused, 'description' => 'Too early']);

    expect(pdfHtml(['activity' => 'lunch', 'status' => 'refused']))
        ->toContain('Refused lunch')
        ->not->toContain('Ate lunch')
        ->not->toContain('Refused dinner')
        ->not->toContain('Too early');
});

it('includes every matching entry grouped by day, with the summary', function () {
    foreach (range(1, 20) as $minute) {
        pdfEntry('2026-10-22', Activity::Urination, ['entry_time' => sprintf('06:%02d:00', $minute), 'description' => "Trip {$minute}."]);
    }
    pdfEntry('2026-10-21', Activity::Vitals, ['description' => 'The day before', 'systolic' => 120, 'diastolic' => 80, 'heart_rate' => 72]);

    $html = pdfHtml();

    expect($html)->toContain('21 entries')
        ->and(strpos($html, 'Wednesday, 10/21/2026'))->toBeLessThan(strpos($html, 'The day before'))
        ->and(strpos($html, 'The day before'))->toBeLessThan(strpos($html, 'Thursday, 10/22/2026'))
        ->and(strpos($html, 'Trip 1.'))->toBeLessThan(strpos($html, 'Trip 20.'));
    expect($html)
        ->toContain('Entries per activity')
        ->toContain('120/80 mmHg')
        ->toContain('BP 120/80 · HR 72');
});

it('keeps long descriptions whole in a wrapping cell', function () {
    $long = str_repeat('She talked about her garden and the old house. ', 30).'https://example.com/'.str_repeat('a', 200);
    pdfEntry('2026-10-22', attributes: ['description' => $long]);

    expect(pdfHtml())->toContain('<td class="wrap">'.$long.'</td>');
});

it('still downloads when nothing matches', function () {
    expect(pdfHtml())->toContain('No entries match these choices.');
});

it('sends a bad choice back to the report form with the message', function () {
    $this->get(route('reports.pdf', ['from' => '2026-10-10', 'to' => '2026-10-01']))
        ->assertRedirect(route('reports'))
        ->assertSessionHasErrors(['to' => 'The end date must be the same as or after the start date.']);
});
