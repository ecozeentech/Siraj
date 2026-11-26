<?php

namespace Database\Seeders;

use App\Models\Finance\SubscriptionPlan;
use Illuminate\Database\Seeder;

/** Starter plans. Prices are placeholders to be set by the business in Admin > Subscriptions. */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['free', 'SIRAJ Free', 'Core worship, Quran and community tools.', 0, 'free', false, 0,
                ['Quran with translation & tafsir', 'Prayer times, Qibla & adhkar', 'Community & events', '5 SIRAJ AI questions a day'],
                ['ai_daily_limit' => 5, 'family_children' => 2, 'premium_courses' => false]],
            ['plus-monthly', 'SIRAJ Plus', 'Everything in Free plus premium learning and higher limits.', 200000, 'month', true, 1,
                ['Premium Academy courses', '100 SIRAJ AI questions a day', 'Family profiles for up to 6 children', 'Priority support'],
                ['ai_daily_limit' => 100, 'family_children' => 6, 'premium_courses' => true]],
            ['plus-yearly', 'SIRAJ Plus (Yearly)', 'Save with annual billing.', 2000000, 'year', false, 2,
                ['Everything in SIRAJ Plus', 'Two months free'],
                ['ai_daily_limit' => 100, 'family_children' => 6, 'premium_courses' => true]],
        ];
        foreach ($plans as [$slug, $name, $desc, $price, $interval, $featured, $sort, $features, $ent]) {
            SubscriptionPlan::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name, 'description' => $desc, 'price_kobo' => $price, 'interval' => $interval, 'currency' => 'NGN',
                'is_featured' => $featured, 'sort' => $sort, 'features' => $features, 'entitlements' => $ent, 'is_active' => true,
            ]);
        }
    }
}
