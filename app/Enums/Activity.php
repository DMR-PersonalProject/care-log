<?php

namespace App\Enums;

/**
 * The single list of activity types. To add one (e.g. Bowel Movement),
 * add a case here and its label below.
 */
enum Activity: string
{
    case Breakfast = 'breakfast';
    case Lunch = 'lunch';
    case Dinner = 'dinner';
    case Snack = 'snack';
    case Medicine = 'medicine';
    case Bath = 'bath';
    case Urination = 'urination';
    case Pedal = 'pedal';
    case Dentures = 'dentures';
    case Sleep = 'sleep';
    case Story = 'story';
    case Vitals = 'vitals';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Pedal => 'Pedal (exercise)',
            default => $this->name,
        };
    }

    /** @return list<self> */
    public static function meals(): array
    {
        return [self::Breakfast, self::Lunch, self::Dinner, self::Snack];
    }

    /** @return array<string, string> value => label, for dropdowns */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $a) => [$a->value => $a->label()])->all();
    }
}
