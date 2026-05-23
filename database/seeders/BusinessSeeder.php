<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Business;
use App\Models\BusinessType;
use App\Models\BusinessView;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WhatsappClick;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        // Default hours: 12:00–01:00 every day (restaurants); adjust per-business if needed
        $defaultHours = collect(range(0, 6))->mapWithKeys(fn ($d) => [
            $d => ['open' => '12:00', 'close' => '23:59', 'closed' => false],
        ])->all();

        // ── Business 1: Pizza Zone — the showcase example ─────────────
        $owner1 = User::firstOrCreate(
            ['email' => 'pizzazone@banhawy.local'],
            [
                'name'     => 'محمد عبد الرحمن',
                'phone'    => '+201005558899',
                'role'     => 'owner',
                'password' => Hash::make('password'),
            ]
        );

        $pizza = Business::updateOrCreate(
            ['slug' => 'pizza-zone'],
            [
                'owner_id'         => $owner1->id,
                'business_type_id' => BusinessType::where('slug', 'restaurant')->value('id'),
                'plan_id'          => Plan::where('slug', 'pro')->value('id'),
                'name'             => 'Pizza Zone',
                'category'         => 'مطعم بيتزا إيطالي',
                'description'      => 'أشهى البيتزا الإيطالية بأيدي طهاة محترفين — نوصلك في بنها خلال 30 دقيقة.',
                'whatsapp'         => '+201005558899',
                'phone'            => '+20133227700',
                'email'            => 'orders@pizzazone.example',
                'address'          => 'شارع فريد ندا، بنها، القليوبية',
                'lat'              => 30.4612,
                'lng'              => 31.1820,
                'price_range'      => 'medium',
                'delivery'         => true,
                'orders_via'       => 'both',
                'bookings_via'     => 'both',
                'is_active'        => true,
                'is_verified'      => true,
                'is_featured'      => true,
                'hours'            => $defaultHours,
                'rating'           => 4.7,
                'reviews_count'    => 218,
                'views_count'      => 1248,
                'whatsapp_clicks'  => 342,
                'setup_progress'   => 75,
            ]
        );

        Subscription::updateOrCreate(
            ['business_id' => $pizza->id, 'status' => 'active'],
            [
                'plan_id'   => $pizza->plan_id,
                'starts_at' => now()->subDays(40)->toDateString(),
                'ends_at'   => now()->addDays(20)->toDateString(),
                'amount'    => 399,
            ]
        );

        // Pizza categories + products
        $catPizza   = ProductCategory::firstOrCreate(['business_id' => $pizza->id, 'name' => 'بيتزا'],       ['sort' => 1]);
        $catBurger  = ProductCategory::firstOrCreate(['business_id' => $pizza->id, 'name' => 'برجر'],        ['sort' => 2]);
        $catDrinks  = ProductCategory::firstOrCreate(['business_id' => $pizza->id, 'name' => 'مشروبات'],     ['sort' => 3]);
        $catOffers  = ProductCategory::firstOrCreate(['business_id' => $pizza->id, 'name' => 'عروض'],        ['sort' => 4]);

        $items = [
            [$catPizza,  'بيتزا مارجريتا',    'جبنة موتزاريلا · صلصة طماطم · ريحان',  95,  true,  1],
            [$catPizza,  'بيتزا بيبروني',     'جبنة · بيبروني حار · زيتون',           120, false, 2],
            [$catPizza,  'بيتزا فور تشيز',    'أربع أنواع جبنة فاخرة',                140, false, 3],
            [$catPizza,  'بيتزا فاهيتا',      'دجاج فاهيتا · فلفل ألوان',              135, false, 4],
            [$catBurger, 'تشيز برجر دبل',    'لحم بقري × 2 · جبنة شيدر',              110, false, 1],
            [$catDrinks, 'بيبسي 2 لتر',      'مشروب غازي بارد',                       25,  false, 1],
            [$catDrinks, 'مياه معدنية',      'مياه نقية مبردة',                        10,  false, 2],
            [$catOffers, 'كومبو العائلة',    '2 بيتزا كبيرة + بيبسي 2ل + سلطة',        250, true,  1],
        ];

        $productIds = [];
        foreach ($items as $i => [$cat, $name, $desc, $price, $featured, $sort]) {
            $p = Product::updateOrCreate(
                ['business_id' => $pizza->id, 'name' => $name],
                [
                    'product_category_id' => $cat->id,
                    'description'         => $desc,
                    'price'               => $price,
                    'is_available'        => true,
                    'is_featured'         => $featured,
                    'sort'                => $sort,
                ]
            );
            $productIds[$name] = $p->id;
        }

        // Sample orders (3 new, 1 preparing, 2 completed)
        $orders = [
            [
                'customer' => ['محمد إبراهيم', '+201001234567'],
                'items'    => [
                    ['name' => 'بيتزا مارجريتا', 'qty' => 1, 'price' => 95],
                    ['name' => 'بيتزا بيبروني',  'qty' => 2, 'price' => 120],
                    ['name' => 'بيبسي 2 لتر',    'qty' => 1, 'price' => 25],
                ],
                'status'  => 'new',
                'minutes' => 4,
                'delivery'=> 15,
            ],
            [
                'customer' => ['سلمى محمود', '+201229984421'],
                'items'    => [
                    ['name' => 'بيتزا فور تشيز', 'qty' => 1, 'price' => 140],
                    ['name' => 'مياه معدنية',    'qty' => 1, 'price' => 10],
                ],
                'status'  => 'new',
                'minutes' => 12,
                'delivery'=> 15,
            ],
            [
                'customer' => ['أحمد كمال', '+201111457721'],
                'items'    => [
                    ['name' => 'بيتزا مارجريتا', 'qty' => 2, 'price' => 95],
                    ['name' => 'بيبسي 2 لتر',    'qty' => 1, 'price' => 25],
                ],
                'status'  => 'preparing',
                'minutes' => 22,
                'delivery'=> 15,
            ],
            [
                'customer' => ['عمر حسن', '+201503348822'],
                'items'    => [
                    ['name' => 'كومبو العائلة', 'qty' => 1, 'price' => 250],
                ],
                'status'  => 'completed',
                'minutes' => 95,
                'delivery'=> 15,
            ],
        ];

        foreach ($orders as $o) {
            $itemsLines = collect($o['items'])->map(fn ($it) => array_merge($it, [
                'product_id' => $productIds[$it['name']] ?? null,
                'line_total' => $it['qty'] * $it['price'],
            ]))->all();

            $subtotal = collect($itemsLines)->sum('line_total');

            Order::create([
                'business_id'      => $pizza->id,
                'customer_name'    => $o['customer'][0],
                'customer_phone'   => $o['customer'][1],
                'subtotal'         => $subtotal,
                'delivery_fee'     => $o['delivery'],
                'total'            => $subtotal + $o['delivery'],
                'status'           => $o['status'],
                'items'            => $itemsLines,
                'placed_at'        => now()->subMinutes($o['minutes']),
            ]);
        }

        // Sample bookings — today
        $bookings = [
            ['حسام عثمان', '+201007811200', '12:30', 4, 'confirmed', 'إفطار عمل'],
            ['منى رفعت',   '+201226554477', '14:00', 2, 'new',       'غداء'],
            ['عائلة الزهراء', '+201113344551', '16:30', 6, 'confirmed', 'حفلة عيد ميلاد'],
            ['محمد طارق',   '+201557788991', '19:00', 2, 'cancelled', null],
            ['أمنية سامي',  '+201001122334', '20:30', 3, 'new',       'عشاء عائلي'],
        ];

        foreach ($bookings as [$name, $phone, $time, $party, $status, $service]) {
            Booking::create([
                'business_id'    => $pizza->id,
                'customer_name'  => $name,
                'customer_phone' => $phone,
                'service'        => $service,
                'booked_at'      => now()->setTimeFromTimeString($time),
                'party_size'     => $party,
                'status'         => $status,
            ]);
        }

        // Reviews
        $reviews = [
            ['محمود ع.', 5, 'أحسن بيتزا في بنها — العجينة طازة والتوصيل سريع جدًا.'],
            ['نهى م.',   5, 'الفور تشيز خيالية — والأسعار معقولة.'],
            ['وائل ف.',  4, 'الطعم ممتاز، بس التوصيل اتأخر شوية.'],
        ];
        foreach ($reviews as [$n, $r, $b]) {
            Review::create([
                'business_id'   => $pizza->id,
                'reviewer_name' => $n,
                'rating'        => $r,
                'body'          => $b,
            ]);
        }

        // Analytics history (last 30 days)
        for ($d = 30; $d >= 0; $d--) {
            $views = rand(20, 60);
            for ($i = 0; $i < $views; $i++) {
                BusinessView::create([
                    'business_id' => $pizza->id,
                    'viewed_at'   => now()->subDays($d)->subMinutes(rand(0, 1440)),
                ]);
            }
            $clicks = rand(4, 18);
            for ($i = 0; $i < $clicks; $i++) {
                WhatsappClick::create([
                    'business_id' => $pizza->id,
                    'source'      => collect(['profile', 'menu', 'order'])->random(),
                    'clicked_at'  => now()->subDays($d)->subMinutes(rand(0, 1440)),
                ]);
            }
        }

        // ── Business 2: مطعم المختار ────────────────────────────────
        $owner2 = User::firstOrCreate(
            ['email' => 'mokhtar@banhawy.local'],
            ['name' => 'سامي المختار', 'phone' => '+201228887766', 'role' => 'owner', 'password' => Hash::make('password')]
        );
        Business::updateOrCreate(['slug' => 'al-mokhtar'], [
            'owner_id'         => $owner2->id,
            'business_type_id' => BusinessType::where('slug', 'restaurant')->value('id'),
            'plan_id'          => Plan::where('slug', 'starter')->value('id'),
            'name'             => 'مطعم المختار',
            'category'         => 'مأكولات شعبية',
            'description'      => 'أكلات بيتي على الأصول.',
            'whatsapp'         => '+201228887766',
            'address'          => 'شارع الجلاء، بنها',
            'lat'              => 30.4595,
            'lng'              => 31.1832,
            'price_range'      => 'low',
            'delivery'         => true,
            'orders_via'       => 'whatsapp',
            'bookings_via'     => 'whatsapp',
            'is_active'        => true,
            'is_verified'      => true,
            'hours'            => $defaultHours,
            'rating'           => 4.5,
            'reviews_count'    => 142,
        ]);

        // ── Business 3: Brew Bar ─────────────────────────────────
        $owner3 = User::firstOrCreate(
            ['email' => 'brewbar@banhawy.local'],
            ['name' => 'كريم عمرو', 'phone' => '+201005112233', 'role' => 'owner', 'password' => Hash::make('password')]
        );
        Business::updateOrCreate(['slug' => 'brew-bar'], [
            'owner_id'         => $owner3->id,
            'business_type_id' => BusinessType::where('slug', 'restaurant')->value('id'),
            'plan_id'          => Plan::where('slug', 'pro')->value('id'),
            'name'             => 'Brew Bar',
            'category'         => 'كافيه متخصص',
            'description'      => 'قهوة سبشيالتي وحلويات يدوية.',
            'whatsapp'         => '+201005112233',
            'address'          => 'كورنيش بنها',
            'lat'              => 30.4540,
            'lng'              => 31.1770,
            'price_range'      => 'medium',
            'delivery'         => false,
            'orders_via'       => 'both',
            'bookings_via'     => 'web',
            'is_active'        => true,
            'is_verified'      => true,
            'is_featured'      => true,
            'hours'            => collect(range(0, 6))->mapWithKeys(fn ($d) => [
                $d => ['open' => '08:00', 'close' => '23:00', 'closed' => false],
            ])->all(),
            'rating'           => 4.6,
            'reviews_count'    => 98,
        ]);

        // ── Business 4: عيادة د. هبة سامي ─────────────────────────
        $owner4 = User::firstOrCreate(
            ['email' => 'dr.heba@banhawy.local'],
            ['name' => 'د. هبة سامي', 'phone' => '+201007654321', 'role' => 'owner', 'password' => Hash::make('password')]
        );
        Business::updateOrCreate(['slug' => 'dr-heba-sami'], [
            'owner_id'         => $owner4->id,
            'business_type_id' => BusinessType::where('slug', 'clinic')->value('id'),
            'plan_id'          => Plan::where('slug', 'starter')->value('id'),
            'name'             => 'د. هبة سامي · أطفال',
            'category'         => 'استشاري أطفال وحديثي ولادة',
            'description'      => 'كشف وحجز مواعيد.',
            'whatsapp'         => '+201007654321',
            'address'          => 'برج النيل، بنها',
            'lat'              => 30.4620,
            'lng'              => 31.1755,
            'price_range'      => 'medium',
            'delivery'         => false,
            'orders_via'       => 'whatsapp', // clinics don't take "orders"
            'bookings_via'     => 'both',     // bookings via dashboard + WA
            'is_active'        => true,
            'is_verified'      => true,
            'hours'            => collect(range(0, 6))->mapWithKeys(fn ($d) => [
                $d => $d === 5 // Friday closed
                    ? ['open' => '00:00', 'close' => '00:00', 'closed' => true]
                    : ['open' => '16:00', 'close' => '22:00', 'closed' => false],
            ])->all(),
            'rating'           => 4.9,
            'reviews_count'    => 56,
        ]);

        // ── Business 5: صالون لارا ────────────────────────────────
        $owner5 = User::firstOrCreate(
            ['email' => 'lara@banhawy.local'],
            ['name' => 'لارا فؤاد', 'phone' => '+201111223344', 'role' => 'owner', 'password' => Hash::make('password')]
        );
        Business::updateOrCreate(['slug' => 'lara-salon'], [
            'owner_id'         => $owner5->id,
            'business_type_id' => BusinessType::where('slug', 'salon')->value('id'),
            'plan_id'          => Plan::where('slug', 'pro')->value('id'),
            'name'             => 'صالون لارا',
            'category'         => 'تجميل وعناية',
            'description'      => 'كل خدمات الجمال في مكان واحد.',
            'whatsapp'         => '+201111223344',
            'address'          => 'مول النخيل، بنها',
            'lat'              => 30.4575,
            'lng'              => 31.1810,
            'price_range'      => 'medium',
            'delivery'         => false,
            'orders_via'       => 'whatsapp',
            'bookings_via'     => 'web',
            'is_active'        => true,
            'is_verified'      => true,
            'hours'            => collect(range(0, 6))->mapWithKeys(fn ($d) => [
                $d => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
            ])->all(),
            'rating'           => 4.8,
            'reviews_count'    => 74,
        ]);

        // ── Business 6: محل النور للأقمشة ─────────────────────────
        $owner6 = User::firstOrCreate(
            ['email' => 'alnour@banhawy.local'],
            ['name' => 'أحمد النور', 'phone' => '+201554433221', 'role' => 'owner', 'password' => Hash::make('password')]
        );
        Business::updateOrCreate(['slug' => 'al-nour-fabrics'], [
            'owner_id'         => $owner6->id,
            'business_type_id' => BusinessType::where('slug', 'shop')->value('id'),
            'plan_id'          => Plan::where('slug', 'starter')->value('id'),
            'name'             => 'محل النور للأقمشة',
            'category'         => 'أقمشة وستائر',
            'description'      => 'أقمشة فاخرة بأسعار الجملة.',
            'whatsapp'         => '+201554433221',
            'address'          => 'سوق بنها التجاري',
            'lat'              => 30.4630,
            'lng'              => 31.1795,
            'price_range'      => 'low',
            'delivery'         => false,
            'is_active'        => true,
            'is_verified'      => false,
            'hours'            => collect(range(0, 6))->mapWithKeys(fn ($d) => [
                $d => $d === 5
                    ? ['open' => '00:00', 'close' => '00:00', 'closed' => true]
                    : ['open' => '10:00', 'close' => '21:00', 'closed' => false],
            ])->all(),
            'rating'           => 4.3,
            'reviews_count'    => 32,
        ]);
    }
}
