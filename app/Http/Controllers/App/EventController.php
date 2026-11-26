<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Events\Event;
use App\Models\Events\EventCategory;
use App\Models\Events\EventRegistration;
use App\Services\Finance\EventTicketService;
use App\Services\Finance\GatewayNotConfiguredException;
use App\Services\Finance\InsufficientFundsException;
use App\Services\Finance\PaymentGateway;
use Illuminate\Http\Request;
use InvalidArgumentException;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category');
        $q = trim((string) $request->query('q'));
        $events = Event::query()->published()->upcoming()->with('category')->withCount(['registrations as taken_count' => fn ($w) => $w->whereIn('status', ['registered', 'attended'])])
            ->when($category, fn ($w) => $w->whereHas('category', fn ($c) => $c->where('slug', $category)))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('title', 'like', "%{$q}%")->orWhere('city', 'like', "%{$q}%")))
            ->when($request->boolean('free'), fn ($w) => $w->where('price_kobo', 0))
            ->orderBy('starts_at')->paginate(12)->withQueryString();

        return view('app.events.index', ['events' => $events, 'categories' => EventCategory::query()->orderBy('sort')->get(), 'category' => $category, 'q' => $q, 'mine' => EventRegistration::query()->where('user_id', $request->user()->id)->whereIn('status', ['registered', 'waitlisted', 'pending_payment', 'attended'])->pluck('status', 'event_id')]);
    }

    public function mine(Request $request)
    {
        return view('app.events.mine', ['registrations' => EventRegistration::query()->where('user_id', $request->user()->id)->whereIn('status', ['registered', 'waitlisted', 'pending_payment', 'attended'])->with('event')->latest('id')->get()]);
    }

    public function show(Request $request, Event $event)
    {
        abort_unless($event->status === 'published' || $event->status === 'cancelled', 404);
        $reg = EventRegistration::query()->where(['event_id' => $event->id, 'user_id' => $request->user()->id])->first();

        return view('app.events.show', [
            'event' => $event->load('category'), 'registration' => $reg && $reg->status !== 'cancelled' ? $reg : null,
            'taken' => $event->registrations()->whereIn('status', ['registered', 'attended'])->count(),
            'balance' => (int) ($request->user()->wallet?->balance_kobo ?? 0), 'gatewayReady' => app(PaymentGateway::class)->isConfigured(),
            'past' => ($event->ends_at ?? $event->starts_at->copy()->endOfDay())->isPast(),
        ]);
    }

    public function register(Request $request, Event $event, EventTicketService $tickets)
    {
        $data = $request->validate(['method' => ['nullable', 'in:wallet,gateway']]);
        abort_if($event->status !== 'published', 404);
        try {
            $result = $tickets->register($request->user(), $event, $data['method'] ?? null);
        } catch (InsufficientFundsException) {
            return back()->withErrors(['method' => 'Your wallet balance is not enough. Top up your wallet or pay online.']);
        } catch (GatewayNotConfiguredException $e) {
            return back()->withErrors(['method' => $e->getMessage()]);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['method' => $e->getMessage()]);
        }

        return $result['redirect'] ? redirect()->away($result['redirect']) : redirect()->route('app.events.show', $event)->with('status', $result['registration']->status === 'waitlisted' ? 'You are on the waitlist. We will notify you if a place opens.' : 'You are registered. See you there, in sha Allah.');
    }

    public function cancel(Request $request, Event $event, EventTicketService $tickets)
    {
        try {
            $tickets->cancelFree($request->user(), $event);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['method' => $e->getMessage()]);
        }

        return redirect()->route('app.events.show', $event)->with('status', 'Your registration was cancelled.');
    }
}
