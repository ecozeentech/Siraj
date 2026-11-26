<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Finance\SubscriptionPlan;
use App\Models\System\ContactMessage;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        return view('public.home');
    }

    public function about()
    {
        return view('public.about');
    }

    public function features()
    {
        return view('public.features');
    }

    public function pricing()
    {
        return view('public.pricing', ['plans' => SubscriptionPlan::query()->where('is_active', true)->orderBy('sort')->get()]);
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function submitContact(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
            'website' => ['nullable', 'max:0'], // honeypot
        ]);
        unset($data['website']);
        ContactMessage::query()->create($data + ['ip_address' => $request->ip()]);

        return back()->with('status', 'Thank you — your message has been received.');
    }

    public function privacy()
    {
        return view('public.privacy');
    }

    public function terms()
    {
        return view('public.terms');
    }

    public function language(Request $request)
    {
        $request->validate(['locale' => ['required', 'in:'.implode(',', array_keys(config('siraj.supported_locales')))]]);
        $request->session()->put('locale', $request->input('locale'));

        return back();
    }
}
