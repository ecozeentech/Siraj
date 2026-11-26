<?php

namespace App\Services\Worship;

use Carbon\CarbonImmutable;
use DateTimeZone;

/**
 * Astronomical prayer time calculator.
 *
 * Implements the widely used solar-position approach (declination, equation of time, hour angles)
 * with selectable calculation methods, Asr juristic factor and high-latitude adjustments.
 * Output is rounded to the nearest minute, so results are accurate to roughly ±1–2 minutes of
 * published timetables that use the same method. Local mosques may apply their own offsets: users
 * can add per-prayer minute adjustments in their prayer preferences.
 */
class PrayerTimeCalculator
{
    public const PRAYERS = ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'];

    /** key => [label, fajr angle, isha angle or "NNmin" interval after maghrib] */
    public const METHODS = [
        'mwl' => ['Muslim World League', 18, 17],
        'isna' => ['Islamic Society of North America (ISNA)', 15, 15],
        'egypt' => ['Egyptian General Authority of Survey', 19.5, 17.5],
        'makkah' => ['Umm al-Qura University, Makkah', 18.5, '90min'],
        'karachi' => ['University of Islamic Sciences, Karachi', 18, 18],
        'kuwait' => ['Kuwait', 18, 17.5],
        'qatar' => ['Qatar', 18, '90min'],
        'singapore' => ['Singapore (MUIS)', 20, 18],
        'dubai' => ['United Arab Emirates', 18.2, 18.2],
        'turkey' => ['Turkey (Diyanet)', 18, 17],
    ];

    public const HIGH_LATITUDE_RULES = ['none' => 'None', 'night_middle' => 'Middle of the night', 'one_seventh' => 'One-seventh of the night', 'angle_based' => 'Angle-based'];

    public const ASR_METHODS = ['standard' => "Standard (Shafi'i, Maliki, Hanbali)", 'hanafi' => 'Hanafi'];

    /**
     * @return array<string, CarbonImmutable> prayer => time in the given timezone
     */
    public function calculate(
        CarbonImmutable $date,
        float $latitude,
        float $longitude,
        string $timezone = 'UTC',
        string $method = 'mwl',
        string $asr = 'standard',
        string $highLatitude = 'angle_based',
        array $adjustments = [],
    ): array {
        [, $fajrAngle, $ishaParam] = self::METHODS[$method] ?? self::METHODS['mwl'];
        $tz = new DateTimeZone($timezone);
        $day = $date->setTimezone($tz)->startOfDay();
        $offsetHours = $tz->getOffset($day->setTime(12, 0)->toDateTime()) / 3600;

        $jd = $this->julianDate((int) $day->format('Y'), (int) $day->format('n'), (int) $day->format('j')) - $longitude / (15 * 24);

        $dhuhr = $this->midDay($jd + 0.5) + $offsetHours - $longitude / 15;
        $sunriseAngle = 0.833;
        $sunrise = $dhuhr - $this->hourAngleTime($sunriseAngle, $jd + 0.25, $latitude);
        $sunset = $dhuhr + $this->hourAngleTime($sunriseAngle, $jd + 0.75, $latitude);
        $fajr = $dhuhr - $this->hourAngleTime($fajrAngle, $jd + 0.25, $latitude);
        $asrTime = $dhuhr + $this->asrTime($asr === 'hanafi' ? 2 : 1, $jd + 0.6, $latitude);
        $maghrib = $sunset;
        $isha = is_string($ishaParam)
            ? $maghrib + ((int) $ishaParam) / 60
            : $dhuhr + $this->hourAngleTime((float) $ishaParam, $jd + 0.75, $latitude);

        $isIntervalIsha = is_string($ishaParam);
        $times = $this->adjustHighLatitudes(compact('fajr', 'sunrise', 'dhuhr', 'asrTime', 'maghrib', 'isha'), $highLatitude, (float) $fajrAngle, $isIntervalIsha ? null : (float) $ishaParam);
        $times['asr'] = $times['asrTime'];
        unset($times['asrTime']);

        $out = [];
        foreach (self::PRAYERS as $prayer) {
            $hours = $times[$prayer] + (($adjustments[$prayer] ?? 0) / 60);
            if (is_nan($hours)) {
                continue;
            }
            $minutes = (int) round($hours * 60);
            $out[$prayer] = $day->addMinutes($minutes);
        }

        return $out;
    }

    private function adjustHighLatitudes(array $t, string $rule, float $fajrAngle, ?float $ishaAngle): array
    {
        if ($rule === 'none') {
            return $t;
        }
        $night = $this->timeDiff($t['maghrib'], $t['sunrise']);
        if (is_nan($night)) {
            return $t;
        }
        $portion = fn (float $angle) => match ($rule) {
            'night_middle' => 1 / 2,
            'one_seventh' => 1 / 7,
            default => $angle / 60,
        };

        $fajrDiff = $portion($fajrAngle) * $night;
        if (is_nan($t['fajr']) || $this->timeDiff($t['fajr'], $t['sunrise']) > $fajrDiff) {
            $t['fajr'] = $t['sunrise'] - $fajrDiff;
        }
        if ($ishaAngle !== null) {
            $ishaDiff = $portion($ishaAngle) * $night;
            if (is_nan($t['isha']) || $this->timeDiff($t['maghrib'], $t['isha']) > $ishaDiff) {
                $t['isha'] = $t['maghrib'] + $ishaDiff;
            }
        }

        return $t;
    }

    private function timeDiff(float $a, float $b): float
    {
        return $this->fixHour($b - $a);
    }

    private function hourAngleTime(float $angle, float $jd, float $lat): float
    {
        $decl = $this->sunPosition($jd)['declination'];
        $cos = (-sin(deg2rad($angle)) - sin(deg2rad($decl)) * sin(deg2rad($lat))) / (cos(deg2rad($decl)) * cos(deg2rad($lat)));
        if ($cos < -1 || $cos > 1) {
            return NAN;
        }

        return rad2deg(acos($cos)) / 15;
    }

    private function asrTime(int $factor, float $jd, float $lat): float
    {
        $decl = $this->sunPosition($jd)['declination'];
        $angle = -rad2deg(atan(1 / ($factor + tan(deg2rad(abs($lat - $decl))))));

        return $this->hourAngleTime($angle, $jd, $lat);
    }

    private function midDay(float $jd): float
    {
        return $this->fixHour(12 - $this->sunPosition($jd)['equation']);
    }

    /** @return array{declination: float, equation: float} */
    private function sunPosition(float $jd): array
    {
        $d = $jd - 2451545.0;
        $g = $this->fixAngle(357.529 + 0.98560028 * $d);
        $q = $this->fixAngle(280.459 + 0.98564736 * $d);
        $l = $this->fixAngle($q + 1.915 * sin(deg2rad($g)) + 0.020 * sin(deg2rad(2 * $g)));
        $e = 23.439 - 0.00000036 * $d;
        $ra = rad2deg(atan2(cos(deg2rad($e)) * sin(deg2rad($l)), cos(deg2rad($l)))) / 15;

        return [
            'declination' => rad2deg(asin(sin(deg2rad($e)) * sin(deg2rad($l)))),
            'equation' => $q / 15 - $this->fixHour($ra),
        ];
    }

    private function julianDate(int $year, int $month, int $day): float
    {
        if ($month <= 2) {
            $year--;
            $month += 12;
        }
        $a = floor($year / 100);
        $b = 2 - $a + floor($a / 4);

        return floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $day + $b - 1524.5;
    }

    private function fixAngle(float $a): float
    {
        return $a - 360 * floor($a / 360);
    }

    private function fixHour(float $a): float
    {
        return $a - 24 * floor($a / 24);
    }
}
