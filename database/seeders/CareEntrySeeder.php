<?php

namespace Database\Seeders;

use App\Enums\Activity;
use App\Enums\Status;
use App\Models\CareEntry;
use Database\Factories\CareEntryFactory;
use Illuminate\Database\Seeder;

/**
 * A believable week of the daily routine, so every screen has something to show.
 */
class CareEntrySeeder extends Seeder
{
    private const ROUTINE = [
        ['06:30', Activity::Urination, 'Helped to the bathroom after waking up.'],
        ['07:00', Activity::Vitals, 'Morning check.'],
        ['07:15', Activity::Dentures, 'Dentures cleaned and put in.'],
        ['07:30', Activity::Breakfast, 'Rice porridge and a banana.'],
        ['08:00', Activity::Medicine, 'Morning pills with water.'],
        ['10:00', Activity::Pedal, '10 minutes on the pedal exerciser.'],
        ['10:45', Activity::Urination, null],
        ['12:00', Activity::Lunch, 'Fish, rice and vegetables.'],
        ['13:30', Activity::Sleep, 'Afternoon nap.'],
        ['15:30', Activity::Snack, 'Biscuits and juice.'],
        ['16:00', Activity::Bath, 'Sponge bath.'],
        ['18:00', Activity::Dinner, 'Chicken soup and rice.'],
        ['18:30', Activity::Medicine, 'Evening pills.'],
        ['20:00', Activity::Dentures, 'Dentures removed and soaked.'],
        ['20:30', Activity::Sleep, 'Went to bed.'],
    ];

    public function run(): void
    {
        foreach (range(6, 0) as $daysAgo) {
            $date = today()->subDays($daysAgo)->toDateString();

            foreach (self::ROUTINE as [$time, $activity, $description]) {
                // Today only gets the entries that have already happened.
                if ($daysAgo === 0 && $time > now()->format('H:i')) {
                    continue;
                }

                // Bath every other day, pedal most days.
                if (($activity === Activity::Bath && $daysAgo % 2 === 1)
                    || ($activity === Activity::Pedal && $daysAgo === 3)) {
                    continue;
                }

                CareEntry::create([
                    'entry_date' => $date,
                    'entry_time' => $time.':00',
                    'activity' => $activity,
                    'status' => Status::Done,
                    'description' => $description,
                    ...($activity === Activity::Vitals ? CareEntryFactory::vitals() : []),
                ]);
            }
        }

        $this->addVariety();
    }

    private function addVariety(): void
    {
        $day = fn (int $daysAgo) => today()->subDays($daysAgo)->toDateString();

        CareEntry::where('entry_date', $day(2))->where('activity', Activity::Lunch)
            ->update(['status' => Status::Refused, 'description' => 'Did not want to eat. Offered soup later.']);

        CareEntry::where('entry_date', $day(4))->where('activity', Activity::Dinner)
            ->update(['status' => Status::Partial, 'description' => 'Ate about half.']);

        CareEntry::create([
            'entry_date' => $day(1), 'entry_time' => '14:30:00',
            'activity' => Activity::Story, 'status' => Status::Done,
            'description' => 'Talked about growing up in the province and her sisters. She was smiling the whole time.',
        ]);

        CareEntry::create([
            'entry_date' => $day(3), 'entry_time' => '21:15:00',
            'activity' => Activity::Other, 'status' => Status::NeedsAttention,
            'description' => 'Restless and confused at bedtime. Felt a little warm.',
            'systolic' => 158, 'diastolic' => 96, 'heart_rate' => 102, 'spo2' => 94, 'temperature' => 37.9,
        ]);
    }
}
