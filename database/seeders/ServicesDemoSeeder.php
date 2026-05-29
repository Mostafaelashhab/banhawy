<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessType;
use App\Models\LostItem;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ServicesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $shipping = BusinessType::where('slug', 'shipping')->first();
        $service  = BusinessType::where('slug', 'service')->first();
        if (! $shipping || ! $service) return;

        $owner = User::firstOrCreate(
            ['email' => 'demo@banhawy.local'],
            [
                'name'     => 'Banhawy Demo',
                'role'     => 'owner',
                'phone'    => '01000000001',
                'password' => Hash::make(Str::random(20)),
            ]
        );

        $defaultHours = collect(range(0, 6))->mapWithKeys(fn ($d) => [
            $d => ['open' => '09:00', 'close' => '22:00', 'closed' => false],
        ])->all();

        // ── Shipping companies ──────────────────────────────────────
        $shippingCos = [
            ['name' => 'Mostafa Express', 'cat' => 'شحن داخل بنها وحولها',  'address' => 'شارع طه الحكيم · بنها', 'lat' => 30.4592, 'lng' => 31.1850],
            ['name' => 'SpeedBox',        'cat' => 'شحن سريع 24 ساعة',     'address' => 'كوبري النحاس · بنها',   'lat' => 30.4631, 'lng' => 31.1839],
            ['name' => 'بنها كارجو',      'cat' => 'بضائع وأثاث',           'address' => 'شارع كوبري الفحص',     'lat' => 30.4655, 'lng' => 31.1882],
            ['name' => 'Aramex بنها',     'cat' => 'شحن دولي ومحلي',         'address' => 'مدخل بنها الرئيسي',     'lat' => 30.4720, 'lng' => 31.1761],
        ];
        foreach ($shippingCos as $i => $c) {
            Business::updateOrCreate(
                ['slug' => Str::slug($c['name']) . '-demo-' . $i],
                [
                    'owner_id'         => $owner->id,
                    'business_type_id' => $shipping->id,
                    'name'             => $c['name'],
                    'category'         => $c['cat'],
                    'whatsapp'         => '0120000000' . $i,
                    'phone'            => '0120000000' . $i,
                    'address'          => $c['address'],
                    'lat'              => $c['lat'],
                    'lng'              => $c['lng'],
                    'price_range'      => 'medium',
                    'delivery'         => true,
                    'orders_via'       => 'whatsapp',
                    'bookings_via'     => 'whatsapp',
                    'is_active'        => true,
                    'is_verified'      => $i < 2,
                    'is_featured'      => $i === 0,
                    'hours'            => $defaultHours,
                    'rating'           => 4.2 + ($i * 0.15),
                    'reviews_count'    => 10 + ($i * 7),
                ]
            );
        }

        // ── Service providers (handymen) ────────────────────────────
        $providers = [
            ['name' => 'سباك حاج محمود',  'cat' => 'سباكة وصيانة مواسير',   'address' => 'حدائق بنها', 'lat' => 30.4612, 'lng' => 31.1810],
            ['name' => 'كهربائي عم سعيد', 'cat' => 'كهرباء وتمديدات',       'address' => 'بنها · شارع ٦', 'lat' => 30.4598, 'lng' => 31.1865],
            ['name' => 'فني تكييف كريم',   'cat' => 'صيانة وتركيب تكييفات',  'address' => 'العبور · بنها', 'lat' => 30.4641, 'lng' => 31.1798],
            ['name' => 'نجار أبو حسن',     'cat' => 'موبيليا وأبواب',         'address' => 'بنها', 'lat' => 30.4577, 'lng' => 31.1842],
            ['name' => 'دهان ورق حائط',    'cat' => 'دهانات وديكور',          'address' => 'بنها', 'lat' => 30.4684, 'lng' => 31.1820],
        ];
        foreach ($providers as $i => $p) {
            Business::updateOrCreate(
                ['slug' => Str::slug($p['name'], '_') . '-srv-' . $i],
                [
                    'owner_id'         => $owner->id,
                    'business_type_id' => $service->id,
                    'name'             => $p['name'],
                    'category'         => $p['cat'],
                    'whatsapp'         => '0110000000' . $i,
                    'phone'            => '0110000000' . $i,
                    'address'          => $p['address'],
                    'lat'              => $p['lat'],
                    'lng'              => $p['lng'],
                    'price_range'      => 'low',
                    'delivery'         => false,
                    'orders_via'       => 'whatsapp',
                    'bookings_via'     => 'whatsapp',
                    'is_active'        => true,
                    'is_verified'      => $i < 3,
                    'is_featured'      => $i === 0,
                    'hours'            => $defaultHours,
                    'rating'           => 4.0 + ($i * 0.18),
                    'reviews_count'    => 5 + ($i * 4),
                ]
            );
        }

        // ── Tasks ───────────────────────────────────────────────────
        $tasks = [
            ['title' => 'محتاج حد ينضّفلي شقة الجمعة',  'cat' => 'cleaning', 'desc' => 'شقة 120 متر · 3 غرف · معايا الأدوات. الجمعة الصبح.', 'loc' => 'حدائق بنها', 'budget' => 250, 'urgency' => 'normal'],
            ['title' => 'فني تكييف يصلح ضوضاء',          'cat' => 'repair',   'desc' => 'تكييف شارب صوته عالي بدأ من أسبوع — يصلحه قبل الأحد.',     'loc' => 'بنها مركز', 'budget' => 300, 'urgency' => 'urgent'],
            ['title' => 'نقل أثاث من بنها للقاهرة',     'cat' => 'moving',   'desc' => '6 قطع كبيرة · شقة دور أرضي → دور 4 بمصعد.',                'loc' => 'بنها → القاهرة', 'budget' => 1200, 'urgency' => 'low'],
            ['title' => 'مدرس رياضيات للصف 3 ثانوي',    'cat' => 'tutoring', 'desc' => '4 حصص أسبوعياً · مدرس متمكن في التفاضل والتكامل.',          'loc' => 'العبور', 'budget' => null, 'urgency' => 'normal'],
        ];
        foreach ($tasks as $i => $t) {
            Task::updateOrCreate(
                ['title' => $t['title']],
                [
                    'user_id'        => null,
                    'category'       => $t['cat'],
                    'description'    => $t['desc'],
                    'location'       => $t['loc'],
                    'budget'         => $t['budget'],
                    'urgency'        => 'low',           // demo data — keep low so it's not eye-catching
                    'contact_name'   => 'بيانات تجريبية',
                    'contact_phone'  => '00000000000',   // dummy number — never reachable
                    // Mark all seeded tasks as completed so no one tries to contact a demo number
                    'status'         => 'completed',
                    'closed_at'      => now()->subDays($i + 1),
                ]
            );
        }

        // ── Lost & Found ────────────────────────────────────────────
        $lost = [
            ['kind' => 'lost',  'title' => 'محفظة سوداء جلد فيها كارنيه', 'cat' => 'wallet',      'desc' => 'محفظة جلد سوداء فيها كارنيه قسم بنها + بطاقة. ضاعت أمس عند موقف بنها.', 'loc' => 'موقف بنها', 'reward' => 200],
            ['kind' => 'lost',  'title' => 'موبايل سامسونج A54 أزرق',      'cat' => 'electronics','desc' => 'وقع مني في الميكروباص خط بنها/القاهرة. الباتري كانت ٧٠٪.',                 'loc' => 'محطة قطار بنها', 'reward' => 500],
            ['kind' => 'found', 'title' => 'مفتاح عربية كيا',             'cat' => 'keys',       'desc' => 'لقيته جنب جامع الصحوة. مفتاح عربية كيا فيه ميدالية حمرا.',                'loc' => 'جامع الصحوة', 'reward' => null],
            ['kind' => 'found', 'title' => 'قطة بيضا · إناث',              'cat' => 'pet',        'desc' => 'قطة بيضا أليفة لقيناها قدام المنزل · بتاكل وبتشرب. ادعِ صاحبها.',           'loc' => 'حدائق بنها', 'reward' => null],
        ];
        foreach ($lost as $i => $l) {
            LostItem::updateOrCreate(
                ['title' => $l['title']],
                [
                    'user_id'       => null,
                    'kind'          => $l['kind'],
                    'category'      => $l['cat'],
                    'description'   => $l['desc'],
                    'location'      => $l['loc'],
                    'happened_on'   => now()->subDays($i + 1)->toDateString(),
                    'reward'        => $l['reward'],
                    'contact_name'  => 'بيانات تجريبية',
                    'contact_phone' => '00000000000',     // dummy — never reachable
                    // Mark all seeded lost items as resolved so no one calls demo numbers
                    'status'        => 'resolved',
                    'resolved_at'   => now()->subDays($i + 1),
                ]
            );
        }
    }
}
