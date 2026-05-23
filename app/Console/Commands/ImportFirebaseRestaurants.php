<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\BusinessType;
use App\Models\Review;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportFirebaseRestaurants extends Command
{
    protected $signature = 'banhawy:import-firebase
        {--url=https://banha-restaurants.firebaseio.com/.json : Source JSON URL}
        {--limit= : Max restaurants to import (default: all)}
        {--fresh : Wipe imported businesses+reviews before importing}';

    protected $description = 'Import businesses + reviews + menu images from the public Firebase dataset.';

    public function handle(): int
    {
        $url   = $this->option('url');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        if ($this->option('fresh')) {
            $this->warn('Wiping previously imported records...');
            Review::whereNotNull('firebase_id')->delete();
            Business::whereNotNull('firebase_id')->delete();
        }

        $this->info("Fetching $url ...");
        $response = Http::timeout(60)->get($url);
        if (! $response->ok()) {
            $this->error('Failed to fetch JSON: HTTP '.$response->status());
            return self::FAILURE;
        }

        $data = $response->json();
        $restaurants = $data['restaurant'] ?? [];
        $this->info('Top-level restaurants: '.count($restaurants));

        $type = BusinessType::where('slug', 'restaurant')->firstOrFail();

        $imported = 0;
        $reviewsImported = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar(min(count($restaurants), $limit ?? PHP_INT_MAX));
        $bar->start();

        foreach ($restaurants as $firebaseId => $entry) {
            if ($limit && $imported >= $limit) break;

            if (! $this->looksLikeRealEntry($firebaseId, $entry)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            try {
                $business = $this->upsertBusiness((string) $firebaseId, $entry, $type->id);
                if (! $business) { $skipped++; $bar->advance(); continue; }
                $reviewsImported += $this->upsertReviews($business, $entry['comment'] ?? []);
                $imported++;
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("Failed for '$firebaseId': ".$e->getMessage());
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Imported businesses: $imported");
        $this->info("Imported reviews:    $reviewsImported");
        $this->info("Skipped entries:     $skipped");

        return self::SUCCESS;
    }

    private function looksLikeRealEntry(string $key, $entry): bool
    {
        unset($key);
        if (! is_array($entry)) return false;
        if (! isset($entry['name']) || ! is_string($entry['name'])) return false;

        $name = trim($entry['name']);
        if ($name === '' || mb_strlen($name) < 2) return false;

        // Has at least one branch with a location
        $branches = is_array($entry['branch'] ?? null) ? $entry['branch'] : [];
        foreach ($branches as $br) {
            if (is_array($br) && isset($br['map']['lat'], $br['map']['lng'])) {
                return true;
            }
        }
        return false;
    }

    private function upsertBusiness(string $firebaseId, array $entry, int $typeId): ?Business
    {
        $branches = $entry['branch'] ?? [];
        $primary  = null;
        foreach ($branches as $br) {
            if (is_array($br) && isset($br['map']['lat'], $br['map']['lng'])) { $primary = $br; break; }
        }
        if (! $primary) return null;

        $lat = (float) $primary['map']['lat'];
        $lng = (float) $primary['map']['lng'];
        if ($lat === 0.0 || $lng === 0.0) return null;

        $address = trim((string) ($primary['details'] ?? 'بنها'));
        if ($address === '') $address = 'بنها';

        $name = trim($entry['name']);

        // Categories: joined by · for display
        $cats = [];
        if (is_array($entry['category'] ?? null)) {
            foreach ($entry['category'] as $catLabel) {
                if (is_string($catLabel) && $catLabel !== '') $cats[] = $catLabel;
            }
        }
        $category = $cats ? implode(' · ', array_unique($cats)) : null;

        // Rating from rate{rate1..rate5,total}
        [$rating, $reviewsCount] = $this->computeRating($entry['rate'] ?? null, $entry['comment'] ?? null);

        $images = $this->collectImages($entry);
        $logo   = is_string($entry['logo'] ?? null) && $entry['logo'] !== '' ? $entry['logo'] : ($images[0] ?? null);

        $phone = $this->cleanPhone($entry['phoneAdmin'] ?? $entry['shkawa'] ?? null);
        $whats = $phone ?? '01000000000';

        $isDelete = ($entry['isDelete'] ?? 'no') === 'yes';
        $isActive = ! $isDelete;

        $slug = $this->ensureUniqueSlug($name, $firebaseId);

        return Business::updateOrCreate(
            ['firebase_id' => $firebaseId],
            [
                'owner_id'         => null,
                'business_type_id' => $typeId,
                'plan_id'          => null,
                'name'             => $name,
                'slug'             => $slug,
                'category'         => $category,
                'description'      => null,
                'whatsapp'         => $whats,
                'phone'            => $phone,
                'address'          => $address,
                'lat'              => $lat,
                'lng'              => $lng,
                'logo'             => $logo,
                'cover'            => $images[0] ?? null,
                'images'           => $images,
                'price_range'      => 'medium',
                'delivery'         => $this->delivery($entry),
                'orders_via'       => 'whatsapp',
                'bookings_via'     => 'walkin',
                'is_active'        => $isActive,
                'is_verified'      => false,
                'is_featured'      => false,
                'rating'           => $rating,
                'reviews_count'    => $reviewsCount,
            ]
        );
    }

    private function ensureUniqueSlug(string $name, string $firebaseId): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            // Pure-Arabic name — Str::slug returns ''. Use transliterated firebase id.
            $base = Str::slug(Str::ascii($name)) ?: 'biz';
        }

        $slug   = $base.'-'.Str::lower(substr(md5($firebaseId), 0, 5));
        $exists = Business::where('slug', $slug)
            ->where('firebase_id', '!=', $firebaseId)
            ->exists();

        if (! $exists) return $slug;

        // Final fallback — append more entropy
        return $base.'-'.Str::lower(substr(md5($firebaseId.microtime(true)), 0, 8));
    }

    private function computeRating($rateField, $commentField): array
    {
        // Prefer counting from actual comments, since the `rate.total` field is unreliable
        $sum = 0; $count = 0;
        if (is_array($commentField)) {
            foreach ($commentField as $c) {
                if (! is_array($c)) continue;
                $r = (int) ($c['rate'] ?? 0);
                if ($r >= 1 && $r <= 5) { $sum += $r; $count++; }
            }
        }

        if ($count > 0) {
            return [round($sum / $count, 2), $count];
        }

        // Fallback to aggregate field
        if (is_array($rateField)) {
            $total = (int) ($rateField['total'] ?? 0);
            if ($total >= 1 && $total <= 5) {
                $n = 0;
                for ($i = 1; $i <= 5; $i++) $n += (int) ($rateField["rate$i"] ?? 0);
                return [(float) $total, $n];
            }
        }
        return [0.0, 0];
    }

    private function collectImages(array $entry): array
    {
        $out = [];
        $menu = $entry['menu'] ?? null;
        if (is_array($menu)) {
            foreach ($menu as $u) {
                if (is_string($u) && str_starts_with($u, 'http')) $out[] = $u;
            }
        }
        $logo = $entry['logo'] ?? null;
        if (is_string($logo) && str_starts_with($logo, 'http')) {
            array_unshift($out, $logo);
        }
        return array_values(array_unique($out));
    }

    private function delivery(array $entry): bool
    {
        $t = strtolower((string) ($entry['delTime'] ?? ''));
        if ($t === '' || str_contains($t, 'لا يوجد')) return false;
        return true;
    }

    private function cleanPhone(?string $p): ?string
    {
        if (! is_string($p)) return null;
        $p = preg_replace('/[^\d+]/', '', $p);
        if (! $p) return null;
        if (strlen($p) > 20) $p = substr($p, 0, 20);
        return $p;
    }

    private function upsertReviews(Business $business, $comments): int
    {
        if (! is_array($comments)) return 0;
        $n = 0;

        foreach ($comments as $fid => $c) {
            if (! is_array($c)) continue;
            $rating = (int) ($c['rate'] ?? 0);
            if ($rating < 1) $rating = 1;
            if ($rating > 5) $rating = 5;

            $body  = trim((string) ($c['desc'] ?? ''));
            $phone = $this->cleanPhone($c['phone'] ?? null);
            $date  = $this->parseArabicDate($c['date'] ?? null);

            $replies = [];
            if (is_array($c['reply'] ?? null)) {
                foreach ($c['reply'] as $rep) {
                    if (! is_array($rep)) continue;
                    $replies[] = [
                        'date' => $this->parseArabicDate($rep['date'] ?? null)?->toDateString(),
                        'body' => trim((string) ($rep['desc'] ?? '')),
                    ];
                }
            }

            Review::updateOrCreate(
                ['firebase_id' => (string) $fid],
                [
                    'business_id'    => $business->id,
                    'user_id'        => null,
                    'reviewer_name'  => null,
                    'reviewer_phone' => $phone,
                    'rating'         => $rating,
                    'body'           => $body !== '' ? $body : null,
                    'replies'        => $replies ?: null,
                    'created_at'     => $date ?? now(),
                    'updated_at'     => $date ?? now(),
                ]
            );
            $n++;
        }
        return $n;
    }

    private function parseArabicDate(?string $s): ?Carbon
    {
        if (! is_string($s) || trim($s) === '') return null;
        // Convert Arabic-Indic digits → Western
        $tr = strtr($s, [
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4',
            '٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
            '/'=>'-',
        ]);
        try {
            return Carbon::parse(trim($tr));
        } catch (\Throwable) {
            return null;
        }
    }
}
