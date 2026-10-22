<?php

namespace App\Enums;

enum Status: string
{
    case Done = 'done';
    case Partial = 'partial';
    case Refused = 'refused';
    case Missed = 'missed';
    case NeedsAttention = 'needs_attention';

    public function label(): string
    {
        return match ($this) {
            self::NeedsAttention => 'Needs Attention',
            default => $this->name,
        };
    }

    /** @return array<string, string> value => label, for dropdowns */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}
