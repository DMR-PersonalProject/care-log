<?php

namespace App\Support;

class Vitals
{
    /**
     * Usual adult ranges. Readings outside them are flagged High or Low on the dashboard.
     * These are general guides only; change them to what her doctor recommends.
     */
    public const NORMAL = [
        'systolic' => [90, 140],
        'diastolic' => [60, 90],
        'heart_rate' => [60, 100],
        'spo2' => [95, 100],
        'temperature' => [36.0, 37.5],
    ];

    /** "High", "Low", or null when the reading is usual (or missing). */
    public static function flag(string $field, int|float|string|null $value): ?string
    {
        if ($value === null || $value === '' || ! isset(self::NORMAL[$field])) {
            return null;
        }

        [$low, $high] = self::NORMAL[$field];

        return match (true) {
            (float) $value < $low => 'Low',
            (float) $value > $high => 'High',
            default => null,
        };
    }

    /** e.g. "90–140" or "36.0–37.5" */
    public static function range(string $field): string
    {
        [$low, $high] = self::NORMAL[$field];
        $format = fn (int|float $n) => is_float($n) ? number_format($n, 1) : (string) $n;

        return $format($low).'–'.$format($high);
    }
}
