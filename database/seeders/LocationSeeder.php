<?php

namespace Database\Seeders;

use App\Models\System\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Lagos', 'Lagos', 'Nigeria', 'NG', 6.5244, 3.3792, 'Africa/Lagos'],
            ['Abuja', 'FCT', 'Nigeria', 'NG', 9.0765, 7.3986, 'Africa/Lagos'],
            ['Kano', 'Kano', 'Nigeria', 'NG', 12.0022, 8.5920, 'Africa/Lagos'],
            ['Ibadan', 'Oyo', 'Nigeria', 'NG', 7.3775, 3.9470, 'Africa/Lagos'],
            ['Kaduna', 'Kaduna', 'Nigeria', 'NG', 10.5105, 7.4165, 'Africa/Lagos'],
            ['Port Harcourt', 'Rivers', 'Nigeria', 'NG', 4.8156, 7.0498, 'Africa/Lagos'],
            ['Ilorin', 'Kwara', 'Nigeria', 'NG', 8.4966, 4.5421, 'Africa/Lagos'],
            ['Maiduguri', 'Borno', 'Nigeria', 'NG', 11.8333, 13.1500, 'Africa/Lagos'],
            ['Sokoto', 'Sokoto', 'Nigeria', 'NG', 13.0059, 5.2476, 'Africa/Lagos'],
            ['Zaria', 'Kaduna', 'Nigeria', 'NG', 11.0855, 7.7199, 'Africa/Lagos'],
            ['Jos', 'Plateau', 'Nigeria', 'NG', 9.8965, 8.8583, 'Africa/Lagos'],
            ['Benin City', 'Edo', 'Nigeria', 'NG', 6.3350, 5.6037, 'Africa/Lagos'],
            ['Enugu', 'Enugu', 'Nigeria', 'NG', 6.4584, 7.5464, 'Africa/Lagos'],
            ['Abeokuta', 'Ogun', 'Nigeria', 'NG', 7.1475, 3.3619, 'Africa/Lagos'],
            ['Osogbo', 'Osun', 'Nigeria', 'NG', 7.7827, 4.5418, 'Africa/Lagos'],
            ['Makkah', null, 'Saudi Arabia', 'SA', 21.3891, 39.8579, 'Asia/Riyadh'],
            ['Madinah', null, 'Saudi Arabia', 'SA', 24.4709, 39.6122, 'Asia/Riyadh'],
            ['Riyadh', null, 'Saudi Arabia', 'SA', 24.7136, 46.6753, 'Asia/Riyadh'],
            ['Cairo', null, 'Egypt', 'EG', 30.0444, 31.2357, 'Africa/Cairo'],
            ['Accra', null, 'Ghana', 'GH', 5.6037, -0.1870, 'Africa/Accra'],
            ['Dakar', null, 'Senegal', 'SN', 14.7167, -17.4677, 'Africa/Dakar'],
            ['Casablanca', null, 'Morocco', 'MA', 33.5731, -7.5898, 'Africa/Casablanca'],
            ['Nairobi', null, 'Kenya', 'KE', -1.2921, 36.8219, 'Africa/Nairobi'],
            ['Johannesburg', null, 'South Africa', 'ZA', -26.2041, 28.0473, 'Africa/Johannesburg'],
            ['London', null, 'United Kingdom', 'GB', 51.5074, -0.1278, 'Europe/London'],
            ['Paris', null, 'France', 'FR', 48.8566, 2.3522, 'Europe/Paris'],
            ['Istanbul', null, 'Türkiye', 'TR', 41.0082, 28.9784, 'Europe/Istanbul'],
            ['Dubai', null, 'United Arab Emirates', 'AE', 25.2048, 55.2708, 'Asia/Dubai'],
            ['Karachi', null, 'Pakistan', 'PK', 24.8607, 67.0011, 'Asia/Karachi'],
            ['Dhaka', null, 'Bangladesh', 'BD', 23.8103, 90.4125, 'Asia/Dhaka'],
            ['Jakarta', null, 'Indonesia', 'ID', -6.2088, 106.8456, 'Asia/Jakarta'],
            ['Kuala Lumpur', null, 'Malaysia', 'MY', 3.1390, 101.6869, 'Asia/Kuala_Lumpur'],
            ['New York', 'NY', 'United States', 'US', 40.7128, -74.0060, 'America/New_York'],
            ['Toronto', 'ON', 'Canada', 'CA', 43.6532, -79.3832, 'America/Toronto'],
        ];
        foreach ($rows as [$name, $region, $country, $cc, $lat, $lng, $tz]) {
            Location::query()->updateOrCreate(['name' => $name, 'country_code' => $cc], [
                'region' => $region, 'country' => $country, 'latitude' => $lat, 'longitude' => $lng, 'timezone' => $tz,
            ]);
        }
    }
}
