<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subscriber;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/** Bookings from the website. Prices are always recomputed here — never trusted from the browser. */
class BookingController extends Controller
{
    public function __construct(private AvailabilityService $availability) {}

    private function fail(string $message, int $status = 422): JsonResponse
    {
        return response()->json(['ok' => false, 'error' => $message], $status);
    }

    private function ref(string $prefix, string $model): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no 0/O/1/I to avoid confusion on WhatsApp
        do {
            $ref = $prefix . '-' . collect(range(1, 6))->map(fn () => $chars[random_int(0, strlen($chars) - 1)])->join('');
        } while ($model::where('ref', $ref)->exists());
        return $ref;
    }

    /** Tour booking request (no payment). */
    public function storeEnquiry(Request $request): JsonResponse
    {
        if ($request->filled('website')) return response()->json(['ok' => true, 'ref' => 'DV-OK']); // honeypot

        $v = Validator::make($request->all(), [
            'slug' => 'required|string', 'date' => 'required|date_format:Y-m-d',
            'adult' => 'required|integer|min:1|max:30', 'child' => 'nullable|integer|min:0|max:30', 'infant' => 'nullable|integer|min:0|max:30',
            'addons' => 'nullable|array', 'addons.*' => 'string',
            'name' => 'required|string|max:100', 'email' => 'required|email|max:150',
            'phone' => ['required', 'string', 'max:40', 'regex:/^[+\d][\d\s()-]{6,}$/'],
            'hotel' => 'nullable|string|max:200', 'notes' => 'nullable|string|max:1000',
        ], ['phone.regex' => 'Please enter a valid phone number.']);
        if ($v->fails()) return $this->fail($v->errors()->first());
        $d = $v->validated();

        $tour = Product::active()->tours()->where('slug', $d['slug'])->first();
        if (! $tour) return $this->fail('Tour not found', 404);

        $status = $this->availability->dayStatus($tour, $d['date']);
        if ($status === 'past') return $this->fail('Please choose a valid future date');
        if ($status === 'soldout') return $this->fail('Sorry, that date is fully booked. Please choose another date', 409);

        $adult = (int) $d['adult']; $child = (int) ($d['child'] ?? 0); $infant = (int) ($d['infant'] ?? 0);
        $childPrice = $tour->child_price ?? round($tour->price * 0.75);
        $addons = collect($tour->addons ?? [])->whereIn('key', $d['addons'] ?? []);
        $total = $adult * $tour->price + $child * $childPrice + $infant * $tour->infant_price
            + $addons->sum(fn ($a) => ($a['per'] ?? 'person') === 'person' ? $a['price'] * ($adult + $child) : $a['price']);

        $enquiry = Enquiry::create([
            'ref' => $this->ref('DV', Enquiry::class), 'product_id' => $tour->id, 'tour_title' => $tour->title,
            'date' => $d['date'], 'adults' => $adult, 'children' => $child, 'infants' => $infant,
            'addons' => $addons->pluck('label')->values()->all(), 'estimated_total' => $total,
            'customer_name' => $d['name'], 'email' => $d['email'], 'phone' => $d['phone'],
            'hotel' => $d['hotel'] ?? null, 'notes' => $d['notes'] ?? null, 'status' => 'new',
        ]);
        // TODO: email the team (SiteSetting::notification_email) + customer confirmation.

        return response()->json(['ok' => true, 'ref' => $enquiry->ref, 'tour' => $tour->title, 'total' => (float) $total]);
    }

    /** Ticket order. DEMO payment: marked paid immediately until N-Genius is connected. */
    public function storeOrder(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'items' => 'required|array|min:1|max:20',
            'items.*.slug' => 'required|string', 'items.*.date' => 'required|date_format:Y-m-d',
            'items.*.time' => 'nullable|string|max:10',
            'items.*.qty.adult' => 'nullable|integer|min:0|max:20', 'items.*.qty.child' => 'nullable|integer|min:0|max:20',
            'items.*.qty.infant' => 'nullable|integer|min:0|max:20',
            'customer.firstName' => 'required|string|max:60', 'customer.lastName' => 'required|string|max:60',
            'customer.email' => 'required|email|max:150',
            'customer.phone' => ['required', 'string', 'max:40', 'regex:/^\+\d{1,4} [\d\s()-]{6,}$/'],
            'customer.notes' => 'nullable|string|max:1000',
            'terms' => 'accepted',
        ], [
            'customer.firstName.required' => 'Please enter your full name', 'customer.lastName.required' => 'Please enter your full name',
            'customer.email.*' => 'Please enter a valid email', 'customer.phone.*' => 'Please enter a valid mobile number',
            'terms.accepted' => 'Please accept the terms', 'items.required' => 'Your cart is empty',
        ]);
        if ($v->fails()) return $this->fail($v->errors()->first());
        $d = $v->validated();

        $lines = [];
        foreach ($d['items'] as $raw) {
            $p = Product::active()->tickets()->where('slug', $raw['slug'])->first();
            if (! $p) return $this->fail('A ticket in your cart is no longer available');
            $day = $this->availability->dayStatus($p, $raw['date']);
            if ($day === 'past') return $this->fail("Please choose a future date for {$p->title}");
            if ($day === 'soldout') return $this->fail("{$p->title} is sold out on that date. Please pick another date", 409);

            $slots = $p->time_slots ?? [];
            $time = $raw['time'] ?? null;
            if ($slots && ! in_array($time, $slots, true)) return $this->fail("Please choose a visit time for {$p->title}");
            if ($slots && $this->availability->slotStatus($p, $raw['date'], $time) === 'soldout') {
                return $this->fail("The {$time} slot for {$p->title} is sold out. Please pick another time", 409);
            }

            $q = ['adult' => (int) ($raw['qty']['adult'] ?? 0), 'child' => (int) ($raw['qty']['child'] ?? 0), 'infant' => (int) ($raw['qty']['infant'] ?? 0)];
            if ($q['adult'] + $q['child'] === 0) return $this->fail("Add at least one adult or child ticket for {$p->title}");
            if ($q['infant'] && ! $q['adult']) return $this->fail('Infants must be accompanied by an adult');

            $child = $p->child_price ?? round($p->price * 0.8);
            $lines[] = [
                'product_id' => $p->id, 'title' => $p->title, 'date' => $raw['date'], 'time_slot' => $slots ? $time : null,
                'adults' => $q['adult'], 'children' => $q['child'], 'infants' => $q['infant'],
                'amount' => $q['adult'] * $p->price + $q['child'] * $child + $q['infant'] * $p->infant_price,
            ];
        }

        $c = $d['customer'];
        $order = DB::transaction(function () use ($lines, $c) {
            $order = Order::create([
                'ref' => $this->ref('DT', Order::class), 'status' => 'paid', 'total' => collect($lines)->sum('amount'),
                'first_name' => $c['firstName'], 'last_name' => $c['lastName'], 'email' => $c['email'], 'phone' => $c['phone'],
                'notes' => $c['notes'] ?? null,
                // DEMO until the gateway is connected: create an N-Genius order here, redirect to its payment page,
                // and set status=paid only after verifying the payment server-side.
                'payment_provider' => 'demo', 'payment_reference' => 'DEMO-' . Str::upper(Str::random(8)), 'paid_at' => now(),
            ]);
            $order->items()->createMany($lines);
            return $order;
        });

        return response()->json([
            'ok' => true, 'ref' => $order->ref, 'total' => (float) $order->total,
            'paymentUrl' => '/checkout/success?ref=' . urlencode($order->ref),
        ]);
    }

    /** Minimal order summary for the confirmation page. */
    public function showOrder(string $ref): JsonResponse
    {
        if (! preg_match('/^DT-[A-Z0-9]{6}$/', $ref)) return $this->fail('Not found', 404);
        $o = Order::with('items')->where('ref', $ref)->first();
        if (! $o || $o->created_at->lt(now()->subDays(30))) return $this->fail('Not found', 404);

        return response()->json([
            'ok' => true, 'ref' => $o->ref, 'status' => $o->payment_provider === 'demo' ? 'paid_demo' : $o->status,
            'total' => (float) $o->total,
            'customer' => ['firstName' => $o->first_name, 'email' => $o->email, 'phone' => $o->phone],
            'lines' => $o->items->map(fn ($i) => [
                'title' => $i->title, 'date' => $i->date->toDateString(), 'time' => $i->time_slot,
                'qty' => ['adult' => $i->adults, 'child' => $i->children, 'infant' => $i->infants], 'amount' => (float) $i->amount,
            ])->values(),
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['email' => 'required|email|max:150']);
        if ($v->fails()) return $this->fail('Please enter a valid email');
        Subscriber::firstOrCreate(['email' => strtolower($v->validated()['email'])], ['source' => $request->input('source', 'Website')]);
        return response()->json(['ok' => true]);
    }
}
