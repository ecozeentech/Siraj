<?php

namespace App\Services\Finance;

use App\Models\Events\Event;
use App\Models\Events\EventRegistration;
use App\Models\Finance\PaymentTransaction;
use App\Models\Finance\Wallet;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class EventTicketService
{
    public function __construct(private LedgerService $ledger, private PaymentService $payments) {}

    /**
     * @param  'wallet'|'gateway'|null  $method  null for free events
     * @return array{registration: EventRegistration, redirect: ?string}
     */
    public function register(User $user, Event $event, ?string $method): array
    {
        if ($event->status !== 'published') {
            throw new InvalidArgumentException('This event is not open for registration.');
        }
        $ends = $event->ends_at ?? $event->starts_at->copy()->endOfDay();
        if ($ends->isPast()) {
            throw new InvalidArgumentException('This event has already taken place.');
        }

        $registration = DB::transaction(function () use ($user, $event, $method) {
            Event::query()->whereKey($event->id)->lockForUpdate()->first();
            $existing = EventRegistration::query()->where(['event_id' => $event->id, 'user_id' => $user->id])->first();
            if ($existing && in_array($existing->status, ['registered', 'attended', 'waitlisted'], true)) {
                throw new InvalidArgumentException('You are already registered for this event.');
            }
            $taken = $event->registrations()->whereIn('status', ['registered', 'attended'])->count()
                + $event->registrations()->where('status', 'pending_payment')->where('updated_at', '>', now()->subMinutes(30))->where('user_id', '!=', $user->id)->count();
            $full = $event->capacity !== null && $taken >= $event->capacity;
            if ($full && ! $event->isFree()) {
                throw new InvalidArgumentException('This event is sold out.');
            }

            $status = $event->isFree() ? ($full ? 'waitlisted' : 'registered') : 'pending_payment';
            if ($existing) {
                $existing->update(['status' => $status]);

                return $existing;
            }

            return EventRegistration::query()->create(['event_id' => $event->id, 'user_id' => $user->id, 'status' => $status, 'ticket_code' => 'TKT-'.Str::upper(Str::random(8))]);
        });

        if ($event->isFree()) {
            $user->notify(new GeneralNotification($registration->status === 'waitlisted' ? 'You are on the waitlist' : 'Registration confirmed', $event->title, route('app.events.show', $event, false), 'events', 'calendar-check'));

            return ['registration' => $registration, 'redirect' => null];
        }

        try {
            if ($method === 'wallet') {
                $this->payWithWallet($user, $event);
                $registration->update(['status' => 'registered']);
                $user->notify(new GeneralNotification('Ticket confirmed', $event->title.' · '.$registration->ticket_code, route('app.events.show', $event, false), 'events', 'ticket'));

                return ['registration' => $registration, 'redirect' => null];
            }
            $tx = $this->payments->initiate($user, 'event_ticket', (int) $event->price_kobo, $event, ['event' => $event->slug]);

            return ['registration' => $registration, 'redirect' => $tx->authorization_url];
        } catch (\Throwable $e) {
            $registration->update(['status' => 'cancelled']);
            throw $e;
        }
    }

    private function payWithWallet(User $user, Event $event): void
    {
        DB::transaction(function () use ($user, $event) {
            $wallet = Wallet::query()->where('user_id', $user->id)->firstOrFail();
            $tx = PaymentTransaction::query()->create([
                'user_id' => $user->id, 'reference' => 'SIRAJ-W'.Str::upper(Str::random(14)), 'gateway' => 'wallet', 'mode' => 'live',
                'purpose' => 'event_ticket', 'payable_type' => $event->getMorphClass(), 'payable_id' => $event->id,
                'amount_kobo' => $event->price_kobo, 'currency' => $event->currency, 'status' => 'succeeded', 'paid_at' => now(),
            ]);
            $this->ledger->post([
                ['account' => $this->ledger->ensureUserAccount($wallet), 'direction' => 'debit', 'amount' => (int) $event->price_kobo],
                ['account' => 'system:event_revenue', 'direction' => 'credit', 'amount' => (int) $event->price_kobo],
            ], 'Ticket: '.$event->title, 'pay:'.$tx->reference, $tx, $event->currency);
        });
    }

    public function cancelFree(User $user, Event $event): void
    {
        $reg = EventRegistration::query()->where(['event_id' => $event->id, 'user_id' => $user->id])->whereIn('status', ['registered', 'waitlisted', 'pending_payment'])->firstOrFail();
        if (! $event->isFree() && $reg->status === 'registered') {
            throw new InvalidArgumentException('Paid tickets are cancelled by requesting a refund from your wallet receipts.');
        }
        $reg->update(['status' => 'cancelled']);
        if ($event->isFree() && $event->capacity !== null) {
            $next = $event->registrations()->where('status', 'waitlisted')->oldest('id')->first();
            if ($next && $event->registrations()->where('status', 'registered')->count() < $event->capacity) {
                $next->update(['status' => 'registered']);
                $next->user->notify(new GeneralNotification('You got a spot!', 'A place opened up for '.$event->title, route('app.events.show', $event, false), 'events', 'calendar-check'));
            }
        }
    }
}
