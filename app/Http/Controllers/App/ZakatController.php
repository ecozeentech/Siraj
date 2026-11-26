<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Worship\ZakatCalculation;
use App\Services\Worship\ZakatCalculator;
use Illuminate\Http\Request;

class ZakatController extends Controller
{
    public function index(Request $request)
    {
        return view('app.zakat', [
            'saved' => ZakatCalculation::query()->where('user_id', $request->user()->id)->latest()->limit(10)->get(),
            'assets' => ZakatCalculator::ASSETS, 'liabilities' => ZakatCalculator::LIABILITIES,
        ]);
    }

    public function store(Request $request, ZakatCalculator $calc)
    {
        $rules = ['name' => ['nullable', 'string', 'max:80'], 'nisab_basis' => ['required', 'in:gold,silver'], 'gold_price_per_gram' => ['required', 'numeric', 'min:0', 'max:100000000'], 'silver_price_per_gram' => ['required', 'numeric', 'min:0', 'max:100000000'], 'hawl_completed' => ['boolean'], 'gold_grams' => ['nullable', 'numeric', 'min:0', 'max:1000000'], 'silver_grams' => ['nullable', 'numeric', 'min:0', 'max:100000000']];
        foreach (array_merge(array_keys(ZakatCalculator::ASSETS), array_keys(ZakatCalculator::LIABILITIES)) as $k) {
            $rules[$k] = ['nullable', 'numeric', 'min:0', 'max:1000000000000'];
        }
        $d = $request->validate($rules);
        $inputs = collect($d)->only(array_merge(array_keys(ZakatCalculator::ASSETS), array_keys(ZakatCalculator::LIABILITIES), ['gold_grams', 'silver_grams']))->map(fn ($v) => (float) ($v ?? 0))->all();
        $r = $calc->calculate($inputs, $d['nisab_basis'], (float) $d['gold_price_per_gram'], (float) $d['silver_price_per_gram'], (bool) ($d['hawl_completed'] ?? true));

        ZakatCalculation::query()->create([
            'user_id' => $request->user()->id, 'name' => $d['name'] ?? null, 'currency' => config('siraj.currency'), 'nisab_basis' => $d['nisab_basis'],
            'gold_price_per_gram' => $d['gold_price_per_gram'], 'silver_price_per_gram' => $d['silver_price_per_gram'], 'hawl_completed' => (bool) ($d['hawl_completed'] ?? true),
            'inputs' => $inputs, 'total_assets' => $r['total_assets'], 'total_liabilities' => $r['total_liabilities'], 'net_wealth' => $r['net_wealth'], 'nisab_value' => $r['nisab_value'], 'zakat_due' => $r['zakat_due'],
        ]);

        return back()->with('status', 'Calculation saved.');
    }

    public function destroy(Request $request, ZakatCalculation $calculation)
    {
        abort_unless($calculation->user_id === $request->user()->id, 404);
        $calculation->delete();

        return back()->with('status', 'Calculation deleted.');
    }
}
