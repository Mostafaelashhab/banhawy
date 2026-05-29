<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessType;
use App\Models\BusinessView;
use App\Models\Plan;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WhatsappClick;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Services-focused demo seeder.
 * Creates a realistic mix of shipping companies and service providers (handymen)
 * across the 3 plan tiers (free / pro / business) so we can showcase how the
 * platform looks with real-ish content and the plan-based perks actually visible.
 */
class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        $shipping = BusinessType::where('slug', 'shipping')->firstOrFail();
        $service  = BusinessType::where('slug', 'service')->firstOrFail();

        $planFree     = Plan::where('slug', 'free')->first();
        $planPro      = Plan::where('slug', 'pro')->first();
        $planBusiness = Plan::where('slug', 'business')->first();

        // ── SHIPPING COMPANIES ──────────────────────────────────────────────
        $this->seedShipping($shipping, $planFree, $planPro, $planBusiness);

        // ── SERVICE PROVIDERS (handymen, contractors) ───────────────────────
        $this->seedServices($service, $planFree, $planPro, $planBusiness);
    }

    private function seedShipping(BusinessType $type, ?Plan $free, ?Plan $pro, ?Plan $business): void
    {
        $hoursMornNight = collect(range(0, 6))->mapWithKeys(fn ($d) => [
            $d => ['open' => '08:00', 'close' => '22:00', 'closed' => false],
        ])->all();

        $hours24 = collect(range(0, 6))->mapWithKeys(fn ($d) => [
            $d => ['open' => '00:00', 'close' => '23:59', 'closed' => false],
        ])->all();

        // 1) Mostafa Express — Business tier showcase
        $this->upsertBusiness([
            'type'         => $type,
            'plan'         => $business,
            'owner'        => ['mostafa-express@banhawy.local', 'مصطفى عبد الفتاح', '01277581700'],
            'slug'         => 'mostafa-express',
            'name'         => 'Mostafa Express',
            'category'     => 'شحن داخل بنها · شحن سريع 24 ساعة',
            'description'  => 'شركة شحن متخصصة في تغطية بنها وضواحيها — استلام من باب البيت وتوصيل لأي مكان في القليوبية.',
            'whatsapp'     => '01277581700',
            'phone'        => '01277581700',
            'address'      => 'شارع طه الحكيم · بنها',
            'lat'          => 30.4592, 'lng' => 31.1850,
            'is_featured'  => true,
            'is_verified'  => true,
            'delivery'     => true,
            'hours'        => $hours24,
            'rating'       => 4.7,
            'reviews_count'=> 84,
            'views_count'  => 1820,
            'wa_clicks'    => 412,
            'reviews'      => [
                ['أحمد ع.',   5, 'استلام في نص ساعة، توصيل اليوم التالي. ممتازين فعلاً.'],
                ['مروة س.',   5, 'بحبهم لأنهم بيردوا على الواتس بسرعة.'],
                ['وليد ر.',   4, 'الأسعار معقولة، التوصيل أحياناً يتأخر شوية.'],
                ['سلمى ف.',   5, 'شغلهم نضيف ومحترم.'],
            ],
            'history_days' => 30,
        ]);

        // 2) SpeedBox — Pro tier
        $this->upsertBusiness([
            'type'         => $type,
            'plan'         => $pro,
            'owner'        => ['speedbox@banhawy.local', 'هاني السيد', '01005558822'],
            'slug'         => 'speedbox',
            'name'         => 'SpeedBox',
            'category'     => 'شحن سريع · بضائع وأثاث',
            'description'  => 'تخصصنا في الشحن السريع للبضائع المهمة — تتبع لحظة بلحظة.',
            'whatsapp'     => '01005558822',
            'phone'        => '01005558822',
            'address'      => 'كوبري النحاس · بنها',
            'lat'          => 30.4631, 'lng' => 31.1839,
            'is_verified'  => true,
            'delivery'     => true,
            'hours'        => $hoursMornNight,
            'rating'       => 4.5,
            'reviews_count'=> 48,
            'views_count'  => 910,
            'wa_clicks'    => 184,
            'reviews'      => [
                ['كريم ج.',   5, 'بعتلي طرد تقيل من القاهرة لبنها في يوم.'],
                ['نهى م.',    4, 'سعرهم أعلى شوية لكن الخدمة محترمة.'],
                ['أحمد ف.',   5, 'تتبع الطلب بالواتساب ميزة جامدة.'],
            ],
            'history_days' => 30,
        ]);

        // 3) بنها كارجو — Pro tier
        $this->upsertBusiness([
            'type'         => $type,
            'plan'         => $pro,
            'owner'        => ['banha-cargo@banhawy.local', 'إبراهيم زكي', '01122334455'],
            'slug'         => 'banha-cargo',
            'name'         => 'بنها كارجو',
            'category'     => 'بضائع وأثاث · نقل المحلات',
            'description'  => 'متخصصون في نقل الأثاث والبضائع التجارية بين المحافظات.',
            'whatsapp'     => '01122334455',
            'phone'        => '01122334455',
            'address'      => 'شارع كوبري الفحص · بنها',
            'lat'          => 30.4655, 'lng' => 31.1882,
            'is_verified'  => true,
            'delivery'     => true,
            'hours'        => $hoursMornNight,
            'rating'       => 4.4,
            'reviews_count'=> 31,
            'views_count'  => 612,
            'wa_clicks'    => 96,
            'reviews'      => [
                ['عمرو ك.',   5, 'نقلوا محتويات شقة كاملة في يوم بدون أي خدش.'],
                ['ياسمين أ.', 4, 'فريق محترم، بس وصلوا متأخر ساعة.'],
            ],
            'history_days' => 21,
        ]);

        // 4) Aramex بنها — Free tier (so we have a free-tier example)
        $this->upsertBusiness([
            'type'         => $type,
            'plan'         => $free,
            'owner'        => ['aramex-banha@banhawy.local', 'فرع أرامكس · بنها', '01066778899'],
            'slug'         => 'aramex-banha',
            'name'         => 'Aramex بنها',
            'category'     => 'شحن دولي ومحلي',
            'description'  => 'فرع أرامكس في بنها — شحن داخلي ودولي.',
            'whatsapp'     => '01066778899',
            'phone'        => '01066778899',
            'address'      => 'مدخل بنها الرئيسي',
            'lat'          => 30.4720, 'lng' => 31.1761,
            'delivery'     => true,
            'hours'        => $hoursMornNight,
            'rating'       => 4.6,
            'reviews_count'=> 142,
            'views_count'  => 540,
            'wa_clicks'    => 88,
            'reviews'      => [
                ['أيمن س.',   5, 'موثوقين جداً للشحن الدولي.'],
                ['داليا ر.',  4, 'الأسعار أعلى من المحليين، لكن الجودة عالية.'],
            ],
            'history_days' => 14,
        ]);
    }

    private function seedServices(BusinessType $type, ?Plan $free, ?Plan $pro, ?Plan $business): void
    {
        $hours = collect(range(0, 6))->mapWithKeys(fn ($d) => [
            $d => $d === 5 // Friday off
                ? ['open' => '00:00', 'close' => '00:00', 'closed' => true]
                : ['open' => '09:00', 'close' => '21:00', 'closed' => false],
        ])->all();

        // 1) سباك الحاج محمود — Pro
        $this->upsertBusiness([
            'type'         => $type,
            'plan'         => $pro,
            'owner'        => ['plumber-mahmoud@banhawy.local', 'الحاج محمود السباك', '01115552201'],
            'slug'         => 'plumber-hag-mahmoud',
            'name'         => 'سباك الحاج محمود',
            'category'     => 'سباكة · صيانة مواسير · سخانات',
            'description'  => 'خبرة 25 سنة في سباكة المنازل والمحلات. خدمة 7 أيام في الأسبوع.',
            'whatsapp'     => '01115552201',
            'phone'        => '01115552201',
            'address'      => 'حدائق بنها',
            'lat'          => 30.4612, 'lng' => 31.1810,
            'is_verified'  => true,
            'is_featured'  => true,
            'hours'        => $hours,
            'rating'       => 4.8,
            'reviews_count'=> 67,
            'views_count'  => 1140,
            'wa_clicks'    => 248,
            'reviews'      => [
                ['عبد الرحمن ل.', 5, 'صنايعي محترم وأمين. صلّحلي مواسير في نص ساعة.'],
                ['ندى ح.',       5, 'بييجي في الميعاد وبيشتغل نضيف.'],
                ['كريم ع.',      5, 'الحاج محمود ثقة — أنصح بيه بقوة.'],
                ['عمرو خ.',      4, 'أحياناً مشغول، لكن الشغل بيستاهل الاستنى.'],
            ],
            'history_days' => 30,
        ]);

        // 2) كهربائي عم سعيد — Pro
        $this->upsertBusiness([
            'type'         => $type,
            'plan'         => $pro,
            'owner'        => ['electrician-saeed@banhawy.local', 'العم سعيد الكهربائي', '01007770033'],
            'slug'         => 'electrician-am-saeed',
            'name'         => 'كهربائي العم سعيد',
            'category'     => 'كهرباء · تمديدات · صيانة',
            'description'  => 'تمديدات كهربائية ومتابعة دورية للمنازل والمحلات.',
            'whatsapp'     => '01007770033',
            'phone'        => '01007770033',
            'address'      => 'بنها · شارع ٦',
            'lat'          => 30.4598, 'lng' => 31.1865,
            'is_verified'  => true,
            'hours'        => $hours,
            'rating'       => 4.7,
            'reviews_count'=> 52,
            'views_count'  => 880,
            'wa_clicks'    => 176,
            'reviews'      => [
                ['شريف ف.', 5, 'حلّ مشكلة عداد الكهرباء عندي في 10 دقايق.'],
                ['ميرا ج.', 4, 'الأسعار أحياناً مرتفعة، بس الجودة ممتازة.'],
                ['عماد ت.', 5, 'محترم وعنده ضمان على شغله.'],
            ],
            'history_days' => 30,
        ]);

        // 3) فني تكييف كريم — Business tier
        $this->upsertBusiness([
            'type'         => $type,
            'plan'         => $business,
            'owner'        => ['ac-karim@banhawy.local', 'كريم فني التكييف', '01556668800'],
            'slug'         => 'ac-tech-karim',
            'name'         => 'فني تكييف كريم',
            'category'     => 'تركيب وصيانة تكييفات · شحن فريون',
            'description'  => 'تركيب وصيانة كل أنواع التكييفات — Sharp, Carrier, LG، وأكتر.',
            'whatsapp'     => '01556668800',
            'phone'        => '01556668800',
            'address'      => 'العبور · بنها',
            'lat'          => 30.4641, 'lng' => 31.1798,
            'is_verified'  => true,
            'is_featured'  => true,
            'hours'        => $hours,
            'rating'       => 4.9,
            'reviews_count'=> 96,
            'views_count'  => 1450,
            'wa_clicks'    => 340,
            'reviews'      => [
                ['نسرين م.', 5, 'ركّبلي تكييف في نص ساعة، نضيف ومحترم.'],
                ['مازن ق.',  5, 'سعر مناسب وضمان سنة على التركيب.'],
                ['ياسر و.',  5, 'كريم محترم جداً، رشّحته لكل أصحابي.'],
                ['دينا ر.',  4, 'بييجي في الميعاد دايماً.'],
            ],
            'history_days' => 30,
        ]);

        // 4) نجار أبو حسن — Free
        $this->upsertBusiness([
            'type'         => $type,
            'plan'         => $free,
            'owner'        => ['carpenter-abu-hassan@banhawy.local', 'أبو حسن النجار', '01223334455'],
            'slug'         => 'carpenter-abu-hassan',
            'name'         => 'نجار أبو حسن',
            'category'     => 'موبيليا · أبواب · إصلاح أثاث',
            'description'  => 'صناعة وإصلاح كل أنواع الموبيليا — مكتب جوه بنها.',
            'whatsapp'     => '01223334455',
            'phone'        => '01223334455',
            'address'      => 'بنها · شارع المحطة',
            'lat'          => 30.4577, 'lng' => 31.1842,
            'hours'        => $hours,
            'rating'       => 4.5,
            'reviews_count'=> 28,
            'views_count'  => 320,
            'wa_clicks'    => 54,
            'reviews'      => [
                ['ماهر ز.',  5, 'أبو حسن صنايعي قديم في بنها — شغله متين.'],
                ['أمل خ.',   4, 'الأسعار مناسبة.'],
            ],
            'history_days' => 14,
        ]);

        // 5) دهانات ورق حائط — Free
        $this->upsertBusiness([
            'type'         => $type,
            'plan'         => $free,
            'owner'        => ['painter@banhawy.local', 'ورشة الدهانات', '01444556677'],
            'slug'         => 'wall-paint-shop',
            'name'         => 'دهان ورق حائط',
            'category'     => 'دهانات · ورق حائط · ديكور',
            'description'  => 'دهان منازل ومحلات بأحدث الألوان · تركيب ورق حائط أوروبي.',
            'whatsapp'     => '01444556677',
            'phone'        => '01444556677',
            'address'      => 'بنها',
            'lat'          => 30.4684, 'lng' => 31.1820,
            'hours'        => $hours,
            'rating'       => 4.4,
            'reviews_count'=> 19,
            'views_count'  => 240,
            'wa_clicks'    => 42,
            'reviews'      => [
                ['نيفين ع.', 5, 'فريق متعاون · جابوا ألوان بالظبط زي ما طلبت.'],
                ['شيماء ت.', 4, 'الشغل تمام، بس استغرق يوم زيادة عن الاتفاق.'],
            ],
            'history_days' => 14,
        ]);

        // 6) شركة تكنولوجيا (مثال خدمة غير تقليدية) — Pro
        $this->upsertBusiness([
            'type'         => $type,
            'plan'         => $pro,
            'owner'        => ['tech-banha@banhawy.local', 'فاطمة عبد الله', '01099887766'],
            'slug'         => 'banha-tech-support',
            'name'         => 'بنها للدعم الفني',
            'category'     => 'صيانة كمبيوتر · مواقع · شبكات',
            'description'  => 'حلول تكنولوجية للشركات الصغيرة في بنها — صيانة، مواقع، وشبكات WiFi.',
            'whatsapp'     => '01099887766',
            'phone'        => '01099887766',
            'address'      => 'بنها مركز',
            'lat'          => 30.4612, 'lng' => 31.1798,
            'is_verified'  => true,
            'hours'        => $hours,
            'rating'       => 4.6,
            'reviews_count'=> 24,
            'views_count'  => 470,
            'wa_clicks'    => 102,
            'reviews'      => [
                ['مايا ت.', 5, 'صلّحوا لي شبكة WiFi المحل في ساعة.'],
                ['طارق ج.', 4, 'محترفين، أسعارهم تنافسية.'],
            ],
            'history_days' => 21,
        ]);
    }

    /**
     * Create a business + its owner + subscription + reviews + analytics history.
     */
    private function upsertBusiness(array $cfg): void
    {
        [$email, $ownerName, $ownerPhone] = $cfg['owner'];

        $owner = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => $ownerName,
                'phone'    => $ownerPhone,
                'role'     => 'owner',
                'password' => Hash::make('password'),
            ]
        );

        $plan = $cfg['plan'];

        $business = Business::updateOrCreate(
            ['slug' => $cfg['slug']],
            [
                'owner_id'         => $owner->id,
                'business_type_id' => $cfg['type']->id,
                'plan_id'          => $plan?->id,
                'name'             => $cfg['name'],
                'category'         => $cfg['category'],
                'description'      => $cfg['description'],
                'whatsapp'         => $cfg['whatsapp'],
                'phone'            => $cfg['phone'] ?? $cfg['whatsapp'],
                'address'          => $cfg['address'],
                'lat'              => $cfg['lat'],
                'lng'              => $cfg['lng'],
                'price_range'      => $cfg['price_range'] ?? 'medium',
                'delivery'         => $cfg['delivery'] ?? false,
                'orders_via'       => 'whatsapp',
                'bookings_via'     => 'whatsapp',
                'is_active'        => true,
                'is_verified'      => $cfg['is_verified'] ?? false,
                'is_featured'      => $cfg['is_featured'] ?? false,
                'hours'            => $cfg['hours'],
                'rating'           => $cfg['rating'] ?? 0,
                'reviews_count'    => $cfg['reviews_count'] ?? 0,
                'views_count'      => $cfg['views_count'] ?? 0,
                'whatsapp_clicks'  => $cfg['wa_clicks'] ?? 0,
                'setup_progress'   => 100,
            ]
        );

        // Subscription record for paid plans
        if ($plan && (int) $plan->price_monthly > 0) {
            Subscription::updateOrCreate(
                ['business_id' => $business->id],
                [
                    'plan_id'   => $plan->id,
                    'status'    => 'active',
                    'starts_at' => now()->subDays(45)->toDateString(),
                    'ends_at'   => now()->addDays(15)->toDateString(),
                    'amount'    => $plan->price_monthly,
                ]
            );
        }

        // Reviews
        foreach ($cfg['reviews'] ?? [] as [$name, $rating, $body]) {
            Review::updateOrCreate(
                ['business_id' => $business->id, 'reviewer_name' => $name, 'body' => $body],
                ['rating' => $rating]
            );
        }

        // Analytics history (only for businesses with non-trivial history)
        $days = $cfg['history_days'] ?? 0;
        if ($days > 0) {
            // Skip if we already seeded for this business
            $alreadyHas = BusinessView::where('business_id', $business->id)->exists();
            if (! $alreadyHas) {
                $this->seedAnalytics($business->id, $days);
            }
        }
    }

    private function seedAnalytics(int $businessId, int $days): void
    {
        for ($d = $days; $d >= 0; $d--) {
            $views = rand(8, 30);
            for ($i = 0; $i < $views; $i++) {
                BusinessView::create([
                    'business_id' => $businessId,
                    'ip_hash'     => hash('sha256', 'seed-' . $businessId . '-' . $d . '-' . $i),
                    'viewed_at'   => now()->subDays($d)->subMinutes(rand(0, 1440)),
                ]);
            }
            $clicks = rand(1, 10);
            for ($i = 0; $i < $clicks; $i++) {
                WhatsappClick::create([
                    'business_id' => $businessId,
                    'source'      => collect(['profile', 'list', 'map'])->random(),
                    'clicked_at'  => now()->subDays($d)->subMinutes(rand(0, 1440)),
                ]);
            }
        }
    }
}
