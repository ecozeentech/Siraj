<?php

namespace App\Services\Worship;

use Carbon\CarbonInterface;
use IntlDateFormatter;

class Hijri
{
    /** Formats a date in the Umm al-Qura Hijri calendar, e.g. "1 Muharram 1447". $offsetDays adjusts for local moon sighting. */
    public static function format(CarbonInterface $date, int $offsetDays = 0): string
    {
        $fmt = new IntlDateFormatter('en_US@calendar=islamic-umalqura', IntlDateFormatter::NONE, IntlDateFormatter::NONE, $date->getTimezone()->getName(), IntlDateFormatter::TRADITIONAL, 'd MMMM y');

        return (string) $fmt->format($date->copy()->addDays($offsetDays)->timestamp);
    }
}
