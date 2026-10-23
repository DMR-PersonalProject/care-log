<?php

use App\Support\Vitals;

it('flags readings outside the usual range, but not at the edges', function (string $field, int|float|string $value, ?string $flag) {
    expect(Vitals::flag($field, $value))->toBe($flag);
})->with([
    ['systolic', 89, 'Low'], ['systolic', 90, null], ['systolic', 140, null], ['systolic', 141, 'High'],
    ['diastolic', 59, 'Low'], ['diastolic', 91, 'High'],
    ['heart_rate', 59, 'Low'], ['heart_rate', 100, null], ['heart_rate', 101, 'High'],
    ['spo2', 94, 'Low'], ['spo2', 95, null], ['spo2', 100, null],
    ['temperature', '35.9', 'Low'], ['temperature', '36.0', null], ['temperature', '37.5', null], ['temperature', '37.6', 'High'],
]);

it('does not flag missing readings', function () {
    expect(Vitals::flag('systolic', null))->toBeNull()
        ->and(Vitals::flag('systolic', ''))->toBeNull();
});

it('describes the usual ranges', function () {
    expect(Vitals::range('systolic'))->toBe('90–140')
        ->and(Vitals::range('temperature'))->toBe('36.0–37.5');
});
