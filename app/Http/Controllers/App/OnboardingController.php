<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Quran\Reciter;
use App\Models\Quran\TranslationSource;
use App\Models\System\Location;
use App\Services\Worship\PrayerTimeCalculator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    public function show(Request $request)
    {
        if ($request->user()->onboarded_at) {
            return redirect()->route('app.home');
        }

        return view('app.onboarding', [
            'locations' => Location::query()->orderBy('country')->orderBy('name')->get(['id', 'name', 'country', 'latitude', 'longitude', 'timezone']),
            'methods' => collect(PrayerTimeCalculator::METHODS)->map(fn ($m) => $m[0])->all(),
            'translations' => TranslationSource::query()->where('is_active', true)->orderBy('sort')->get(),
            'pref' => $request->user()->prayerPreference,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'location_name' => ['required', 'string', 'max:120'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'timezone' => ['required', 'timezone:all'],
            'method' => ['required', Rule::in(array_keys(PrayerTimeCalculator::METHODS))],
            'asr_method' => ['required', Rule::in(array_keys(PrayerTimeCalculator::ASR_METHODS))],
            'translation_source_id' => ['nullable', 'exists:translation_sources,id'],
            'theme' => ['required', Rule::in(['light', 'dark', 'system'])],
        ]);

        $user = $request->user();
        $user->prayerPreference()->updateOrCreate([], collect($data)->only(['latitude', 'longitude', 'timezone', 'method', 'asr_method'])->merge(['location_name' => $data['location_name']])->all());
        $user->preferences()->updateOrCreate([], [
            'theme' => $data['theme'], 'timezone' => $data['timezone'],
            'translation_source_id' => $data['translation_source_id'] ?? TranslationSource::query()->orderBy('sort')->value('id'),
            'reciter_id' => $user->preferences?->reciter_id ?? Reciter::query()->orderBy('sort')->value('id'),
        ]);
        $user->forceFill(['onboarded_at' => now()])->save();

        return redirect()->route('app.home')->with('status', 'Welcome to SIRAJ. Your preferences are saved.');
    }

    public function skip(Request $request)
    {
        $request->user()->forceFill(['onboarded_at' => now()])->save();

        return redirect()->route('app.home');
    }
}
