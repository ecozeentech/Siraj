<?php

namespace Database\Seeders;

use App\Models\Academy\CourseCategory;
use App\Models\Directory\BusinessCategory;
use App\Models\Events\EventCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Quran Studies', 'quran-studies', 'book-open'], ['Arabic Language', 'arabic-language', 'languages'],
            ['Seerah & History', 'seerah-history', 'landmark'], ['Fiqh', 'fiqh', 'scale'], ['Family & Character', 'family-character', 'heart-handshake'],
        ] as $i => [$name, $slug, $icon]) {
            CourseCategory::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'icon' => $icon, 'sort' => $i]);
        }
        foreach ([['Lectures', 'lectures'], ['Community', 'community'], ['Education', 'education'], ['Charity', 'charity'], ['Eid & Celebrations', 'eid-celebrations'], ['Youth', 'youth']] as $i => [$name, $slug]) {
            EventCategory::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'sort' => $i]);
        }
        foreach ([
            ['Restaurants', 'restaurants', 'utensils'], ['Groceries & Butchers', 'groceries-butchers', 'shopping-basket'], ['Bakeries & Sweets', 'bakeries', 'croissant'],
            ['Catering', 'catering', 'chef-hat'], ['Modest Fashion', 'modest-fashion', 'shirt'], ['Islamic Finance', 'islamic-finance', 'landmark'], ['Other', 'other', 'store'],
        ] as $i => [$name, $slug, $icon]) {
            BusinessCategory::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'icon' => $icon, 'sort' => $i]);
        }
    }
}
