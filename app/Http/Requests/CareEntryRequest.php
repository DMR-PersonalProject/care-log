<?php

namespace App\Http\Requests;

use App\Enums\Activity;
use App\Enums\Status;
use App\Models\CareEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Used for both adding and editing an entry.
 */
class CareEntryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'entry_date' => ['required', 'date_format:Y-m-d'],
            'entry_time' => ['required', 'date_format:H:i,H:i:s'],
            'activity' => ['required', Rule::enum(Activity::class)],
            'status' => ['required', Rule::enum(Status::class)],
            'description' => ['nullable', 'string', 'max:2000'],
            'systolic' => ['nullable', 'integer', 'between:50,250'],
            'diastolic' => ['nullable', 'integer', 'between:30,150'],
            'heart_rate' => ['nullable', 'integer', 'between:20,250'],
            'spo2' => ['nullable', 'integer', 'between:50,100'],
            'temperature' => ['nullable', 'numeric', 'between:30,45', 'decimal:0,1'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $noReadings = collect(CareEntry::VITAL_FIELDS)->every(fn (string $field) => blank($this->input($field)));

                if ($this->input('activity') === Activity::Vitals->value && $noReadings) {
                    $validator->errors()->add('vitals', 'Please enter at least one reading for a vitals check.');
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'entry_date' => 'date',
            'entry_time' => 'time',
            'systolic' => 'top blood pressure number',
            'diastolic' => 'bottom blood pressure number',
            'heart_rate' => 'heart rate',
            'spo2' => 'oxygen level',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Please fill in the :attribute.',
            'activity.required' => 'Please choose an activity.',
            'activity.enum' => 'Please choose an activity from the list.',
            'status.enum' => 'Please choose a status from the list.',
            'entry_date.date_format' => 'Please choose a valid date.',
            'entry_time.date_format' => 'Please enter a valid time, like 7:30 AM.',
            'integer' => 'The :attribute must be a whole number.',
            'numeric' => 'The :attribute must be a number.',
            'between' => ':Attribute must be between :min and :max.',
            'temperature.decimal' => 'Temperature can have one decimal place at most, like 37.5.',
            'description.max' => 'The notes are too long (2000 characters at most).',
        ];
    }
}
