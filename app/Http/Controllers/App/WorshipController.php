<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\System\Location;
use App\Models\Worship\AdhkarCategory;
use App\Models\Worship\AdhkarEntry;
use App\Models\Worship\AdhkarFavorite;
use App\Models\Worship\AdhkarProgress;
use App\Models\Worship\DhikrSession;
use App\Models\Worship\PrayerLog;
use App\Models\Worship\TajweedResource;
use App\Services\Worship\Hijri;
use App\Services\Worship\PrayerTimeCalculator;
use App\Services\Worship\PrayerTimeService;
use App\Services\Worship\QiblaCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorshipController extends Controller
{
    public const PRAYERS = ['fajr' => 'Fajr', 'dhuhr' => 'Dhuhr', 'asr' => 'Asr', 'maghrib' => 'Maghrib', 'isha' => 'Isha'];

    public function __construct(private PrayerTimeService $prayers) {}

    public function index(Request $request)
    {
        $pref = $this->prayers->preferenceFor($request->user());
        $now = CarbonImmutable::now($pref->timezone);

        return view('app.worship.index', ['pref' => $pref, 'next' => $this->prayers->nextPrayer($pref, $now), 'hijri' => Hijri::format($now, (int) $pref->hijri_offset), 'names' => self::PRAYERS]);
    }

    public function prayerTimes(Request $request)
    {
        $pref = $this->prayers->preferenceFor($request->user());
        $now = CarbonImmutable::now($pref->timezone);
        $day = $now;
        if ($request->filled('date') && preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->query('date'))) {
            try {
                $day = CarbonImmutable::createFromFormat('Y-m-d', $request->query('date'), $pref->timezone)->startOfDay();
            } catch (\Throwable) {
            }
        }
        $isToday = $day->isSameDay($now);

        return view('app.worship.prayer-times', [
            'pref' => $pref, 'day' => $day, 'isToday' => $isToday, 'times' => $this->prayers->timesFor($pref, $day), 'now' => $now,
            'next' => $isToday ? $this->prayers->nextPrayer($pref, $now) : null, 'current' => $isToday ? $this->prayers->currentPrayer($pref, $now) : null,
            'hijri' => Hijri::format($day, (int) $pref->hijri_offset), 'method' => PrayerTimeCalculator::METHODS[$pref->method][0] ?? $pref->method,
        ]);
    }

    public function timetable(Request $request)
    {
        $pref = $this->prayers->preferenceFor($request->user());
        $month = CarbonImmutable::now($pref->timezone)->startOfMonth();
        if (preg_match('/^\d{4}-\d{2}$/', (string) $request->query('month'))) {
            $month = CarbonImmutable::createFromFormat('Y-m-d', $request->query('month').'-01', $pref->timezone)->startOfMonth();
        }
        $rows = [];
        for ($d = $month; $d->month === $month->month; $d = $d->addDay()) {
            $rows[] = ['date' => $d, 'times' => $this->prayers->timesFor($pref, $d)];
        }

        return view('app.worship.timetable', ['pref' => $pref, 'month' => $month, 'rows' => $rows, 'today' => CarbonImmutable::now($pref->timezone)->toDateString()]);
    }

    public function settings(Request $request)
    {
        return view('app.worship.settings', [
            'pref' => $this->prayers->preferenceFor($request->user()),
            'locations' => Location::query()->orderBy('country')->orderBy('name')->get(['id', 'name', 'country', 'latitude', 'longitude', 'timezone']),
            'methods' => collect(PrayerTimeCalculator::METHODS)->map(fn ($m) => $m[0])->all(),
            'prayers' => ['fajr' => 'Fajr', 'sunrise' => 'Sunrise', 'dhuhr' => 'Dhuhr', 'asr' => 'Asr', 'maghrib' => 'Maghrib', 'isha' => 'Isha'],
        ]);
    }

    public function saveSettings(Request $request)
    {
        $d = $request->validate([
            'location_name' => ['required', 'string', 'max:120'], 'latitude' => ['required', 'numeric', 'between:-90,90'], 'longitude' => ['required', 'numeric', 'between:-180,180'],
            'timezone' => ['required', 'timezone:all'], 'method' => ['required', Rule::in(array_keys(PrayerTimeCalculator::METHODS))],
            'asr_method' => ['required', Rule::in(array_keys(PrayerTimeCalculator::ASR_METHODS))], 'high_latitude_rule' => ['required', Rule::in(array_keys(PrayerTimeCalculator::HIGH_LATITUDE_RULES))],
            'adjustments' => ['nullable', 'array'], 'adjustments.*' => ['nullable', 'integer', 'between:-30,30'], 'hijri_offset' => ['required', 'integer', 'between:-2,2'],
            'reminders_enabled' => ['boolean'], 'remind_minutes_before' => ['required', 'integer', 'between:0,60'],
        ]);
        $d['adjustments'] = collect($d['adjustments'] ?? [])->map(fn ($v) => (int) $v)->filter()->all();
        $request->user()->prayerPreference()->updateOrCreate([], $d);

        return redirect()->route('app.worship.prayer-times')->with('status', 'Prayer settings saved.');
    }

    public function qibla(Request $request)
    {
        $user = $request->user();
        $pref = $this->prayers->preferenceFor($user);
        $q = $user->qiblaPreference ?? null;
        $lat = (float) $pref->latitude;
        $lng = (float) $pref->longitude;
        $custom = false;
        if ($q && ! $q->use_prayer_location && $q->latitude !== null) {
            $lat = (float) $q->latitude;
            $lng = (float) $q->longitude;
            $custom = true;
        }

        return view('app.worship.qibla', [
            'bearing' => round(QiblaCalculator::bearing($lat, $lng), 1), 'distance' => round(QiblaCalculator::distanceKm($lat, $lng)),
            'location' => $custom ? 'Custom location' : $pref->location_name, 'calibration' => (int) ($q->calibration_offset ?? 0),
        ]);
    }

    public function saveQibla(Request $request)
    {
        $d = $request->validate(['calibration_offset' => ['required', 'integer', 'between:-45,45']]);
        $request->user()->qiblaPreference()->updateOrCreate([], $d);

        return back()->with('status', 'Compass calibration saved.');
    }

    public function tracker(Request $request)
    {
        $user = $request->user();
        $pref = $this->prayers->preferenceFor($user);
        $today = CarbonImmutable::now($pref->timezone)->startOfDay();
        $days = collect(range(0, 6))->map(fn ($i) => $today->subDays($i));
        $logs = PrayerLog::query()->where('user_id', $user->id)->whereBetween('day', [$days->last()->toDateString(), $today->toDateString()])->get();

        $grid = $days->map(fn ($d) => [
            'date' => $d->toDateString(), 'label' => $d->isToday() ? 'Today' : $d->format('D j M'),
            'logs' => $logs->filter(fn ($l) => $l->day->toDateString() === $d->toDateString())->mapWithKeys(fn ($l) => [$l->prayer => ['status' => $l->status, 'congregation' => $l->in_congregation]])->all(),
        ])->values();
        $done = $logs->whereIn('status', ['on_time', 'late', 'qada'])->count();

        return view('app.worship.tracker', ['grid' => $grid, 'prayers' => self::PRAYERS, 'weekDone' => $done, 'onTime' => $logs->where('status', 'on_time')->count()]);
    }

    public function adhkar(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $cats = AdhkarCategory::query()->where('is_published', true)->withCount('entries')
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('title_en', 'like', "%{$q}%")->orWhere('title_ar', 'like', "%{$q}%")))->orderBy('sort')->get();
        $featured = $cats->whereIn('group', ['morning_evening', 'after_prayer', 'sleep'])->values();

        return view('app.worship.adhkar', ['cats' => $cats, 'featured' => $q === '' ? $featured : collect(), 'q' => $q, 'favoritesCount' => AdhkarFavorite::query()->where('user_id', $request->user()->id)->count()]);
    }

    public function adhkarShow(Request $request, AdhkarCategory $category)
    {
        abort_unless($category->is_published, 404);
        $user = $request->user();
        $day = CarbonImmutable::now($this->prayers->preferenceFor($user)->timezone)->toDateString();
        $entries = $category->entries()->orderBy('sort')->get();
        $progress = AdhkarProgress::query()->where('user_id', $user->id)->where('day', $day)->whereIn('adhkar_entry_id', $entries->pluck('id'))->pluck('count', 'adhkar_entry_id');
        $favs = AdhkarFavorite::query()->where('user_id', $user->id)->whereIn('adhkar_entry_id', $entries->pluck('id'))->pluck('adhkar_entry_id')->flip();

        return view('app.worship.adhkar-show', ['category' => $category, 'entries' => $entries, 'progress' => $progress, 'favs' => $favs, 'day' => $day]);
    }

    public function favorites(Request $request)
    {
        $entries = AdhkarEntry::query()->whereIn('id', AdhkarFavorite::query()->where('user_id', $request->user()->id)->pluck('adhkar_entry_id'))->with('category')->get();

        return view('app.worship.adhkar-favorites', ['entries' => $entries]);
    }

    public function toggleFavorite(Request $request, AdhkarEntry $entry)
    {
        $fav = AdhkarFavorite::query()->where('user_id', $request->user()->id)->where('adhkar_entry_id', $entry->id)->first();
        $fav ? $fav->delete() : AdhkarFavorite::query()->create(['user_id' => $request->user()->id, 'adhkar_entry_id' => $entry->id]);

        return response()->json(['favorite' => ! $fav]);
    }

    public function tasbih(Request $request)
    {
        $sessions = DhikrSession::query()->where('user_id', $request->user()->id)->latest()->limit(8)->get();

        return view('app.worship.tasbih', [
            'phrases' => [['text' => 'SubhanAllah', 'target' => 33], ['text' => 'Alhamdulillah', 'target' => 33], ['text' => 'Allahu Akbar', 'target' => 34], ['text' => 'La ilaha illallah', 'target' => 100], ['text' => 'Astaghfirullah', 'target' => 100], ['text' => 'Salawat upon the Prophet', 'target' => 100]],
            'sessions' => $sessions, 'todayTotal' => (int) DhikrSession::query()->where('user_id', $request->user()->id)->where('created_at', '>=', now()->startOfDay())->sum('count'),
        ]);
    }

    public function tajweed()
    {
        return view('app.worship.tajweed', ['resources' => TajweedResource::query()->where('is_published', true)->orderBy('sort')->get()]);
    }

    public function tajweedShow(TajweedResource $resource)
    {
        abort_unless($resource->is_published, 404);

        return view('app.worship.tajweed-show', ['resource' => $resource]);
    }
}
