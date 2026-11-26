<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Quran\Ayah;
use App\Models\Quran\Bookmark;
use App\Models\Quran\Juz;
use App\Models\Quran\MemorisationProgress;
use App\Models\Quran\ReadingGoal;
use App\Models\Quran\ReadingHistory;
use App\Models\Quran\Reciter;
use App\Models\Quran\Surah;
use App\Models\Quran\TafsirSource;
use App\Models\Quran\TranslationSource;
use App\Services\Quran\ReaderData;
use App\Services\Quran\ReadingStats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuranController extends Controller
{
    public function index(Request $request, ReadingStats $stats)
    {
        $user = $request->user();
        $last = ReadingHistory::query()->where('user_id', $user->id)->latest('read_at')->with('ayah')->first();

        return view('app.quran.index', [
            'surahs' => Surah::query()->orderBy('id')->get(),
            'juz' => Juz::query()->with(['firstAyah.surah', 'lastAyah.surah'])->orderBy('id')->get(),
            'last' => $last ? ['surah' => Surah::find($last->surah_id), 'ayah' => $last->ayah->number_in_surah] : null,
            'streak' => $stats->streak($user), 'goal' => $stats->goalProgress($user),
            'tab' => in_array($request->query('tab'), ['juz'], true) ? $request->query('tab') : 'surah',
        ]);
    }

    public function surah(Request $request, Surah $surah, ReaderData $data)
    {
        $ayahs = Ayah::query()->where('surah_id', $surah->id)->orderBy('number_in_surah')->get();

        return $this->reader($request, $data, $ayahs, [
            'title' => $surah->name_en, 'title_ar' => $surah->name_ar, 'subtitle' => $surah->meaning_en.' · '.ucfirst($surah->revelation).' · '.$surah->ayah_count.' ayahs',
            'surah_id' => $surah->id, 'kind' => 'surah', 'id' => $surah->id,
        ], (int) $request->query('ayah', 1));
    }

    public function juz(Request $request, Juz $juz, ReaderData $data)
    {
        $ayahs = Ayah::query()->whereBetween('id', [$juz->first_ayah_id, $juz->last_ayah_id])->orderBy('id')->get();

        return $this->reader($request, $data, $ayahs, [
            'title' => 'Juz '.$juz->id, 'title_ar' => 'الجزء '.$juz->id, 'subtitle' => $ayahs->count().' ayahs', 'surah_id' => null, 'kind' => 'juz', 'id' => $juz->id,
        ], 1);
    }

    private function reader(Request $request, ReaderData $data, $ayahs, array $meta, int $startN)
    {
        $user = $request->user();
        $prefs = $user->preferences;
        $ids = $ayahs->pluck('id');
        $trId = $prefs->show_translation ? $prefs->translation_source_id : null;
        $tfId = $prefs->show_tafsir ? $prefs->tafsir_source_id : null;
        $reciter = $prefs->reciter ?? Reciter::query()->where('is_active', true)->orderBy('sort')->first();

        $start = $meta['kind'] === 'surah' ? max(1, min($startN, $ayahs->count())) : 1;
        $surahs = Surah::query()->orderBy('id')->get(['id', 'name_en', 'name_ar', 'ayah_count']);

        return view('app.quran.reader', [
            'config' => [
                'meta' => $meta, 'basmalah' => Ayah::basmalah(),
                'ayahs' => $data->ayahs($ayahs, $trId, $tfId),
                'start' => $start,
                'prefs' => ['font' => (int) $prefs->quran_font_size, 'mode' => $request->query('mode', $prefs->quran_reading_mode), 'showTr' => (bool) $prefs->show_translation, 'showTf' => (bool) $prefs->show_tafsir],
                'sources' => $data->sources($trId, $tfId),
                'bookmarks' => Bookmark::query()->where('user_id', $user->id)->whereIn('ayah_id', $ids)->pluck('ayah_id')->all(),
                'notes' => DB::table('ayah_notes')->where('user_id', $user->id)->whereIn('ayah_id', $ids)->pluck('body', 'ayah_id')->all() ?: new \stdClass,
                'memorised' => MemorisationProgress::query()->where('user_id', $user->id)->whereIn('ayah_id', $ids)->pluck('status', 'ayah_id')->all() ?: new \stdClass,
                'reciter' => $reciter ? ['id' => $reciter->id, 'slug' => $reciter->slug, 'name' => $reciter->name] : null,
                'surahs' => $surahs,
                'urls' => ['prefs' => route('app.quran.prefs'), 'surah' => url('/app/quran/surah/__ID__'), 'audio' => url('/quran-audio/'.($reciter->slug ?? 'none').'/__ID__')],
            ],
            'meta' => $meta,
            'reciters' => Reciter::query()->where('is_active', true)->orderBy('sort')->get(['id', 'slug', 'name']),
            'translations' => TranslationSource::query()->where('is_active', true)->orderBy('sort')->get(['id', 'name']),
            'tafsirs' => TafsirSource::query()->where('is_active', true)->orderBy('sort')->get(['id', 'name']),
            'prefs' => $prefs,
        ]);
    }

    /** Payload used by the offline downloader. */
    public function data(Request $request, Surah $surah, ReaderData $data)
    {
        $prefs = $request->user()->preferences;
        $trId = $request->query('t', $prefs->translation_source_id);
        $tfId = $request->query('s', $prefs->tafsir_source_id);
        $ayahs = Ayah::query()->where('surah_id', $surah->id)->orderBy('number_in_surah')->get();

        return response()->json([
            'surah' => $surah->only(['id', 'name_ar', 'name_en', 'meaning_en', 'revelation', 'ayah_count']),
            'ayahs' => $data->ayahs($ayahs, $trId ? (int) $trId : null, $tfId ? (int) $tfId : null),
            'sources' => $data->sources($trId ? (int) $trId : null, $tfId ? (int) $tfId : null),
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $results = collect();
        $note = null;

        if ($q !== '') {
            if ($ayah = Ayah::findByReference($q)) {
                return redirect()->route('app.quran.surah', ['surah' => $ayah->surah_id, 'ayah' => $ayah->number_in_surah]);
            }
            $surahMatches = Surah::query()->where(fn ($w) => $w->where('name_en', 'like', "%{$q}%")->orWhere('meaning_en', 'like', "%{$q}%")->orWhere('name_ar', 'like', "%{$q}%"))->limit(10)->get();
            $trId = $request->user()->preferences->translation_source_id;
            if ($trId && mb_strlen($q) >= 3) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $results = DB::table('translations')->join('ayahs', 'ayahs.id', '=', 'translations.ayah_id')->join('surahs', 'surahs.id', '=', 'ayahs.surah_id')
                    ->where('translations.translation_source_id', $trId)->where('translations.text', 'like', $like)
                    ->orderBy('ayahs.id')->limit(40)->get(['ayahs.id', 'ayahs.surah_id', 'ayahs.number_in_surah', 'surahs.name_en', 'translations.text']);
            } elseif (mb_strlen($q) < 3) {
                $note = 'Type at least 3 letters to search the translation, or a reference like 2:255.';
            }
        } else {
            $surahMatches = collect();
        }

        return view('app.quran.search', ['q' => $q, 'surahMatches' => $surahMatches, 'results' => $results, 'note' => $note]);
    }

    public function bookmarks(Request $request)
    {
        $user = $request->user();
        $trId = $user->preferences->translation_source_id;
        $items = Bookmark::query()->where('user_id', $user->id)->latest()->with(['ayah.surah'])->paginate(20);
        $tr = DB::table('translations')->where('translation_source_id', $trId)->whereIn('ayah_id', $items->pluck('ayah_id'))->pluck('text', 'ayah_id');
        $notes = DB::table('ayah_notes')->where('user_id', $user->id)->whereIn('ayah_id', $items->pluck('ayah_id'))->pluck('body', 'ayah_id');
        $allNotes = DB::table('ayah_notes')->join('ayahs', 'ayahs.id', '=', 'ayah_notes.ayah_id')->join('surahs', 'surahs.id', '=', 'ayahs.surah_id')
            ->where('ayah_notes.user_id', $user->id)->latest('ayah_notes.updated_at')->limit(30)->get(['ayah_notes.id', 'ayah_notes.body', 'ayahs.surah_id', 'ayahs.number_in_surah', 'surahs.name_en']);

        return view('app.quran.bookmarks', ['items' => $items, 'tr' => $tr, 'notes' => $notes, 'allNotes' => $allNotes]);
    }

    public function destroyNote(Request $request, int $note)
    {
        DB::table('ayah_notes')->where('id', $note)->where('user_id', $request->user()->id)->delete();

        return back()->with('status', 'Note deleted.');
    }

    public function goals(Request $request, ReadingStats $stats)
    {
        return view('app.quran.goals', ['progress' => $stats->goalProgress($request->user()), 'streak' => $stats->streak($request->user())]);
    }

    public function saveGoal(Request $request)
    {
        $d = $request->validate(['daily_target' => ['required', 'integer', 'between:1,500']]);
        ReadingGoal::query()->where('user_id', $request->user()->id)->update(['is_active' => false]);
        ReadingGoal::query()->create(['user_id' => $request->user()->id, 'type' => 'ayahs', 'daily_target' => $d['daily_target'], 'starts_on' => today(), 'is_active' => true]);

        return back()->with('status', 'Daily goal set.');
    }

    public function clearGoal(Request $request)
    {
        ReadingGoal::query()->where('user_id', $request->user()->id)->update(['is_active' => false]);

        return back()->with('status', 'Goal removed.');
    }

    public function memorisation(Request $request)
    {
        $rows = MemorisationProgress::query()->where('user_id', $request->user()->id)->get();
        $bySurah = $rows->groupBy('surah_id');
        $surahs = Surah::query()->whereIn('id', $bySurah->keys())->orderBy('id')->get()->map(function ($s) use ($bySurah) {
            $g = $bySurah[$s->id];

            return ['surah' => $s, 'memorised' => $g->where('status', 'memorised')->count(), 'learning' => $g->where('status', 'learning')->count(), 'revising' => $g->where('status', 'revising')->count()];
        });

        return view('app.quran.memorisation', ['surahs' => $surahs, 'total' => $rows->where('status', 'memorised')->count()]);
    }

    public function prefs(Request $request)
    {
        $d = $request->validate([
            'quran_font_size' => ['sometimes', 'integer', 'between:22,56'], 'quran_reading_mode' => ['sometimes', Rule::in(['ayah', 'continuous', 'page'])],
            'show_translation' => ['sometimes', 'boolean'], 'show_tafsir' => ['sometimes', 'boolean'],
            'translation_source_id' => ['sometimes', 'nullable', 'exists:translation_sources,id'], 'tafsir_source_id' => ['sometimes', 'nullable', 'exists:tafsir_sources,id'],
            'reciter_id' => ['sometimes', 'nullable', 'exists:reciters,id'],
        ]);
        $request->user()->preferences()->updateOrCreate([], $d);

        return response()->json(['ok' => true]);
    }

    public function offline(Request $request)
    {
        $prefs = $request->user()->preferences;

        return view('app.quran.offline', [
            'surahs' => Surah::query()->orderBy('id')->get(['id', 'name_en', 'name_ar', 'ayah_count']),
            'translations' => TranslationSource::query()->where('is_active', true)->orderBy('sort')->get(['id', 'name']),
            'tafsirs' => TafsirSource::query()->where('is_active', true)->orderBy('sort')->get(['id', 'name']),
            'prefs' => $prefs, 'reciter' => $prefs->reciter ?? Reciter::query()->orderBy('sort')->first(),
        ]);
    }
}
