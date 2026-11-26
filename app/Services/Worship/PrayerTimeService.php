<?php

namespace App\Services\Worship;

use App\Models\User;
use App\Models\Worship\PrayerPreference;
use Carbon\CarbonImmutable;

class PrayerTimeService
{
    public function __construct(private PrayerTimeCalculator $calculator) {}

    public function preferenceFor(?User $user): PrayerPreference
    {
        $pref = $user?->prayerPreference;
        if (! $pref) {
            $pref = new PrayerPreference([
                'location_name' => 'Lagos, Nigeria', 'latitude' => 6.5244, 'longitude' => 3.3792, 'timezone' => 'Africa/Lagos',
                'method' => 'mwl', 'asr_method' => 'standard', 'high_latitude_rule' => 'angle_based', 'adjustments' => [],
            ]);
        }

        return $pref;
    }

    public function timesFor(PrayerPreference $pref, ?CarbonImmutable $day = null): array
    {
        $day ??= CarbonImmutable::now($pref->timezone);

        return $this->calculator->calculate(
            $day, (float) $pref->latitude, (float) $pref->longitude, $pref->timezone, $pref->method,
            $pref->asr_method, $pref->high_latitude_rule, $pref->adjustments ?? [],
        );
    }

    /**
     * @return array{name: string, at: CarbonImmutable, is_tomorrow: bool}
     */
    public function nextPrayer(PrayerPreference $pref, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now($pref->timezone);
        $times = $this->timesFor($pref, $now);
        foreach (['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'] as $name) {
            if (isset($times[$name]) && $times[$name]->greaterThan($now)) {
                return ['name' => $name, 'at' => $times[$name], 'is_tomorrow' => false];
            }
        }
        $tomorrow = $this->timesFor($pref, $now->addDay());

        return ['name' => 'fajr', 'at' => $tomorrow['fajr'], 'is_tomorrow' => true];
    }

    public function currentPrayer(PrayerPreference $pref, ?CarbonImmutable $now = null): ?string
    {
        $now ??= CarbonImmutable::now($pref->timezone);
        $times = $this->timesFor($pref, $now);
        $current = null;
        foreach (['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'] as $name) {
            if (isset($times[$name]) && $times[$name]->lessThanOrEqualTo($now)) {
                $current = $name;
            }
        }

        return $current;
    }
}
