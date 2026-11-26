<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Directory\Business;
use App\Models\Directory\BusinessCategory;
use App\Models\Directory\BusinessReview;
use App\Models\Directory\DirectoryReport;
use App\Models\Directory\Mosque;
use App\Services\Community\CommunityService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DirectoryController extends Controller
{
    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['businesses', 'halal'], true) ? $request->query('tab') : 'mosques';
        $q = trim((string) $request->query('q'));
        $city = trim((string) $request->query('city'));
        $lat = is_numeric($request->query('lat')) ? (float) $request->query('lat') : null;
        $lng = is_numeric($request->query('lng')) ? (float) $request->query('lng') : null;
        $like = fn (string $v) => '%'.str_replace(['%', '_'], ['\%', '\_'], $v).'%';

        $query = $tab === 'mosques'
            ? Mosque::query()->where('status', 'published')
            : Business::query()->where('status', 'published')->with('category')->when($tab === 'halal', fn ($w) => $w->where('halal_status', 'verified')->where(fn ($x) => $x->whereNull('halal_certificate_expires_at')->orWhere('halal_certificate_expires_at', '>=', today())))
                ->when($request->query('category'), fn ($w, $c) => $w->whereHas('category', fn ($x) => $x->where('slug', $c)));
        $query->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', $like($q))->orWhere('address', 'like', $like($q))))
            ->when($city !== '', fn ($w) => $w->where('city', 'like', $like($city)));

        if ($lat !== null && $lng !== null) {
            $all = $query->limit(500)->get()->map(function ($m) use ($lat, $lng) {
                $m->distance_km = $m->latitude && $m->longitude ? \App\Support\Geo::distanceKm($lat, $lng, $m->latitude, $m->longitude) : null;

                return $m;
            })->sortBy(fn ($m) => $m->distance_km ?? PHP_INT_MAX)->values();
            $page = LengthAwarePaginator::resolveCurrentPage();
            $items = new LengthAwarePaginator($all->forPage($page, 12)->values(), $all->count(), 12, $page, ['path' => $request->url(), 'query' => $request->query()]);
        } else {
            $items = $query->orderBy('name')->paginate(12)->withQueryString();
        }

        return view('app.directory.index', [
            'tab' => $tab, 'items' => $items, 'q' => $q, 'city' => $city, 'lat' => $lat, 'lng' => $lng, 'categories' => BusinessCategory::query()->orderBy('sort')->get(), 'category' => $request->query('category'),
            'cities' => Mosque::query()->where('status', 'published')->distinct()->orderBy('city')->pluck('city'),
        ]);
    }

    public function mosque(Mosque $mosque)
    {
        abort_unless($mosque->status === 'published', 404);

        return view('app.directory.mosque', ['mosque' => $mosque]);
    }

    public function business(Request $request, Business $business)
    {
        abort_unless($business->status === 'published', 404);
        $hidden = app(CommunityService::class)->hiddenAuthorIds($request->user());
        $reviews = $business->reviews()->where('status', 'published')->whereNotIn('user_id', $hidden)->with('user')->latest('id')->paginate(10);

        return view('app.directory.business', [
            'business' => $business->load('category'), 'reviews' => $reviews,
            'mine' => $business->reviews()->where('user_id', $request->user()->id)->first(),
            'halalValid' => $business->halal_status === 'verified' && (! $business->halal_certificate_expires_at || $business->halal_certificate_expires_at->gte(today())),
        ]);
    }

    public function review(Request $request, Business $business)
    {
        abort_unless($business->status === 'published', 404);
        abort_if(app(CommunityService::class)->isRestricted($request->user(), 'posting'), 403, 'Your posting privileges are restricted.');
        abort_if($business->submitted_by === $request->user()->id, 422, 'You cannot review a business you submitted.');
        $data = $request->validate(['rating' => ['required', 'integer', 'between:1,5'], 'body' => ['nullable', 'string', 'max:1000']]);
        DB::transaction(function () use ($business, $request, $data) {
            BusinessReview::query()->updateOrCreate(['business_id' => $business->id, 'user_id' => $request->user()->id], ['rating' => $data['rating'], 'body' => isset($data['body']) ? trim(strip_tags($data['body'])) : null, 'status' => 'published']);
            $agg = $business->reviews()->where('status', 'published')->selectRaw('COUNT(*) c, AVG(rating) a')->first();
            $business->update(['reviews_count' => (int) $agg->c, 'rating_average' => round((float) $agg->a, 2)]);
        });

        return back()->with('status', 'Thanks for your review.');
    }

    public function report(Request $request, string $type, int $id)
    {
        $subject = $type === 'mosque' ? Mosque::query()->findOrFail($id) : Business::query()->findOrFail($id);
        $data = $request->validate(['type' => ['required', Rule::in(['incorrect_info', 'closed', 'not_halal', 'correction', 'other'])], 'details' => ['required', 'string', 'min:5', 'max:1500']]);
        DirectoryReport::query()->firstOrCreate(
            ['reporter_id' => $request->user()->id, 'reportable_type' => $subject->getMorphClass(), 'reportable_id' => $subject->id, 'status' => 'open', 'type' => $data['type']],
            ['details' => $data['details']],
        );

        return back()->with('status', 'Thank you. Our team will review your report.');
    }

    public function create()
    {
        return view('app.directory.submit', ['categories' => BusinessCategory::query()->orderBy('sort')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'business_category_id' => ['required', 'exists:business_categories,id'], 'description' => ['nullable', 'string', 'max:2000'],
            'address' => ['nullable', 'string', 'max:255'], 'city' => ['required', 'string', 'max:120'], 'state' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'], 'email' => ['nullable', 'email', 'max:255'], 'website' => ['nullable', 'url', 'max:255'],
            'halal_certifier' => ['nullable', 'string', 'max:160'], 'halal_certificate_number' => ['nullable', 'string', 'max:80'],
        ]);
        abort_if(Business::query()->where('submitted_by', $request->user()->id)->where('status', 'pending')->count() >= 5, 429, 'You have several submissions awaiting review. Please wait for them to be reviewed.');
        $slug = Str::slug($data['name']);
        while (Business::query()->where('slug', $slug)->exists()) {
            $slug = Str::slug($data['name']).'-'.Str::lower(Str::random(4));
        }
        // A claimed certificate never makes a business "verified"; only an admin can do that after checking it.
        Business::query()->create($data + ['slug' => $slug, 'submitted_by' => $request->user()->id, 'status' => 'pending', 'halal_status' => filled($data['halal_certificate_number'] ?? null) ? 'pending' : 'unverified']);

        return redirect()->route('app.directory.index', ['tab' => 'businesses'])->with('status', 'Thanks! Your listing was submitted and will appear after review.');
    }
}
