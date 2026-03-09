<?php

namespace Database\Seeders;

use App\Models\PublicHoliday;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PublicHolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ([now()->year, now()->year + 1] as $year) {
            foreach ($this->getZimbabwePublicHolidays($year) as $holiday) {
                PublicHoliday::updateOrCreate(
                    ['date' => $holiday['date']],
                    [
                        'name' => $holiday['name'],
                        'date' => $holiday['date'],
                        'is_generated' => true,
                    ]
                );
            }
        }
    }

     private function getZimbabwePublicHolidays(int $year): array
    {
        $easterSunday = Carbon::createMidnightDate($year, 3, 21)->addDays(easter_days($year));

        return [
            ['name' => "New Year's Day", 'date' => Carbon::create($year, 1, 1)->toDateString()],
            ['name' => 'Robert Gabriel Mugabe National Youth Day', 'date' => Carbon::create($year, 2, 21)->toDateString()],
            ['name' => 'Good Friday', 'date' => $easterSunday->copy()->subDays(2)->toDateString()],
            ['name' => 'Holy Saturday', 'date' => $easterSunday->copy()->subDay()->toDateString()],
            ['name' => 'Easter Monday', 'date' => $easterSunday->copy()->addDay()->toDateString()],
            ['name' => 'Independence Day', 'date' => Carbon::create($year, 4, 18)->toDateString()],
            ['name' => "Workers' Day", 'date' => Carbon::create($year, 5, 1)->toDateString()],
            ['name' => 'Africa Day', 'date' => Carbon::create($year, 5, 25)->toDateString()],
            ['name' => "Heroes' Day", 'date' => $this->secondMondayOfAugust($year)->toDateString()],
            ['name' => 'Defence Forces Day', 'date' => $this->secondMondayOfAugust($year)->copy()->addDay()->toDateString()],
            ['name' => 'National Unity Day', 'date' => Carbon::create($year, 12, 22)->toDateString()],
            ['name' => 'Christmas Day', 'date' => Carbon::create($year, 12, 25)->toDateString()],
            ['name' => 'Boxing Day', 'date' => Carbon::create($year, 12, 26)->toDateString()],
        ];
    }

    private function secondMondayOfAugust(int $year): Carbon
    {
        $date = Carbon::create($year, 8, 1);

        while (! $date->isMonday()) {
            $date->addDay();
        }

        return $date->addWeek();
    }
}
