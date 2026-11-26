<?php

namespace App\Services\Worship;

class QiblaCalculator
{
    public const KAABA_LAT = 21.422487;

    public const KAABA_LNG = 39.826206;

    /** Initial great-circle bearing (degrees clockwise from true north) from the given point to the Kaaba. */
    public static function bearing(float $lat, float $lng): float
    {
        $phi1 = deg2rad($lat);
        $phi2 = deg2rad(self::KAABA_LAT);
        $dLng = deg2rad(self::KAABA_LNG - $lng);
        $y = sin($dLng);
        $x = cos($phi1) * tan($phi2) - sin($phi1) * cos($dLng);

        return fmod(rad2deg(atan2($y, $x)) + 360, 360);
    }

    public static function distanceKm(float $lat, float $lng): float
    {
        $r = 6371.0088;
        $dPhi = deg2rad(self::KAABA_LAT - $lat);
        $dLam = deg2rad(self::KAABA_LNG - $lng);
        $a = sin($dPhi / 2) ** 2 + cos(deg2rad($lat)) * cos(deg2rad(self::KAABA_LAT)) * sin($dLam / 2) ** 2;

        return 2 * $r * asin(min(1, sqrt($a)));
    }
}
