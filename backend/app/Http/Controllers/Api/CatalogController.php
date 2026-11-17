<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\Reel;
use App\Models\Review;
use App\Models\SiteSetting;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

/**
 * Read-only data for the Astro website. Response shapes mirror the website's
 * TypeScript types (frontend/src/data/*.ts) so pages don't need to change.
 */
class CatalogController extends Controller
{
    public static function media(?string $path): ?string
    {
        if (! $path) return null;
        return str_starts_with($path, 'http') ? $path : Storage::disk('public')->url($path);
    }

    public static function productUrl(Product $p): string
    {
        return '/' . ($p->type === 'ticket' ? 'tickets' : 'tours') . '/' . $p->slug;
    }

    public function catalog(): JsonResponse
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get()
            ->map(fn (Category $c) => [
                'slug' => $c->slug, 'name' => $c->name, 'icon' => $c->icon, 'blurb' => $c->blurb, 'color' => $c->color,
            ])->values();

        $products = Product::with('category')->active()->orderBy('sort_order')->get()
            ->filter(fn (Product $p) => $p->category?->is_active)
            ->map(fn (Product $p) => $this->product($p))->values();

        return response()->json(['categories' => $categories, 'products' => $products, 'site' => $this->site()])
            ->header('Cache-Control', 'public, max-age=30');
    }

    private function product(Product $p): array
    {
        $isTicket = $p->type === 'ticket';
        $child = $p->child_price !== null ? (float) $p->child_price : round($p->price * ($isTicket ? 0.8 : 0.75));
        $gallery = collect($p->gallery ?? [])->map(fn ($g) => self::media($g))->filter()->values();
        $cover = self::media($p->cover_image) ?? $gallery->first();
        if ($cover && ! $gallery->contains($cover)) $gallery->prepend($cover);

        return [
            'slug' => $p->slug,
            'type' => $p->type,
            'title' => $p->title,
            'category' => $p->category->slug,
            'location' => $p->location ?? '',
            'image' => $cover,
            'duration' => $p->duration ?? '',
            'timing' => $p->timing,
            'price' => (float) $p->price,
            'oldPrice' => $p->old_price ? (float) $p->old_price : null,
            'rating' => (float) $p->rating,
            'reviews' => (int) $p->reviews_count,
            'badge' => $p->badge,
            'featured' => (bool) $p->is_featured,
            'pickup' => (bool) $p->hotel_pickup,
            'instant' => $isTicket,
            'highlights' => $p->highlights ?? [],
            'details' => [
                'gallery' => $gallery->take(8)->all(),
                'overview' => collect(preg_split("/\n\s*\n/", (string) $p->overview))->map('trim')->filter()->values()->all(),
                'features' => $p->features ?? [],
                'inclusions' => $p->inclusions ?? [],
                'exclusions' => $p->exclusions ?? [],
                'itinerary' => $p->itinerary ?? [],
                'pickup' => $p->hotel_pickup ? ['areas' => $p->pickup_areas ?? '', 'time' => $p->pickup_time ?? ''] : null,
                'info' => $p->good_to_know ?? [],
                'cancellation' => $p->cancellation ?? [],
                'faqs' => $p->faqs ?? [],
                'language' => $p->languages ?? 'English',
                'groupType' => $p->group_type ?? '',
                'prices' => [
                    ['key' => 'adult', 'label' => 'Adult', 'sub' => 'Age 12+', 'price' => (float) $p->price],
                    ['key' => 'child', 'label' => 'Child', 'sub' => 'Age 3–11', 'price' => $child],
                    ['key' => 'infant', 'label' => 'Infant', 'sub' => 'Under 3', 'price' => (float) $p->infant_price],
                ],
                'addons' => collect($p->addons ?? [])->map(fn ($a) => [
                    'key' => $a['key'], 'label' => $a['label'], 'sub' => $a['sub'] ?? null,
                    'price' => (float) $a['price'], 'per' => $a['per'] ?? 'person',
                ])->values()->all(),
                'timeSlots' => $isTicket ? array_values($p->time_slots ?? []) : [],
                'minGuests' => 1,
                'minNoticeDays' => (int) $p->min_notice_days,
            ],
            'seo' => ['title' => $p->seo_title, 'description' => $p->seo_description],
        ];
    }

    private function site(): array
    {
        $s = SiteSetting::current();
        $wa = preg_replace('/\D+/', '', (string) $s->whatsapp);

        return [
            'name' => 'Desert Voyagers Tourism',
            'legalName' => $s->company_name,
            'phone' => $s->phone,
            'phoneHref' => 'tel:' . preg_replace('/[^\d+]/', '', (string) $s->phone),
            'whatsappNumber' => $wa,
            'whatsapp' => "https://wa.me/{$wa}?text=" . rawurlencode('Hi Desert Voyagers, I would like to book a tour'),
            'email' => $s->email,
            'hours' => $s->hours,
            'address' => $s->address,
            'social' => ['facebook' => $s->facebook, 'instagram' => $s->instagram, 'youtube' => $s->youtube],
            'award' => ['name' => $s->award_name, 'year' => $s->award_year, 'href' => $s->award_url],
            'rating' => ['average' => (float) $s->rating_average, 'total' => (int) $s->rating_total],
        ];
    }

    public function content(): JsonResponse
    {
        $reviews = Review::with('product')->where('is_published', true)->where('is_featured', true)
            ->orderByDesc('review_date')->limit(12)->get()->map(fn (Review $r) => [
                'name' => $r->name, 'country' => $r->country ?? '', 'flag' => '',
                'date' => $r->review_date?->format('M Y') ?? '', 'rating' => (int) $r->rating,
                'title' => $r->title ?? '', 'text' => $r->text,
                'tour' => $r->product?->title ?? '', 'tourHref' => $r->product ? self::productUrl($r->product) : '#',
            ])->values();

        $reels = Reel::with('product')->where('is_published', true)->orderBy('sort_order')->get()->map(fn (Reel $r) => [
            'title' => $r->title, 'guest' => $r->guest ?? '', 'duration' => $r->duration ?? '',
            'poster' => self::media($r->poster), 'video' => self::media($r->video), 'youtube' => $r->youtube_id,
            'tour' => $r->product?->title ?? '', 'tourHref' => $r->product ? self::productUrl($r->product) : '/tours',
        ])->values();

        $products = Product::active()->get()->keyBy('id');
        $posts = Post::where('is_published', true)->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderByDesc('published_at')->get()->map(fn (Post $p) => [
                'slug' => $p->slug, 'title' => $p->title, 'excerpt' => $p->excerpt ?? '',
                'image' => self::media($p->cover_image), 'category' => $p->category ?? 'Travel',
                'date' => ($p->published_at ?? $p->created_at)->toDateString(), 'readTime' => $p->read_time ?? '',
                'html' => (string) $p->body,
                'related' => collect($p->related_products ?? [])->map(fn ($id) => $products->get($id))->filter()
                    ->map(fn ($prod) => self::productUrl($prod))->values()->all(),
                'seo' => ['title' => $p->seo_title, 'description' => $p->seo_description],
            ])->values();

        $s = SiteSetting::current();

        return response()->json([
            'reviews' => $reviews,
            'reviewSummary' => ['average' => (float) ($s->rating_average ?? 5), 'total' => (int) ($s->rating_total ?? 0), 'breakdown' => $this->breakdown()],
            'reels' => $reels,
            'posts' => $posts,
        ])->header('Cache-Control', 'public, max-age=30');
    }

    /** % of published reviews per star (5★..1★). */
    private function breakdown(): array
    {
        $counts = Review::where('is_published', true)->selectRaw('rating, count(*) c')->groupBy('rating')->pluck('c', 'rating');
        $total = max(1, $counts->sum());
        return collect([5, 4, 3, 2, 1])->map(fn ($n) => (int) round(($counts[$n] ?? 0) / $total * 100))->all();
    }

    public function availability(string $slug, AvailabilityService $svc): JsonResponse
    {
        $product = Product::active()->where('slug', $slug)->firstOrFail();
        return response()->json($svc->overrides($product))->header('Cache-Control', 'no-cache');
    }
}
