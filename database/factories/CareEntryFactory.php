<?php

namespace Database\Factories;

use App\Enums\Activity;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\CareEntry>
 */
class CareEntryFactory extends Factory
{
    public function definition(): array
    {
        $activity = fake()->randomElement(Activity::cases());

        // Vitals rows always carry readings; other rows sometimes do.
        $withVitals = $activity === Activity::Vitals || fake()->boolean(30);

        return [
            'entry_date' => fake()->dateTimeBetween('-7 days', 'now')->format('Y-m-d'),
            'entry_time' => sprintf('%02d:%02d:00', fake()->numberBetween(6, 21), fake()->randomElement([0, 15, 30, 45])),
            'activity' => $activity,
            'status' => fake()->randomElement([Status::Done, Status::Done, Status::Done, Status::Partial, Status::Refused]),
            'description' => fake()->optional(0.6)->sentence(),
            ...($withVitals ? self::vitals() : []),
        ];
    }

    public function withVitals(): static
    {
        return $this->state(fn () => self::vitals());
    }

    /** @return array<string, int|float> */
    public static function vitals(): array
    {
        return [
            'systolic' => fake()->numberBetween(105, 150),
            'diastolic' => fake()->numberBetween(65, 95),
            'heart_rate' => fake()->numberBetween(60, 95),
            'spo2' => fake()->numberBetween(93, 99),
            'temperature' => fake()->randomFloat(1, 36.1, 37.6),
        ];
    }
}
