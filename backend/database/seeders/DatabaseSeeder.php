<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\Reel;
use App\Models\Review;
use App\Models\SiteSetting;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->adminUser();
        $data = json_decode(file_get_contents(__DIR__ . '/data/website-sample.json'), true);
        $this->catalog($data);
        $this->content($data);
        $this->settings($data);
        $this->demoBookings();
    }

    /** Admin login from ADMIN_EMAIL / ADMIN_PASSWORD in .env */
    private function adminUser(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@desertvoyagerstourism.com');
        $password = env('ADMIN_PASSWORD');
        if (! $password) {
            $this->command->warn('ADMIN_PASSWORD is not set in .env, skipping admin user.');
            return;
        }
        User::updateOrCreate(['email' => $email], ['name' => 'Admin', 'password' => Hash::make($password)]);
        $this->command->info("Admin user: {$email} (password from .env)");
    }

    private function catalog(array $data): void
    {
        $colors = [
            'desert-safari' => '#e8873a', 'city-tours' => '#2f64ae', 'tickets' => '#3c9a4a',
            'theme-parks' => '#e0a021', 'water' => '#0e9aa7', 'adventure' => '#c0564a',
        ];
        $catIds = [];
        foreach ($data['categories'] as $i => $c) {
            $catIds[$c['slug']] = Category::updateOrCreate(['slug' => $c['slug']], [
                'name' => $c['name'], 'icon' => $c['slug'], 'blurb' => $c['blurb'],
                'color' => $colors[$c['slug']] ?? null, 'sort_order' => $i, 'is_active' => true,
            ])->id;
        }

        foreach ($data['products'] as $p) {
            $d = $p['details'];
            $price = fn ($key) => collect($d['prices'])->firstWhere('key', $key)['price'] ?? null;
            Product::updateOrCreate(['slug' => $p['slug']], [
                'category_id' => $catIds[$p['category']],
                'type' => $p['type'],
                'title' => $p['title'],
                'location' => $p['location'],
                'duration' => $p['duration'],
                'timing' => $p['timing'] ?? null,
                'price' => $p['price'],
                'old_price' => $p['oldPrice'] ?? null,
                'child_price' => $price('child'),
                'infant_price' => $price('infant') ?? 0,
                'addons' => $d['addons'],
                'badge' => $p['badge'] ?? null,
                'is_featured' => $p['featured'],
                'is_active' => true,
                'hotel_pickup' => $p['pickup'],
                'rating' => $p['rating'],
                'reviews_count' => $p['reviews'],
                'sort_order' => $p['sort_order'],
                'cover_image' => $p['cover_image'],
                'gallery' => $d['gallery'],
                'highlights' => $p['highlights'],
                'overview' => implode("\n\n", $d['overview']),
                'features' => $d['features'],
                'inclusions' => $d['inclusions'],
                'exclusions' => $d['exclusions'],
                'itinerary' => $d['itinerary'],
                'pickup_areas' => $d['pickup']['areas'] ?? null,
                'pickup_time' => $d['pickup']['time'] ?? null,
                'good_to_know' => $d['info'],
                'cancellation' => $d['cancellation'],
                'faqs' => $d['faqs'],
                'group_type' => $d['groupType'],
                'languages' => $d['language'],
                'time_slots' => $d['timeSlots'],
                'min_notice_days' => $p['type'] === 'tour' ? 1 : 0,
            ]);
        }

        // A few sample availability rules so the calendar shows sold-out / few-left days
        $burj = Product::where('slug', 'burj-khalifa-at-the-top')->first();
        $safari = Product::where('slug', 'evening-desert-safari')->first();
        foreach ([[$safari, 5, null, 'soldout', 'Private event'], [$safari, 9, null, 'limited', null],
                  [$burj, 3, '17:30', 'soldout', 'Sunset slot full'], [$burj, 6, null, 'limited', null]] as [$prod, $days, $slot, $status, $note]) {
            if ($prod) Availability::updateOrCreate(
                ['product_id' => $prod->id, 'date' => today()->addDays($days)->toDateString(), 'time_slot' => $slot],
                ['status' => $status, 'note' => $note]
            );
        }
    }

    private function content(array $data): void
    {
        $bySlug = Product::pluck('id', 'slug');
        $slugFromHref = fn (?string $href) => $href ? $bySlug[basename($href)] ?? null : null;

        foreach ($data['reviews'] as $r) {
            Review::updateOrCreate(['name' => $r['name'], 'title' => $r['title']], [
                'product_id' => $slugFromHref($r['tourHref']), 'country' => $r['country'], 'rating' => $r['rating'],
                'text' => $r['text'], 'review_date' => Carbon::parse('1 ' . $r['date']), 'source' => 'Website',
                'is_published' => true, 'is_featured' => true,
            ]);
        }

        foreach ($data['reels'] as $i => $r) {
            Reel::updateOrCreate(['title' => $r['title']], [
                'guest' => $r['guest'], 'product_id' => $slugFromHref($r['tourHref']), 'poster' => $r['poster'],
                'youtube_id' => $r['youtube'] ?? null, 'duration' => $r['duration'], 'sort_order' => $i, 'is_published' => true,
            ]);
        }

        foreach ($data['posts'] as $p) {
            $body = collect($p['body'])->map(fn ($b) => (! empty($b['h']) ? '<h2>' . e($b['h']) . '</h2>' : '')
                . collect($b['p'])->map(fn ($t) => '<p>' . e($t) . '</p>')->join(''))->join('');
            Post::updateOrCreate(['slug' => $p['slug']], [
                'title' => $p['title'], 'category' => $p['category'], 'excerpt' => $p['excerpt'],
                'cover_image' => $p['image'], 'body' => $body, 'read_time' => $p['readTime'],
                'related_products' => collect($p['related'])->map($slugFromHref)->filter()->values()->all(),
                'published_at' => Carbon::parse($p['date']), 'is_published' => true,
            ]);
        }
    }

    private function settings(array $data): void
    {
        $s = $data['site'];
        SiteSetting::current()->update([
            'company_name' => $s['legalName'], 'phone' => $s['phone'], 'whatsapp' => '971585721121',
            'email' => $s['email'], 'hours' => $s['hours'], 'address' => $s['address'],
            'facebook' => $s['social']['facebook'], 'instagram' => $s['social']['instagram'], 'youtube' => $s['social']['youtube'],
            'award_name' => $s['award']['name'], 'award_year' => $s['award']['year'], 'award_url' => $s['award']['href'],
            'rating_average' => $data['reviewSummary']['average'], 'rating_total' => $data['reviewSummary']['total'],
            'notification_email' => $s['email'],
        ]);
    }

    /** DEMO bookings so the dashboard isn't empty. Delete them from the admin before launch. */
    private function demoBookings(): void
    {
        if (Enquiry::where('notes', 'like', '[DEMO]%')->exists()) return;
        $tours = Product::tours()->get();
        $tickets = Product::tickets()->get();
        $names = ['Demo Guest A', 'Demo Guest B', 'Demo Guest C', 'Demo Guest D', 'Demo Guest E', 'Demo Guest F'];
        $statuses = ['new', 'new', 'new', 'contacted', 'confirmed', 'confirmed', 'completed', 'cancelled'];

        foreach (range(1, 18) as $i) {
            $t = $tours->random();
            $adults = rand(1, 4); $children = rand(0, 2);
            $created = now()->subDays(rand(0, 28))->subMinutes(rand(0, 600));
            Enquiry::create([
                'ref' => 'DV-' . Str::upper(Str::random(6)), 'product_id' => $t->id, 'tour_title' => $t->title,
                'date' => today()->addDays(rand(-5, 20)), 'adults' => $adults, 'children' => $children,
                'estimated_total' => $adults * $t->price + $children * ($t->child_price ?? $t->price),
                'customer_name' => $names[array_rand($names)], 'email' => "demo{$i}@example.com", 'phone' => '+971 50 000 00' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'hotel' => 'Demo Hotel, Abu Dhabi', 'notes' => '[DEMO] Sample booking request',
                'status' => $statuses[array_rand($statuses)], 'created_at' => $created, 'updated_at' => $created,
            ]);
        }

        foreach (range(1, 14) as $i) {
            $created = now()->subDays(rand(0, 28))->subMinutes(rand(0, 600));
            $order = Order::create([
                'ref' => 'DT-' . Str::upper(Str::random(6)), 'status' => $i % 7 === 0 ? 'pending' : 'paid', 'total' => 0,
                'first_name' => 'Demo', 'last_name' => 'Customer ' . $i, 'email' => "order{$i}@example.com",
                'phone' => '+971 55 000 00' . str_pad($i, 2, '0', STR_PAD_LEFT), 'notes' => '[DEMO] Sample order',
                'payment_reference' => $i % 7 === 0 ? null : 'demo-' . Str::lower(Str::random(10)),
                'paid_at' => $i % 7 === 0 ? null : $created, 'tickets_sent' => $i % 3 !== 0,
                'created_at' => $created, 'updated_at' => $created,
            ]);
            $total = 0;
            foreach ($tickets->random(rand(1, 2)) as $tk) {
                $a = rand(1, 3); $c = rand(0, 2);
                $amount = $a * $tk->price + $c * ($tk->child_price ?? $tk->price);
                $total += $amount;
                $order->items()->create([
                    'product_id' => $tk->id, 'title' => $tk->title, 'date' => today()->addDays(rand(1, 20)),
                    'time_slot' => collect($tk->time_slots)->random(), 'adults' => $a, 'children' => $c, 'amount' => $amount,
                ]);
            }
            $order->update(['total' => $total]);
        }

        foreach (['traveller.one@example.com', 'traveller.two@example.com', 'traveller.three@example.com'] as $e) {
            Subscriber::firstOrCreate(['email' => $e], ['source' => 'Footer']);
        }
    }
}
