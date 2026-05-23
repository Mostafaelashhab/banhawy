<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\PushSubscription;
use App\Models\User;
use App\Services\PushSender;
use Illuminate\Console\Command;

class SendNudges extends Command
{
    protected $signature = 'banhawy:nudge
        {--audience=auto : merchants | customers | auto (both, balanced by user role)}
        {--limit=0 : Max users to nudge this run (0 = unlimited)}
        {--dry : Don\'t send, just log what would be sent}';

    protected $description = 'Send motivational push nudges to merchants & customers to drive engagement.';

    public function handle(PushSender $sender): int
    {
        $audience = $this->option('audience');
        $limit    = (int) $this->option('limit');
        $dry      = (bool) $this->option('dry');

        $sent = 0;

        if (in_array($audience, ['merchants', 'auto'], true)) {
            $sent += $this->nudgeMerchants($sender, $limit, $dry);
        }

        if (in_array($audience, ['customers', 'auto'], true)) {
            $sent += $this->nudgeCustomers($sender, $limit, $dry);
        }

        $this->info($dry ? "DRY-RUN: would have nudged $sent device(s)." : "Sent $sent nudge(s) ✓");
        return self::SUCCESS;
    }

    /**
     * Merchant nudges — picked based on the merchant's own state so it feels relevant.
     */
    private function nudgeMerchants(PushSender $sender, int $limit, bool $dry): int
    {
        $owners = User::where('role', 'owner')
            ->whereHas('pushSubscriptions')
            ->with(['businesses' => fn ($q) => $q->withCount('orders')])
            ->when($limit > 0, fn ($q) => $q->limit($limit))
            ->get();

        $count = 0;
        foreach ($owners as $owner) {
            $biz = $owner->businesses->first();
            if (! $biz) continue;

            $payload = $this->pickMerchantNudge($biz);
            if (! $payload) continue;

            $payload['tag'] = 'nudge-merchant-' . now()->format('Ymd-H') . '-' . $owner->id;
            $payload['url'] = $payload['url'] ?? route('merchant.dashboard');

            if ($dry) {
                $this->line("[merchant {$owner->id}] {$payload['title']} :: {$payload['body']}");
                $count++;
                continue;
            }

            try {
                $count += $sender->toUser($owner, $payload);
            } catch (\Throwable $e) {
                $this->warn("Failed to nudge merchant {$owner->id}: {$e->getMessage()}");
            }
        }

        return $count;
    }

    private function pickMerchantNudge(Business $biz): ?array
    {
        // Tailored nudges, picked in priority order — only the most relevant one fires
        $imagesCount = is_array($biz->images) ? count($biz->images) : 0;

        if ($imagesCount === 0) {
            return [
                'title' => '📸 صور متجرك تبيع أكتر',
                'body'  => 'المتاجر اللي عليها صور بتاخد زيارات أكتر بـ 3x — ارفع صورك دلوقتي.',
                'url'   => route('merchant.photos'),
            ];
        }

        if (($biz->setup_progress ?? 0) < 100) {
            return [
                'title' => '⚙️ كمّل إعداد متجرك',
                'body'  => "ملف متجرك مكتمل بنسبة {$biz->setup_progress}% — كمّل الباقي وافتح كل المزايا.",
                'url'   => route('merchant.settings'),
            ];
        }

        $newOrders = \App\Models\Order::where('business_id', $biz->id)
            ->where('status', 'new')->count();
        if ($newOrders > 0) {
            return [
                'title' => '🛒 طلبات في انتظارك',
                'body'  => "عندك $newOrders طلب جديد ما اترديش عليه. ادخل ورد على عملاءك.",
                'url'   => route('merchant.orders.index'),
            ];
        }

        if ($biz->views_count >= 20 && $biz->reviews_count < 3) {
            return [
                'title' => '⭐ خلّي زبايتك يقيّموك',
                'body'  => "متجرك جابلك {$biz->views_count} زيارة بس لسه قليل التقييمات — اطلب من زبايتك يقيّموك.",
                'url'   => route('business.show', $biz),
            ];
        }

        // Default: positive reinforcement nudge from a pool — adds variety so it doesn't feel robotic
        return collect([
            [
                'title' => '📣 شارك متجرك',
                'body'  => 'ابعت رمز QR لعملائك على واتساب — كل عميل يدخل = زيارة جديدة.',
                'url'   => route('merchant.qr'),
            ],
            [
                'title' => '🎯 خلّيك أعلى في النتائج',
                'body'  => 'فعّل خطة Pro وابقى في أول صفحة بحث.',
                'url'   => route('merchant.dashboard'),
            ],
            [
                'title' => '👀 شوف زبايتك بيدوّروا على إيه',
                'body'  => 'ادخل التحليلات شوف زيارات الأسبوع وضغطات الواتساب.',
                'url'   => route('merchant.analytics'),
            ],
            [
                'title' => '💚 شكراً إنك معانا',
                'body'  => 'بنهاوي بيكبر كل يوم بفضل تجار زيك. استمر معانا!',
                'url'   => route('merchant.dashboard'),
            ],
        ])->random();
    }

    /**
     * Customer nudges — light & sparse so we don't burn out subscribers.
     * Skip users who already got a nudge in the past 36 hours (best-effort via tag).
     */
    private function nudgeCustomers(PushSender $sender, int $limit, bool $dry): int
    {
        $customers = User::where('role', 'customer')
            ->whereHas('pushSubscriptions')
            ->when($limit > 0, fn ($q) => $q->limit($limit))
            ->get();

        $featured = Business::where('is_active', true)
            ->where('is_featured', true)
            ->orderByDesc('rating')
            ->take(20)
            ->get();

        $messages = [
            [
                'title' => '🍽️ جوعان؟ شوف بنهاوي',
                'body'  => 'اكتشف مطاعم وكافيهات بنها — الأعلى تقييماً قريب منك.',
                'url'   => route('home'),
            ],
            [
                'title' => '✨ متاجر جديدة انضمّت',
                'body'  => 'شوف آخر المتاجر اللي وصلت بنهاوي وجرّبهم.',
                'url'   => route('home'),
            ],
            [
                'title' => '⭐ متاجر مميّزة في بنها',
                'body'  => 'تقييماتها عالية ومنصوح بيها — جرّبها اليوم.',
                'url'   => route('home'),
            ],
            [
                'title' => '🗺️ اكتشف ما حولك',
                'body'  => 'افتح خريطة بنهاوي وشوف الأنشطة اللي قريب منك.',
                'url'   => route('map'),
            ],
            [
                'title' => '💚 المفضّلة بتاعتك',
                'body'  => 'ارجع لمحلاتك المفضّلة وشوف لو فيه جديد.',
                'url'   => route('home'),
            ],
        ];

        // If we have featured businesses, tailor one of the messages
        if ($featured->count() > 0) {
            $top = $featured->first();
            $messages[] = [
                'title' => '⭐ ' . $top->name,
                'body'  => 'تقييم ' . number_format($top->rating, 1) . ' من ' . $top->reviews_count . ' عميل — جرّبه!',
                'url'   => route('business.show', $top),
            ];
        }

        $count = 0;
        foreach ($customers as $customer) {
            $payload = collect($messages)->random();
            $payload['tag'] = 'nudge-customer-' . now()->format('Ymd-H') . '-' . $customer->id;

            if ($dry) {
                $this->line("[customer {$customer->id}] {$payload['title']} :: {$payload['body']}");
                $count++;
                continue;
            }

            try {
                $count += $sender->toUser($customer, $payload);
            } catch (\Throwable $e) {
                $this->warn("Failed to nudge customer {$customer->id}: {$e->getMessage()}");
            }
        }

        return $count;
    }
}
