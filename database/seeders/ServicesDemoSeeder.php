<?php

namespace Database\Seeders;

use App\Models\LostItem;
use App\Models\Task;
use Illuminate\Database\Seeder;

/**
 * Community demo data: tasks board + lost & found.
 * Businesses are now seeded by BusinessSeeder — keep this strictly to user-
 * generated content so we don't duplicate the canonical business list.
 *
 * Everything here is marked as `completed`/`resolved` so the boards look
 * populated without anyone trying to call the dummy phone numbers.
 */
class ServicesDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Tasks (always marked completed) ─────────────────────────
        $tasks = [
            ['title' => 'محتاج حد ينضّفلي شقة الجمعة',  'cat' => 'cleaning', 'desc' => 'شقة 120 متر · 3 غرف · معايا الأدوات. الجمعة الصبح.', 'loc' => 'حدائق بنها',       'budget' => 250],
            ['title' => 'فني تكييف يصلح ضوضاء',          'cat' => 'repair',   'desc' => 'تكييف شارب صوته عالي بدأ من أسبوع — يصلحه قبل الأحد.',     'loc' => 'بنها مركز',         'budget' => 300],
            ['title' => 'نقل أثاث من بنها للقاهرة',     'cat' => 'moving',   'desc' => '6 قطع كبيرة · شقة دور أرضي → دور 4 بمصعد.',                'loc' => 'بنها → القاهرة', 'budget' => 1200],
            ['title' => 'مدرس رياضيات للصف 3 ثانوي',    'cat' => 'tutoring', 'desc' => '4 حصص أسبوعياً · مدرس متمكن في التفاضل والتكامل.',          'loc' => 'العبور',             'budget' => null],
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
                    'urgency'        => 'low',
                    'contact_name'   => 'بيانات تجريبية',
                    'contact_phone'  => '00000000000',
                    'status'         => 'completed',
                    'closed_at'      => now()->subDays($i + 1),
                ]
            );
        }

        // ── Lost & found (always marked resolved) ───────────────────
        $lost = [
            ['kind' => 'lost',  'title' => 'محفظة سوداء جلد فيها كارنيه', 'cat' => 'wallet',      'desc' => 'محفظة جلد سوداء فيها كارنيه قسم بنها + بطاقة. ضاعت أمس عند موقف بنها.', 'loc' => 'موقف بنها',     'reward' => 200],
            ['kind' => 'lost',  'title' => 'موبايل سامسونج A54 أزرق',      'cat' => 'electronics','desc' => 'وقع مني في الميكروباص خط بنها/القاهرة. الباتري كانت ٧٠٪.',                 'loc' => 'محطة قطار بنها', 'reward' => 500],
            ['kind' => 'found', 'title' => 'مفتاح عربية كيا',             'cat' => 'keys',       'desc' => 'لقيته جنب جامع الصحوة. مفتاح عربية كيا فيه ميدالية حمرا.',                'loc' => 'جامع الصحوة',   'reward' => null],
            ['kind' => 'found', 'title' => 'قطة بيضا · إناث',              'cat' => 'pet',        'desc' => 'قطة بيضا أليفة لقيناها قدام المنزل · بتاكل وبتشرب. ادعِ صاحبها.',           'loc' => 'حدائق بنها',     'reward' => null],
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
                    'contact_phone' => '00000000000',
                    'status'        => 'resolved',
                    'resolved_at'   => now()->subDays($i + 1),
                ]
            );
        }
    }
}
