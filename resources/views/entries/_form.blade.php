{{-- Shared by the quick-add form on the daily log and the edit page.
     Needs $entry, $action, $submitLabel; optional $method (default POST) and $cancelUrl. --}}
@use('App\Enums\Activity')
@use('App\Enums\Status')
@use('App\Models\CareEntry')
@php
    $method ??= 'POST';
    $activity = old('activity', $entry->activity?->value);
    $openVitals = $errors->hasAny([...CareEntry::VITAL_FIELDS, 'vitals'])
        || $activity === Activity::Vitals->value
        || collect(CareEntry::VITAL_FIELDS)->contains(fn ($field) => filled(old($field, $entry->{$field})));

    $vitalInputs = [
        'systolic' => ['Blood pressure, top number', 'mmHg', '1'],
        'diastolic' => ['Blood pressure, bottom number', 'mmHg', '1'],
        'heart_rate' => ['Heart rate', 'beats per minute', '1'],
        'spo2' => ['Oxygen level (SpO₂)', '%', '1'],
        'temperature' => ['Temperature', '°C', '0.1'],
    ];
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5"
      x-data="{ activity: @js($activity), showVitals: @js($openVitals) }"
      x-effect="if (activity === 'vitals') showVitals = true">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label for="entry_date" class="field-label">Date</label>
            <input type="date" id="entry_date" name="entry_date" required class="field-input"
                   value="{{ old('entry_date', $entry->entry_date?->toDateString()) }}">
            <x-field-error name="entry_date" />
        </div>

        <div>
            <label for="entry_time" class="field-label">Time</label>
            <input type="time" id="entry_time" name="entry_time" required class="field-input"
                   value="{{ old('entry_time', $entry->entry_time ? substr($entry->entry_time, 0, 5) : '') }}">
            <x-field-error name="entry_time" />
        </div>

        <div>
            <label for="activity" class="field-label">Activity</label>
            <select id="activity" name="activity" required class="field-input" x-model="activity">
                <option value="" disabled @selected(! $activity)>Choose…</option>
                @foreach (Activity::options() as $value => $label)
                    <option value="{{ $value }}" @selected($activity === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-field-error name="activity" />
        </div>

        <div>
            <label for="status" class="field-label">Status</label>
            <select id="status" name="status" required class="field-input">
                @foreach (Status::options() as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $entry->status?->value) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-field-error name="status" />
        </div>
    </div>

    <div>
        <label for="description" class="field-label">Description <span class="font-normal text-stone-600">(optional)</span></label>
        <textarea id="description" name="description" rows="3" class="field-input"
                  placeholder="What happened? Anything worth remembering?">{{ old('description', $entry->description) }}</textarea>
        <x-field-error name="description" />
    </div>

    <div class="rounded-lg border border-stone-200 bg-stone-50">
        <button type="button" class="flex min-h-11 w-full cursor-pointer items-center justify-between px-4 py-2 text-left text-lg font-semibold"
                @click="showVitals = ! showVitals" :aria-expanded="showVitals">
            <span>Vitals <span class="font-normal text-stone-600">(optional)</span></span>
            <span aria-hidden="true" x-text="showVitals ? '▲' : '▼'">▼</span>
        </button>

        <div x-show="showVitals" x-cloak class="border-t border-stone-200 px-4 pt-3 pb-4">
            <x-field-error name="vitals" />
            <div class="mt-2 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($vitalInputs as $field => [$label, $unit, $step])
                    <div>
                        <label for="{{ $field }}" class="field-label text-base">{{ $label }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number" id="{{ $field }}" name="{{ $field }}" step="{{ $step }}"
                                   inputmode="{{ $step === '1' ? 'numeric' : 'decimal' }}" class="field-input min-w-20"
                                   value="{{ old($field, $entry->{$field}) }}">
                            <span class="text-base text-stone-600">{{ $unit }}</span>
                        </div>
                        <x-field-error :name="$field" />
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        @isset($cancelUrl)
            <a href="{{ $cancelUrl }}" class="btn btn-secondary">Cancel</a>
        @endisset
    </div>
</form>
