<?php

use App\Enums\Activity;
use App\Enums\Status;

it('gives every activity and status a label', function () {
    foreach ([...Activity::cases(), ...Status::cases()] as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty();
    }
});

it('uses friendly labels where the case name is not enough', function () {
    expect(Activity::Pedal->label())->toBe('Pedal (exercise)')
        ->and(Status::NeedsAttention->label())->toBe('Needs Attention');
});

it('lists the four meal types', function () {
    expect(Activity::meals())->toBe([Activity::Breakfast, Activity::Lunch, Activity::Dinner, Activity::Snack]);
});

it('builds dropdown options keyed by stored value', function () {
    expect(Activity::options())->toHaveCount(13)->toHaveKey('pedal', 'Pedal (exercise)')
        ->and(Status::options())->toHaveKey('needs_attention', 'Needs Attention');
});
