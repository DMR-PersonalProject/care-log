<?php

namespace App\Http\Controllers;

use App\Http\Requests\CareEntryRequest;
use App\Models\CareEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareEntryController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $search = is_string($request->query('q')) ? mb_substr(trim($request->query('q')), 0, 100) : '';

        $entries = CareEntry::query()
            ->when($search !== '', fn ($query) => $query->search($search))
            ->newestFirst()
            ->paginate(15)
            ->withQueryString();

        // e.g. after deleting the only entry on the last page.
        if ($entries->isEmpty() && $entries->currentPage() > 1) {
            return redirect($entries->url($entries->lastPage()));
        }

        return view('entries.index', [
            'entries' => $entries,
            'search' => $search,
            'words' => CareEntry::searchWords($search),
        ]);
    }

    public function store(CareEntryRequest $request): RedirectResponse
    {
        $entry = CareEntry::create($request->validated());

        return $this->backToDay($entry, 'Entry added.');
    }

    public function show(CareEntry $entry): View
    {
        return view('entries.show', ['entry' => $entry]);
    }

    public function edit(CareEntry $entry): View
    {
        return view('entries.edit', ['entry' => $entry]);
    }

    public function update(CareEntryRequest $request, CareEntry $entry): RedirectResponse
    {
        $entry->update($request->validated());

        return $this->backToDay($entry, 'Changes saved.');
    }

    public function destroy(Request $request, CareEntry $entry): RedirectResponse
    {
        $entry->delete();

        $returnTo = $request->input('return_to');

        // Only a path inside this app ("/entries?q=..."), never "//other-site" or "/\other-site".
        if (is_string($returnTo) && preg_match('#^/(?![/\\\\])#', $returnTo)) {
            return redirect($returnTo)->with('status', 'Entry deleted.');
        }

        return $this->backToDay($entry, 'Entry deleted.');
    }

    private function backToDay(CareEntry $entry, string $message): RedirectResponse
    {
        return to_route('log', ['date' => $entry->entry_date->toDateString()])->with('status', $message);
    }
}
