<?php

namespace App\Http\Controllers;

use App\Http\Requests\CareEntryRequest;
use App\Models\CareEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CareEntryController extends Controller
{
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

    public function destroy(CareEntry $entry): RedirectResponse
    {
        $entry->delete();

        return $this->backToDay($entry, 'Entry deleted.');
    }

    private function backToDay(CareEntry $entry, string $message): RedirectResponse
    {
        return to_route('log', ['date' => $entry->entry_date->toDateString()])->with('status', $message);
    }
}
