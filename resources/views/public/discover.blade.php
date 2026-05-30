@extends('layouts.mobile')

@section('title', 'بنهاوي · خدمات بنها')
@section('page-title', 'بنهاوي')

@section('content')

{{-- ── HERO ─────────────────────────────────────────────────── --}}
<div class="disc-hero">
    <div class="disc-hero-inner">
        @auth
            <div class="disc-greet">أهلًا، {{ explode(' ', auth()->user()->name)[0] }} 👋</div>
        @else
            <div class="disc-greet">أهلًا بك في بنهاوي 👋</div>
        @endauth
        <h1 class="disc-headline">كل خدمات بنها في مكان واحد</h1>
        <p class="disc-sub">شحن · صنايعية · مهام · مفقودات — كل اللي تحتاجه على بُعد ضغطة.</p>

        <a href="{{ route('search') }}" class="disc-search">
            <x-icon name="search" :size="16" stroke="#5E6A77"/>
            <span>ابحث عن خدمة، شركة شحن، أو مهمة...</span>
        </a>
    </div>
</div>

{{-- ── QUICK SECTIONS GRID ──────────────────────────────────── --}}
<div style="padding: 12px 14px 0;">
    <div class="quick-grid">
        <a href="{{ route('shipping') }}" class="quick-tile" style="background: linear-gradient(135deg, rgba(13,148,136,.12), rgba(13,148,136,.02));">
            <span class="quick-tile-ico" style="background: rgba(13,148,136,.18); color: var(--teal);">
                <x-icon name="truck" :size="24" stroke="#0D9488"/>
            </span>
            <div class="quick-tile-title">شركات شحن</div>
            <div class="quick-tile-sub">داخلي وخارجي</div>
        </a>

        <a href="{{ route('services') }}" class="quick-tile" style="background: linear-gradient(135deg, rgba(14,165,233,.12), rgba(14,165,233,.02));">
            <span class="quick-tile-ico" style="background: rgba(14,165,233,.18); color: #0369A1;">
                <x-icon name="briefcase" :size="22" stroke="#0369A1"/>
            </span>
            <div class="quick-tile-title">خدمات</div>
            <div class="quick-tile-sub">صنايعية وحرفيين</div>
        </a>

        <a href="{{ route('tasks.index') }}" class="quick-tile" style="background: linear-gradient(135deg, rgba(245,158,11,.13), rgba(245,158,11,.02));">
            <span class="quick-tile-ico" style="background: rgba(245,158,11,.18); color: #B45309;">
                <x-icon name="task" :size="22" stroke="#B45309"/>
            </span>
            <div class="quick-tile-title">مهام</div>
            <div class="quick-tile-sub">انشر طلب · أو ساعد</div>
        </a>

        <a href="{{ route('lost.index') }}" class="quick-tile" style="background: linear-gradient(135deg, rgba(220,38,38,.10), rgba(220,38,38,.02));">
            <span class="quick-tile-ico" style="background: rgba(220,38,38,.14); color: #B91C1C;">
                <x-icon name="search-loc" :size="22" stroke="#B91C1C"/>
            </span>
            <div class="quick-tile-title">مفقودات</div>
            <div class="quick-tile-sub">ضايع · أو موجود</div>
        </a>
    </div>
</div>

{{-- ── SHIPPING SECTION ─────────────────────────────────────── --}}
@if($shipping->count() > 0)
    <div id="shipping" style="padding: 22px 14px 0; scroll-margin-top: 12px;">
        <div class="section-head">
            <div>
                <div class="section-title">شركات شحن في بنها</div>
                <div class="section-sub">اختار شركة موثوقة لشحن طلباتك</div>
            </div>
            <a href="{{ route('shipping') }}" class="section-more">عرض الكل ›</a>
        </div>

        <div class="biz-row">
            @foreach($shipping as $b)
                <a href="{{ route('business.show', $b) }}" class="biz-tile @if($b->isOnBusinessPlan()) is-promoted @endif">
                    @if($b->isOnBusinessPlan())
                        <span class="biz-tile-badge biz-tile-badge-business">★ مميّز</span>
                    @elseif($b->isOnProPlan())
                        <span class="biz-tile-badge biz-tile-badge-pro">PRO</span>
                    @endif
                    <div class="biz-tile-thumb ph ph-{{ $b->type?->slug ?? 'service' }}">
                        @if($b->logo)
                            <img src="{{ $b->logo }}" alt="" onerror="this.style.display='none'">
                        @else
                            <span>{{ mb_substr($b->name, 0, 2) }}</span>
                        @endif
                    </div>
                    <div class="biz-tile-name">
                        {{ Str::limit($b->name, 28) }}
                        @if($b->isPlanVerified())<span class="biz-tile-verified" title="موثّق">✓</span>@endif
                    </div>
                    <div class="biz-tile-meta">
                        <x-icon name="star-f" :size="11"/>
                        {{ number_format($b->rating, 1) }}
                        <span class="dot"></span>
                        <span>{{ $b->reviews_count }} تقييم</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif

{{-- ── SERVICES SECTION ─────────────────────────────────────── --}}
@if($services->count() > 0)
    <div id="services" style="padding: 22px 14px 0; scroll-margin-top: 12px;">
        <div class="section-head">
            <div>
                <div class="section-title">صنايعية وحرفيين</div>
                <div class="section-sub">سباك · كهربائي · نجار · فني تكييف...</div>
            </div>
            <a href="{{ route('services') }}" class="section-more">عرض الكل ›</a>
        </div>

        <div class="biz-row">
            @foreach($services as $b)
                <a href="{{ route('business.show', $b) }}" class="biz-tile @if($b->isOnBusinessPlan()) is-promoted @endif">
                    @if($b->isOnBusinessPlan())
                        <span class="biz-tile-badge biz-tile-badge-business">★ مميّز</span>
                    @elseif($b->isOnProPlan())
                        <span class="biz-tile-badge biz-tile-badge-pro">PRO</span>
                    @endif
                    <div class="biz-tile-thumb ph ph-{{ $b->type?->slug ?? 'service' }}">
                        @if($b->logo)
                            <img src="{{ $b->logo }}" alt="" onerror="this.style.display='none'">
                        @else
                            <span>{{ mb_substr($b->name, 0, 2) }}</span>
                        @endif
                    </div>
                    <div class="biz-tile-name">
                        {{ Str::limit($b->name, 28) }}
                        @if($b->isPlanVerified())<span class="biz-tile-verified" title="موثّق">✓</span>@endif
                    </div>
                    <div class="biz-tile-meta">
                        <x-icon name="star-f" :size="11"/>
                        {{ number_format($b->rating, 1) }}
                        <span class="dot"></span>
                        <span>{{ $b->reviews_count }} تقييم</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif

{{-- ── TASKS BOARD ──────────────────────────────────────────── --}}
<div style="padding: 24px 14px 0;">
    <div class="section-head">
        <div>
            <div class="section-title">آخر المهام المنشورة</div>
            <div class="section-sub">ساعد جارك واكسب · أو انشر طلبك</div>
        </div>
        <a href="{{ route('tasks.index') }}" class="section-more">عرض الكل ›</a>
    </div>

    @forelse($latestTasks as $task)
        <a href="{{ route('tasks.show', $task) }}" class="task-row">
            <div class="task-row-ico" style="background: rgba(245,158,11,.14); color: #B45309;">
                <x-icon name="task" :size="16" stroke="#B45309"/>
            </div>
            <div style="flex: 1; min-width: 0;">
                <div class="task-row-title">{{ $task->title }}</div>
                <div class="task-row-meta">
                    <span>{{ \App\Models\Task::CATEGORIES[$task->category] ?? '' }}</span>
                    @if($task->location)
                        <span class="dot"></span>
                        <span>{{ $task->location }}</span>
                    @endif
                </div>
            </div>
            @if($task->budget)
                <span class="task-row-budget">{{ number_format($task->budget) }} ج</span>
            @endif
        </a>
    @empty
        <div style="padding: 18px; text-align: center; color: var(--ink-3); background: #FAFBFC; border-radius: 12px; font-size: 13px;">
            مفيش مهام دلوقتي.
            <a href="{{ route('tasks.create') }}" style="color: var(--teal); font-weight: 800; margin-right: 4px;">انشر أول مهمة ›</a>
        </div>
    @endforelse
</div>

{{-- ── LOST & FOUND ─────────────────────────────────────────── --}}
@if($latestLost->count() > 0)
    <div style="padding: 24px 14px 0;">
        <div class="section-head">
            <div>
                <div class="section-title">آخر المفقودات</div>
                <div class="section-sub">ساعد جارك يلاقي حاجته</div>
            </div>
            <a href="{{ route('lost.index') }}" class="section-more">عرض الكل ›</a>
        </div>

        <div class="lost-row">
            @foreach($latestLost as $item)
                <a href="{{ route('lost.show', $item) }}" class="lost-tile">
                    @if($item->image)
                        <div class="lost-tile-img">
                            <img src="{{ $item->image }}" alt="" loading="lazy" onerror="this.parentNode.style.background='#F1F4F7'; this.style.display='none'">
                        </div>
                    @else
                        <div class="lost-tile-img lost-tile-placeholder">
                            <x-icon name="search-loc" :size="22" stroke="#94A1AE"/>
                        </div>
                    @endif
                    @if($item->kind === 'lost')
                        <span class="lost-tile-pill is-lost">ضايع</span>
                    @else
                        <span class="lost-tile-pill is-found">موجود</span>
                    @endif
                    <div class="lost-tile-title">{{ Str::limit($item->title, 26) }}</div>
                </a>
            @endforeach
        </div>
    </div>
@endif

{{-- ── ROAD SAFETY (السواقة بأمان) ───────────────────────────── --}}
<div style="padding: 22px 14px 0;">
    <div class="safety-card">
        <div class="safety-card-bg"></div>

        <div class="safety-head">
            <div class="safety-pulse">
                <span class="safety-pulse-dot"></span>
                <span class="safety-pulse-ring"></span>
            </div>
            <div style="flex: 1;">
                <div class="safety-title">السواقة بأمان</div>
                <div class="safety-sub">تنبيهات الطريق لحظة بلحظة في بنها</div>
            </div>
        </div>

        <div class="safety-stats">
            <div class="safety-stat">
                <div class="safety-stat-num">{{ $alertsActive }}</div>
                <div class="safety-stat-lbl">تنبيه نشط</div>
            </div>
            <div class="safety-stat-divider"></div>
            <div class="safety-stat">
                <div class="safety-stat-num">{{ $alertsToday }}</div>
                <div class="safety-stat-lbl">النهاردة</div>
            </div>
            <div class="safety-stat-divider"></div>
            <div class="safety-stat">
                <div class="safety-stat-num" style="font-size: 18px;">⚡</div>
                <div class="safety-stat-lbl">فوري</div>
            </div>
        </div>

        <div class="safety-types">
            <span class="safety-type" style="background: rgba(220,38,38,.16); color: #FCA5A5;">⚠️ حوادث</span>
            <span class="safety-type" style="background: rgba(245,158,11,.16); color: #FCD34D;">🚧 حفر</span>
            <span class="safety-type" style="background: rgba(14,165,233,.16); color: #7DD3FC;">📷 رادارات</span>
            <span class="safety-type" style="background: rgba(124,58,237,.16); color: #C4B5FD;">🚦 زحمة</span>
        </div>

        <div class="safety-actions">
            <a href="{{ route('map') }}" class="safety-btn safety-btn-primary">
                <x-icon name="pin" :size="14" stroke="white"/>
                افتح الخريطة
            </a>
            <a href="{{ route('map') }}?report=1" class="safety-btn safety-btn-ghost">
                + بلّغ عن تنبيه
            </a>
        </div>
    </div>
</div>

{{-- ── CTA: register your service ───────────────────────────── --}}
<div style="padding: 28px 14px;">
    <div class="register-cta">
        <div style="flex: 1;">
            <div style="font-weight: 900; font-size: 15px;">عندك شركة شحن أو خدمة؟</div>
            <p style="font-size: 12.5px; color: rgba(255,255,255,.75); margin: 6px 0 0; line-height: 1.7;">سجّل دلوقتي وابقى ظاهر لآلاف المستخدمين في بنها.</p>
        </div>
        <a href="{{ route('pricing') }}" class="register-cta-btn">شوف الأسعار</a>
    </div>
</div>

<style>
.disc-hero { padding: 14px 14px 4px; }
.disc-hero-inner {
    background: linear-gradient(135deg, #001B2A 0%, #0E2E3F 100%);
    color: white;
    border-radius: 22px;
    padding: 22px 20px 18px;
    position: relative;
    overflow: hidden;
}
.disc-hero-inner::after {
    content: "";
    position: absolute;
    bottom: -60px;
    left: -50px;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: rgba(13,148,136,.18);
}
.disc-greet { font-size: 12px; font-weight: 700; color: rgba(255,255,255,.65); margin-bottom: 6px; position: relative; z-index: 1; }
.disc-headline { font-size: 22px; font-weight: 900; letter-spacing: -.3px; margin: 0 0 6px; position: relative; z-index: 1; line-height: 1.35; }
.disc-sub { font-size: 12.5px; color: rgba(255,255,255,.7); font-weight: 600; margin: 0; line-height: 1.7; position: relative; z-index: 1; }

.disc-search {
    display: flex; align-items: center; gap: 10px;
    background: white; border-radius: 14px;
    padding: 11px 14px; color: var(--ink-3);
    text-decoration: none; font-size: 13px; font-weight: 600;
    margin-top: 14px; position: relative; z-index: 1;
    box-shadow: 0 6px 20px -8px rgba(0,0,0,.4);
}

.quick-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
.quick-tile {
    display: block; padding: 14px;
    border-radius: 14px; border: 1px solid var(--line);
    text-decoration: none; color: var(--ink-1);
    transition: transform .12s ease;
}
.quick-tile:active { transform: scale(.97); }
.quick-tile-ico {
    width: 42px; height: 42px; border-radius: 12px;
    display: grid; place-items: center; margin-bottom: 10px;
}
.quick-tile-title { font-weight: 900; font-size: 14px; }
.quick-tile-sub { font-size: 11.5px; color: var(--ink-3); font-weight: 700; margin-top: 2px; }

.section-head {
    display: flex; align-items: flex-end; justify-content: space-between;
    margin-bottom: 10px; padding: 0 2px;
}
.section-title { font-weight: 900; font-size: 15px; }
.section-sub { font-size: 11.5px; color: var(--ink-3); font-weight: 700; margin-top: 2px; }
.section-more { font-size: 12px; font-weight: 800; color: var(--teal); text-decoration: none; }

.biz-row {
    display: flex; gap: 10px;
    overflow-x: auto; scrollbar-width: none;
    margin: 0 -14px; padding: 0 14px 4px;
}
.biz-row::-webkit-scrollbar { display: none; }
.biz-tile {
    position: relative;
    flex-shrink: 0; width: 158px;
    background: white; border: 1px solid var(--line); border-radius: 14px;
    padding: 12px; text-decoration: none; color: var(--ink-1);
}
.biz-tile.is-promoted {
    border-color: #F59E0B;
    box-shadow: 0 8px 20px -10px rgba(245,158,11,.40);
}
.biz-tile-badge {
    position: absolute;
    top: 8px; right: 8px;
    padding: 2px 7px;
    border-radius: 6px;
    font-size: 9.5px;
    font-weight: 900;
    letter-spacing: .3px;
    z-index: 1;
}
.biz-tile-badge-business {
    background: linear-gradient(135deg, #FBBF24, #F59E0B);
    color: white;
}
.biz-tile-badge-pro {
    background: rgba(13,148,136,.14);
    color: var(--teal);
}
.biz-tile-verified {
    display: inline-block;
    color: var(--teal);
    font-weight: 900;
    margin-right: 2px;
}
.biz-tile-thumb {
    width: 100%; aspect-ratio: 1; border-radius: 11px;
    overflow: hidden; display: grid; place-items: center;
    font-weight: 900; font-size: 18px; color: white; margin-bottom: 10px;
}
.biz-tile-thumb img { width: 100%; height: 100%; object-fit: cover; }
.biz-tile-name { font-weight: 800; font-size: 13px; line-height: 1.4; }
.biz-tile-meta {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; color: var(--ink-3); font-weight: 700; margin-top: 6px;
}
.biz-tile-meta .dot { width: 3px; height: 3px; background: var(--ink-4); border-radius: 50%; }

.task-row {
    display: flex; align-items: center; gap: 10px;
    padding: 12px; background: white; border: 1px solid var(--line);
    border-radius: 12px; margin-bottom: 8px;
    text-decoration: none; color: var(--ink-1);
}
.task-row-ico {
    width: 32px; height: 32px; border-radius: 10px;
    display: grid; place-items: center; flex-shrink: 0;
}
.task-row-title { font-weight: 800; font-size: 13.5px; line-height: 1.4; }
.task-row-meta { font-size: 11px; color: var(--ink-3); font-weight: 700; margin-top: 4px; display: flex; align-items: center; gap: 6px; }
.task-row-meta .dot { width: 3px; height: 3px; background: var(--ink-4); border-radius: 50%; }
.task-row-budget { color: #047857; font-weight: 900; font-size: 12.5px; flex-shrink: 0; }

.lost-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
.lost-tile {
    position: relative; border-radius: 12px; overflow: hidden;
    background: white; border: 1px solid var(--line);
    text-decoration: none; color: var(--ink-1);
}
.lost-tile-img { width: 100%; aspect-ratio: 16/10; background: #F1F4F7; }
.lost-tile-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.lost-tile-placeholder { display: grid; place-items: center; }
.lost-tile-pill {
    position: absolute; top: 8px; right: 8px;
    padding: 3px 8px; border-radius: 7px;
    font-size: 10.5px; font-weight: 800;
}
.lost-tile-pill.is-lost  { background: rgba(220,38,38,.94); color: white; }
.lost-tile-pill.is-found { background: rgba(16,185,129,.94); color: white; }
.lost-tile-title { padding: 9px 10px; font-weight: 800; font-size: 12.5px; line-height: 1.4; }

.register-cta {
    background: linear-gradient(135deg, #001B2A, #0E2E3F); color: white;
    border-radius: 18px; padding: 18px;
    display: flex; align-items: center; gap: 12px;
    box-shadow: 0 10px 30px -10px rgba(0,27,42,.4);
}
.register-cta-btn {
    background: var(--teal); color: white;
    padding: 10px 16px; border-radius: 11px;
    font-weight: 900; font-size: 12.5px; text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 6px 16px -6px rgba(13,148,136,.6);
    flex-shrink: 0;
}

/* ── السواقة بأمان · road safety promo card ───────────────── */
.safety-card {
    position: relative;
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 60%, #0E2E3F 100%);
    color: white;
    border-radius: 20px;
    padding: 18px;
    overflow: hidden;
    box-shadow: 0 18px 38px -14px rgba(15,23,42,.5), 0 4px 12px -4px rgba(15,23,42,.3);
}
.safety-card-bg {
    position: absolute;
    top: -50px; left: -50px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(220,38,38,.20) 0%, transparent 70%);
    filter: blur(20px);
    pointer-events: none;
}
.safety-head {
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 14px;
    position: relative;
}
.safety-pulse {
    width: 44px; height: 44px;
    border-radius: 14px;
    background: rgba(220,38,38,.18);
    display: grid; place-items: center;
    position: relative;
    flex-shrink: 0;
}
.safety-pulse-dot {
    width: 12px; height: 12px;
    border-radius: 50%;
    background: #EF4444;
    box-shadow: 0 0 12px rgba(239,68,68,.8);
}
.safety-pulse-ring {
    position: absolute;
    inset: 8px;
    border-radius: 12px;
    border: 2px solid rgba(239,68,68,.5);
    animation: safetyPulse 1.8s ease-out infinite;
}
@keyframes safetyPulse {
    0%   { transform: scale(0.8); opacity: 1; }
    100% { transform: scale(1.6); opacity: 0; }
}
.safety-title { font-weight: 900; font-size: 16px; }
.safety-sub   { font-size: 11.5px; color: rgba(255,255,255,.65); margin-top: 2px; font-weight: 600; }

.safety-stats {
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 14px;
    padding: 10px 8px;
    margin-bottom: 12px;
    position: relative;
}
.safety-stat { flex: 1; text-align: center; }
.safety-stat-num { font-weight: 900; font-size: 20px; line-height: 1; color: white; }
.safety-stat-lbl { font-size: 10.5px; color: rgba(255,255,255,.55); margin-top: 4px; font-weight: 700; }
.safety-stat-divider { width: 1px; height: 26px; background: rgba(255,255,255,.10); }

.safety-types {
    display: flex; flex-wrap: wrap; gap: 6px;
    margin-bottom: 14px;
    position: relative;
}
.safety-type {
    font-size: 10.5px;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 999px;
}

.safety-actions { display: flex; gap: 8px; position: relative; }
.safety-btn {
    flex: 1;
    padding: 11px 12px;
    border-radius: 12px;
    font-weight: 900; font-size: 12.5px;
    text-decoration: none;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    transition: transform .12s ease;
}
.safety-btn:active { transform: scale(0.97); }
.safety-btn-primary {
    background: var(--teal);
    color: white;
    box-shadow: 0 8px 18px -6px rgba(13,148,136,.55);
}
.safety-btn-ghost {
    background: rgba(255,255,255,.08);
    color: white;
    border: 1px solid rgba(255,255,255,.10);
}
</style>

@include('partials.visitor-nav')
@endsection
