@extends('layouts.mobile')

@section('title', $business->name . ' · بنهاوي')
@section('page-title', $business->name)
@section('shell-class', 'no-bnav')

@section('content')
@php
    $typeSlug = $business->type->slug;
    $orderableTypes = ['restaurant', 'shop'];
    $canOrder = in_array($typeSlug, $orderableTypes) && ($business->acceptsWebOrders() || $business->acceptsWhatsappOrders());
    $canBook  = $business->acceptsWebBookings() || $business->acceptsWhatsappBookings();
    $walkinOnly = $business->isWalkinOnly();
    $hasMenu  = $canOrder;
    $showPriceRange = in_array($typeSlug, ['restaurant', 'shop', 'salon']);
    $menuLabel  = ['restaurant' => 'المنيو', 'shop' => 'المنتجات'][$typeSlug] ?? 'المنتجات';
    $orderLabel = 'اطلب الآن';
    $bookLabel  = ['clinic' => 'احجز كشف', 'salon' => 'احجز موعد', 'education' => 'احجز', 'restaurant' => 'احجز طاولة', 'service' => 'اطلب خدمة'][$typeSlug] ?? 'احجز';
    $bookUrl    = $business->acceptsWebBookings()
        ? route('business.book.form', $business)
        : route('business.whatsapp', ['business' => $business, 'message' => 'مرحبًا، حابب أحجز']);
@endphp

{{-- ── Flash toasts (claim + report feedback) ─────────────── --}}
@php
    $toast = null;
    if (session('report_status') === 'submitted') {
        $toast = ['type' => 'success', 'text' => 'شكراً لإبلاغك — هنراجع المعلومة قريب ✓'];
    } elseif (session('report_status') === 'duplicate') {
        $toast = ['type' => 'warn',    'text' => 'سبق إنك بلّغت عن هذا النشاط خلال اليوم.'];
    } elseif (session('claim_status') === 'submitted') {
        $toast = ['type' => 'success', 'text' => 'تم استلام طلب الملكية ✓ هنراجعه ونتواصل معاك قريب.'];
    } elseif (session('claim_status') === 'already_owned') {
        $toast = ['type' => 'warn',    'text' => 'هذا النشاط له مالك بالفعل.'];
    }
@endphp
@if($toast)
    <div class="biz-toast biz-toast-{{ $toast['type'] }}" role="status" id="biz-toast">
        <span class="biz-toast-icon">
            @if($toast['type'] === 'success')
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            @else
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="13"/><circle cx="12" cy="17" r="0.5" fill="currentColor"/>
                </svg>
            @endif
        </span>
        <span class="biz-toast-text">{{ $toast['text'] }}</span>
        <button type="button" class="biz-toast-close" aria-label="إغلاق" onclick="this.parentNode.remove()">✕</button>
    </div>
@endif

<div class="biz">

    {{-- ── HERO (cover + name) ─────────────────────────────── --}}
    <div class="biz-cover ph ph-{{ $business->type->slug }}">
        <div class="biz-cover-overlay"></div>
        <div class="biz-cover-actions">
            <a href="{{ route('home') }}" class="biz-icon-btn" aria-label="رجوع للرئيسية"><x-icon name="chev-r" :size="18" stroke="white"/></a>
            <div style="display: flex; gap: 8px;">
                <button class="biz-icon-btn" onclick="navigator.share ? navigator.share({title: '{{ $business->name }}', url: window.location.href}) : navigator.clipboard.writeText(window.location.href)">
                    <x-icon name="share" :size="16" stroke="white"/>
                </button>
                @auth
                    @php $isFav = auth()->user()->hasFavorited($business); @endphp
                    <form method="post" action="{{ route('favorites.toggle', $business) }}" style="display: contents;">
                        @csrf
                        <button class="biz-icon-btn @if($isFav) is-fav @endif" aria-label="{{ $isFav ? 'إزالة من المفضلة' : 'أضف للمفضلة' }}">
                            @if($isFav)
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="#FF4D6D" stroke="#FF4D6D" stroke-width="1.5">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            @else
                                <x-icon name="heart" :size="16" stroke="white"/>
                            @endif
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="biz-icon-btn" aria-label="سجّل دخولك للحفظ">
                        <x-icon name="heart" :size="16" stroke="white"/>
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <div class="biz-head">
        <div class="biz-logo">
            <div class="ph ph-{{ $business->type->slug }}" style="width: 100%; height: 100%; border-radius: 14px; font-size: 18px;">
                {{ mb_substr($business->name, 0, 2) }}
            </div>
        </div>
        <div class="biz-id">
            <div class="biz-name">{{ $business->name }}</div>
            <div class="label-meta">{{ $business->category }}</div>
            <div class="biz-meta">
                <span class="stars" style="font-size: 12px;">
                    <x-icon name="star-f" :size="12"/> {{ number_format($business->rating, 1) }}
                    <span style="color: var(--ink-4); font-weight: 600;">({{ $business->reviews_count }})</span>
                </span>
                <span class="dot"></span>
                @if($business->isOpenNow())
                    <span class="chip open" style="padding: 2px 7px; font-size: 10px;">مفتوح الآن</span>
                @else
                    <span class="chip closed" style="padding: 2px 7px; font-size: 10px;">مغلق</span>
                @endif
                @if($business->isPlanVerified())
                    <span class="chip teal" style="padding: 2px 7px; font-size: 10px;">موثّق ✓</span>
                @endif
                @if($business->isOnBusinessPlan())
                    <span class="chip" style="padding: 2px 7px; font-size: 10px; background: linear-gradient(135deg, #FBBF24, #F59E0B); color: white; font-weight: 800;">★ مميّز</span>
                @elseif($business->isOnProPlan())
                    <span class="chip" style="padding: 2px 7px; font-size: 10px; background: rgba(13,148,136,.14); color: var(--teal); font-weight: 800;">PRO</span>
                @endif
            </div>
        </div>

        {{-- Desktop-only inline CTAs --}}
        <div class="biz-head-cta">
            @if($canOrder)
                <a href="{{ route('business.menu', $business) }}" class="btn btn-wa" style="padding: 12px 18px;">
                    <x-icon name="cart" :size="14" stroke="white"/> {{ $orderLabel }}
                </a>
            @endif
            @if($canBook)
                <a href="{{ $bookUrl }}" class="btn btn-navy" style="padding: 12px 18px;">{{ $bookLabel }}</a>
            @endif
            @if($walkinOnly && ! $canOrder)
                <span class="chip teal" style="padding: 8px 14px; font-size: 12px;">بدون حجز — تواجد مباشر</span>
            @endif
        </div>
    </div>

    {{-- ── TABS ─────────────────────────────────────────────── --}}
    @php
        $hasImages  = is_array($business->images) && count($business->images) > 0;
        $reviewsAll = $business->reviews->sortByDesc('created_at');
        $reviewsCount = $reviewsAll->count();
    @endphp
    <div class="biz-tabs">
        <a href="#" class="biz-tab is-active" onclick="event.preventDefault(); window.scrollTo({top:0,behavior:'smooth'})">الرئيسية</a>
        @if($hasMenu)
            <a href="{{ route('business.menu', $business) }}" class="biz-tab">{{ $menuLabel }}</a>
        @endif
        @if($hasImages)
            <a href="#biz-gallery" class="biz-tab" data-gallery-tab hidden>الصور</a>
        @endif
        <a href="#biz-reviews" class="biz-tab">
            التقييمات @if($reviewsCount) <span style="color: var(--ink-4); font-weight: 700;">({{ $reviewsCount }})</span> @endif
        </a>
    </div>

    {{-- ── BODY (mobile single column, desktop 2-column) ───── --}}
    <div class="biz-body">

        <div class="biz-main">
            {{-- Quick actions --}}
            <div class="biz-quick">
                <a href="tel:{{ $business->telLink() }}" class="btn btn-line biz-quick-btn">
                    <span style="color: var(--teal);"><x-icon name="phone" :size="18" stroke="#0D9488"/></span>
                    اتصل
                </a>
                <a href="{{ route('business.whatsapp', $business) }}" class="btn btn-line biz-quick-btn">
                    <span style="color: var(--wa);"><x-icon name="whatsapp" :size="18" stroke="#25D366"/></span>
                    واتساب
                </a>
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $business->lat }},{{ $business->lng }}" target="_blank" class="btn btn-line biz-quick-btn">
                    <span style="color: var(--navy);"><x-icon name="directions" :size="18" stroke="#001B2A"/></span>
                    الاتجاهات
                </a>
            </div>

            @if($business->description)
                <div class="card card-pad" style="margin-top: 14px;">
                    <div class="label-strong" style="margin-bottom: 6px;">عن النشاط</div>
                    <p class="muted" style="line-height: 1.75;">{{ $business->description }}</p>
                </div>
            @endif

            {{-- ── IMAGES GALLERY ─────────────────────────────── --}}
            @if($hasImages)
                <div id="biz-gallery" class="card card-pad" style="margin-top: 14px; scroll-margin-top: 80px;" hidden data-gallery>
                    <div class="label-strong" style="margin-bottom: 10px;">الصور والمنيو</div>
                    <div class="biz-gallery">
                        @foreach($business->images as $i => $img)
                            <button type="button" class="biz-gallery-item" data-img="{{ $img }}" aria-label="صورة {{ $i + 1 }}" hidden>
                                <img src="{{ $img }}" alt="صورة {{ $i + 1 }}" loading="lazy" referrerpolicy="no-referrer"
                                     onload="this.closest('.biz-gallery-item').hidden=false; var g=this.closest('[data-gallery]'); g.hidden=false; var t=document.querySelector('[data-gallery-tab]'); if(t) t.hidden=false"
                                     onerror="this.closest('.biz-gallery-item').remove()">
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ── REVIEWS ────────────────────────────────────── --}}
            <div id="biz-reviews" class="card card-pad" style="margin-top: 14px; scroll-margin-top: 80px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <div class="label-strong">التقييمات @if($reviewsCount) <span style="color: var(--ink-4); font-weight: 700;">({{ $reviewsCount }})</span> @endif</div>
                    @if($business->rating > 0)
                        <span class="stars" style="font-size: 13px;">
                            <x-icon name="star-f" :size="14"/> {{ number_format($business->rating, 1) }}
                        </span>
                    @endif
                </div>

                {{-- ── ADD REVIEW FORM ─────────────────────── --}}
                @php $myReview = auth()->check() ? $business->reviews->firstWhere('user_id', auth()->id()) : null; @endphp
                @auth
                    <div class="biz-review-form" style="background: var(--gray-100); border-radius: 14px; padding: 12px; margin-bottom: 12px;">
                        <div style="font-weight: 800; font-size: 13px; margin-bottom: 8px;">
                            {{ $myReview ? 'عدّل تقييمك' : 'قيّم النشاط' }}
                        </div>
                        <form method="post" action="{{ route('business.review.store', $business) }}">
                            @csrf
                            <div class="rating-picker" style="display: flex; gap: 6px; direction: ltr; justify-content: flex-end; margin-bottom: 10px;">
                                @for($s = 5; $s >= 1; $s--)
                                    <input type="radio" name="rating" id="rate-{{ $s }}" value="{{ $s }}"
                                           @checked(($myReview->rating ?? 0) == $s)
                                           style="position: absolute; opacity: 0; pointer-events: none;">
                                    <label for="rate-{{ $s }}" data-star="{{ $s }}"
                                           style="cursor: pointer; line-height: 1; -webkit-tap-highlight-color: transparent;">
                                        <svg class="rate-star" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#D9DDE3" stroke-width="1.6" stroke-linejoin="round">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                        </svg>
                                    </label>
                                @endfor
                            </div>
                            <textarea name="body" rows="2" maxlength="1000"
                                      placeholder="اكتب رأيك (اختياري) — تجربتك مع الصنايعي/الشركة"
                                      style="width: 100%; border: 1px solid var(--ink-5); border-radius: 10px; padding: 8px 10px; font-size: 13px; font-family: inherit; resize: vertical;">{{ $myReview->body ?? '' }}</textarea>
                            @error('rating') <div style="color: #B91C1C; font-size: 11px; margin-top: 4px;">{{ $message }}</div> @enderror
                            <div style="display: flex; gap: 8px; margin-top: 10px; align-items: center;">
                                <button type="submit" class="btn btn-teal" style="padding: 9px 14px; font-size: 12px;">
                                    {{ $myReview ? 'حفظ' : 'إرسال' }}
                                </button>
                                @if($myReview)
                                    <button type="submit" form="del-my-review" class="btn btn-line" style="padding: 9px 14px; font-size: 12px; color: #B91C1C; border-color: #FECACA;">
                                        حذف تقييمي
                                    </button>
                                @endif
                            </div>
                        </form>
                        @if($myReview)
                            <form id="del-my-review" method="post" action="{{ route('business.review.destroy', [$business, $myReview]) }}" style="display: none;">
                                @csrf @method('DELETE')
                            </form>
                        @endif
                    </div>
                    <script>
                        (function() {
                            const picker = document.currentScript.previousElementSibling.querySelector('.rating-picker');
                            if (!picker) return;
                            const labels = [...picker.querySelectorAll('label')].sort((a,b)=>+a.dataset.star - +b.dataset.star);
                            const radios = [...picker.querySelectorAll('input[type=radio]')];
                            const paint = (val) => {
                                labels.forEach(l => {
                                    const poly = l.querySelector('polygon');
                                    if (!poly) return;
                                    const filled = +l.dataset.star <= val;
                                    poly.setAttribute('stroke', filled ? '#F59E0B' : '#D9DDE3');
                                    poly.setAttribute('fill',   filled ? '#F59E0B' : 'none');
                                });
                            };
                            labels.forEach(l => {
                                l.addEventListener('mouseenter', () => paint(+l.dataset.star));
                                l.addEventListener('mouseleave', () => paint(+(radios.find(r=>r.checked)?.value || 0)));
                                l.addEventListener('click',     () => { radios.find(r=>+r.value===+l.dataset.star).checked = true; paint(+l.dataset.star); });
                            });
                            paint(+(radios.find(r=>r.checked)?.value || 0));
                        })();
                    </script>
                @else
                    <a href="{{ route('login') }}?next={{ urlencode(route('business.show', $business).'#biz-reviews') }}"
                       class="btn btn-line btn-full" style="padding: 10px; font-size: 12px; margin-bottom: 12px;">
                        سجّل دخولك عشان تقدر تقيّم
                    </a>
                @endauth

                @if($reviewsCount === 0)
                    <p class="muted" style="text-align: center; padding: 16px 0;">لا توجد تقييمات بعد — كن أول من يضيف رأيه.</p>
                @else
                    @php $showAll = request()->boolean('all_reviews'); @endphp
                    <div class="biz-reviews-list">
                        @foreach(($showAll ? $reviewsAll : $reviewsAll->take(10)) as $r)
                            <div class="biz-review">
                                <div class="biz-review-head">
                                    <div class="biz-review-avatar">
                                        {{ mb_substr($r->reviewer_name ?? 'ز', 0, 1) }}
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-weight: 800; font-size: 13px;">
                                            {{ $r->reviewer_name ?? 'زائر' }}
                                            @if($r->reviewer_phone)
                                                <span style="color: var(--ink-4); font-weight: 600; font-size: 11px; direction: ltr;">
                                                    · {{ '****'.substr($r->reviewer_phone, -4) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="label-meta">{{ $r->created_at?->translatedFormat('d M Y') }}</div>
                                    </div>
                                    <div class="biz-review-rating">
                                        @for($s = 1; $s <= 5; $s++)
                                            <x-icon name="star-f" :size="12" stroke="{{ $s <= $r->rating ? '#F59E0B' : '#D9DDE3' }}"/>
                                        @endfor
                                    </div>
                                </div>
                                @if($r->body)
                                    <p class="biz-review-body">{{ $r->body }}</p>
                                @endif
                                @if(is_array($r->replies) && count($r->replies))
                                    <div class="biz-review-replies">
                                        @foreach($r->replies as $rep)
                                            @if(!empty($rep['body']))
                                                <div class="biz-review-reply">
                                                    <div class="label-meta" style="margin-bottom: 2px;">رد — {{ $rep['date'] ?? '' }}</div>
                                                    <p style="margin: 0;">{{ $rep['body'] }}</p>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($reviewsCount > 10 && ! $showAll)
                        <div style="text-align: center; margin-top: 10px;">
                            <button type="button" class="btn btn-line" id="reviews-show-more" style="padding: 8px 16px; font-size: 12px;">
                                عرض الكل ({{ $reviewsCount }})
                            </button>
                        </div>
                    @endif
                @endif
            </div>

            {{-- ── CLAIM OWNERSHIP ───────────────────────────── --}}
            @if(! $business->owner_id)
                <div class="card card-pad biz-claim-card" style="margin-top: 14px;">
                    @if(session('claim_status') === 'submitted')
                        <div class="label-strong" style="color: var(--teal);">تم استلام طلبك ✓</div>
                        <p class="muted" style="margin-top: 6px; line-height: 1.7;">هنراجع الطلب ونتواصل معاك خلال أيام قليلة.</p>
                    @else
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <span style="width: 36px; height: 36px; border-radius: 12px; background: var(--teal-50); display: grid; place-items: center; color: var(--teal); flex-shrink: 0;">
                                <x-icon name="lock" :size="18" stroke="#0D9488"/>
                            </span>
                            <div>
                                <div class="label-strong">أنت صاحب هذا النشاط؟</div>
                                <div class="label-meta" style="margin-top: 2px;">قدّم طلب ملكية وهنراجعه ونتواصل معاك.</div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-navy" style="width: 100%; padding: 11px; font-size: 13px;" id="claim-open">
                            تقديم طلب ملكية
                        </button>
                    @endif
                </div>

                <dialog id="claim-dialog" class="biz-claim-dialog" dir="rtl">
                    <button type="button" class="biz-claim-close" id="claim-close" aria-label="إغلاق">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
                            <line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/>
                        </svg>
                    </button>

                    <div class="biz-claim-header">
                        <span class="biz-claim-badge">
                            <x-icon name="lock" :size="20" stroke="#0D9488"/>
                        </span>
                        <div>
                            <div class="biz-claim-title">طلب ملكية</div>
                            <div class="biz-claim-sub">{{ $business->name }}</div>
                        </div>
                    </div>

                    <form method="post" action="{{ route('business.claim', $business) }}" class="biz-claim-form">
                        @csrf

                        <label class="biz-claim-label" for="claim_name">الاسم الكامل</label>
                        <input id="claim_name" name="claimant_name" required minlength="3" maxlength="120" class="biz-claim-input" autocomplete="name" placeholder="مثال: محمد أحمد">

                        <label class="biz-claim-label" for="claim_phone">رقم موبايل (واتساب)</label>
                        <input id="claim_phone" name="claimant_phone" required minlength="8" maxlength="20" inputmode="tel" class="biz-claim-input" autocomplete="tel" placeholder="01xxxxxxxxx" dir="ltr" style="text-align: right;">

                        <label class="biz-claim-label" for="claim_email">الإيميل <span class="biz-claim-opt">(اختياري)</span></label>
                        <input id="claim_email" name="claimant_email" type="email" maxlength="120" class="biz-claim-input" autocomplete="email" placeholder="you@example.com" dir="ltr" style="text-align: right;">

                        <label class="biz-claim-label" for="claim_msg">رسالة <span class="biz-claim-opt">(اختياري)</span></label>
                        <textarea id="claim_msg" name="message" maxlength="1000" rows="3" class="biz-claim-input" placeholder="إيه اللي بيثبت ملكيتك للنشاط؟ (سجل تجاري، صورة من المحل…)"></textarea>

                        <button type="submit" class="btn btn-teal biz-claim-submit">
                            إرسال الطلب
                        </button>
                        <div class="biz-claim-note">هنراجع طلبك ونتواصل معاك خلال أيام قليلة.</div>
                    </form>
                </dialog>
            @endif

            {{-- ── REPORT BUTTON ─────────────────────────────── --}}
            <div class="biz-report-row">
                @if(session('report_status') === 'submitted')
                    <span class="biz-report-thanks">شكراً لإبلاغك — هنراجع المعلومة قريب ✓</span>
                @elseif(session('report_status') === 'duplicate')
                    <span class="biz-report-thanks" style="color: #B45309;">سبق إنك بلّغت عن هذا النشاط خلال اليوم.</span>
                @else
                    <button type="button" id="report-open" class="biz-report-btn">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 22V4a3 3 0 0 1 3-3h11l-3 5 3 5H7"/>
                            <line x1="4" y1="22" x2="4" y2="15"/>
                        </svg>
                        إبلاغ عن مشكلة في النشاط
                    </button>
                @endif
            </div>

            <dialog id="report-dialog" class="biz-claim-dialog" dir="rtl">
                <button type="button" class="biz-claim-close" id="report-close" aria-label="إغلاق">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
                        <line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/>
                    </svg>
                </button>

                <div class="biz-claim-header" style="background: linear-gradient(135deg, rgba(220,38,38,.06), rgba(220,38,38,0));">
                    <span class="biz-claim-badge" style="border-color: rgba(220,38,38,.20); box-shadow: 0 4px 12px -4px rgba(220,38,38,.30);">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#DC2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 22V4a3 3 0 0 1 3-3h11l-3 5 3 5H7"/>
                            <line x1="4" y1="22" x2="4" y2="15"/>
                        </svg>
                    </span>
                    <div>
                        <div class="biz-claim-title">إبلاغ عن مشكلة</div>
                        <div class="biz-claim-sub">{{ $business->name }}</div>
                    </div>
                </div>

                <form method="post" action="{{ route('business.report', $business) }}" class="biz-claim-form">
                    @csrf

                    <label class="biz-claim-label">سبب البلاغ</label>
                    <div class="report-reasons" id="report-reasons">
                        @foreach(\App\Models\BusinessReport::REASONS as $key => $label)
                            <label class="report-reason">
                                <input type="radio" name="reason" value="{{ $key }}" required @if($loop->first) checked @endif>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>

                    <label class="biz-claim-label" for="report_details">تفاصيل <span class="biz-claim-opt">(اختياري)</span></label>
                    <textarea id="report_details" name="details" rows="3" maxlength="2000" class="biz-claim-input" placeholder="وضّحلنا المشكلة بالظبط…"></textarea>

                    <label class="biz-claim-label" for="report_phone">رقمك للتواصل <span class="biz-claim-opt">(اختياري)</span></label>
                    <input id="report_phone" name="reporter_phone" maxlength="30" inputmode="tel" class="biz-claim-input" autocomplete="tel" placeholder="01xxxxxxxxx" dir="ltr" style="text-align: right;">

                    <button type="submit" class="btn biz-claim-submit" style="background: #DC2626; color: white;">
                        إرسال البلاغ
                    </button>
                    <div class="biz-claim-note">بنراجع كل البلاغات يدوياً للحفاظ على جودة الدليل.</div>
                </form>
            </dialog>

            {{-- ── LIGHTBOX FOR GALLERY ──────────────────────── --}}
            @if($hasImages)
                <div id="biz-lightbox" class="biz-lightbox" hidden>
                    <button type="button" class="biz-lightbox-close" aria-label="إغلاق">✕</button>
                    <img alt="" referrerpolicy="no-referrer">
                </div>
            @endif
        </div>

        <aside class="biz-side">
            <div class="card biz-info">
                <div class="biz-info-row">
                    <span class="biz-info-ico"><x-icon name="pin" :size="16" stroke="#0D9488"/></span>
                    <div>
                        <div style="font-weight: 800; font-size: 13px;">{{ $business->address }}</div>
                        <div class="label-meta">{{ $business->lat }}, {{ $business->lng }}</div>
                    </div>
                </div>
                <div class="biz-info-sep"></div>
                <div class="biz-info-row">
                    <span class="biz-info-ico"><x-icon name="clock" :size="16" stroke="#0D9488"/></span>
                    <div style="flex: 1;">
                        @php $today = $business->hours[(int) now()->format('w')] ?? null; @endphp
                        @if($today && empty($today['closed']))
                            <div style="font-weight: 800; font-size: 13px;">يفتح اليوم @time12($today['open']) – @time12($today['close'])</div>
                        @else
                            <div style="font-weight: 800; font-size: 13px;">مغلق اليوم</div>
                        @endif
                        <div class="label-meta">المواعيد قابلة للتغيير في الأعياد</div>
                    </div>
                    @if($business->isOpenNow())
                        <span class="chip open" style="padding: 2px 7px; font-size: 10px;">مفتوح</span>
                    @endif
                </div>
                @if($showPriceRange)
                    <div class="biz-info-sep"></div>
                    <div class="biz-info-row">
                        <span class="biz-info-ico"><x-icon name="tag" :size="16" stroke="#0D9488"/></span>
                        <div>
                            <div style="font-weight: 800; font-size: 13px;">
                                {{ ['low' => 'أسعار اقتصادية', 'medium' => 'نطاق سعري متوسط', 'high' => 'نطاق سعري مرتفع'][$business->price_range] }}
                            </div>
                            <div class="label-meta">
                                @if($business->delivery) توصيل متاح @else استلام من المحل @endif
                            </div>
                        </div>
                    </div>
                @endif
                @if($walkinOnly)
                    <div class="biz-info-sep"></div>
                    <div class="biz-info-row">
                        <span class="biz-info-ico"><x-icon name="pin" :size="16" stroke="#0D9488"/></span>
                        <div>
                            <div style="font-weight: 800; font-size: 13px;">بدون حجز — تواجد مباشر</div>
                            <div class="label-meta">تعالى في مواعيد العمل بدون موعد مسبق.</div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Mini map: route from user → business (Leaflet + OSM + OSRM) --}}
            <div class="card biz-mini-map">
                <div id="biz-mini-map"
                     data-lat="{{ $business->lat }}"
                     data-lng="{{ $business->lng }}"
                     data-name="{{ $business->name }}"
                     style="position: absolute; inset: 0; background: #EEF1F4;"></div>

                {{-- Route info overlay --}}
                <div id="biz-route-info" class="biz-route-info" hidden>
                    <div class="biz-route-info-row">
                        <span class="biz-route-info-dot" style="background: #0D9488;"></span>
                        <span id="biz-route-distance">— كم</span>
                        <span style="color: var(--ink-4); margin: 0 6px;">·</span>
                        <span id="biz-route-duration">— دقيقة</span>
                    </div>
                </div>

                {{-- Locate me button --}}
                <button type="button" id="biz-locate-btn" class="biz-locate-btn" aria-label="موقعي">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="2.5"/>
                        <line x1="12" y1="2"  x2="12" y2="5"/>
                        <line x1="12" y1="19" x2="12" y2="22"/>
                        <line x1="2"  y1="12" x2="5"  y2="12"/>
                        <line x1="19" y1="12" x2="22" y2="12"/>
                    </svg>
                </button>

                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $business->lat }},{{ $business->lng }}" target="_blank" class="biz-mini-map-cta">
                    <x-icon name="directions" :size="14"/> الاتجاهات
                </a>
            </div>
        </aside>
    </div>

    {{-- ── STICKY CTA (mobile only) ────────────────────────── --}}
    @if($canOrder || $canBook || $walkinOnly)
        <div class="biz-sticky-cta">
            @if($canOrder)
                <a href="{{ route('business.menu', $business) }}" class="btn btn-wa" style="flex: 2; padding: 12px; font-size: 13px;">
                    <x-icon name="cart" :size="14" stroke="white"/> {{ $orderLabel }}
                </a>
            @endif
            @if($canBook)
                <a href="{{ $bookUrl }}" class="btn btn-navy" style="flex: {{ $canOrder ? '1.2' : '1' }}; padding: 12px; font-size: 13px;">{{ $bookLabel }}</a>
            @endif
            @if($walkinOnly && ! $canOrder)
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $business->lat }},{{ $business->lng }}" target="_blank" class="btn btn-navy" style="flex: 1; padding: 12px; font-size: 13px;">
                    <x-icon name="directions" :size="14" stroke="white"/> اعرف الطريق
                </a>
            @endif
        </div>
    @endif
</div>
@endsection

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
.biz { display: flex; flex-direction: column; flex: 1; }

#biz-mini-map .leaflet-container { background: #EEF1F4; font-family: 'Cairo', sans-serif; }
#biz-mini-map .leaflet-tile-pane  { filter: saturate(.85); }
.banhawy-pin { background: transparent !important; border: none !important; }
.banhawy-pin svg { filter: drop-shadow(0 4px 6px rgba(0,27,42,.25)); }

.biz-cover {
    position: relative;
    height: 170px;
    flex-shrink: 0;
}
.biz-cover-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(0,27,42,0) 30%, rgba(0,27,42,.55) 100%);
}
.biz-cover-actions {
    position: absolute; top: 14px; right: 14px; left: 14px;
    display: flex; justify-content: space-between;
}
.biz-icon-btn {
    width: 36px; height: 36px; border-radius: 12px;
    background: rgba(255,255,255,.18); backdrop-filter: blur(8px);
    display: grid; place-items: center; color: white; border: none;
}

.biz-head {
    padding: 12px 14px 0;
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    flex-wrap: wrap;
}
.biz-logo {
    width: 70px; height: 70px;
    border-radius: 18px;
    background: white;
    padding: 4px;
    box-shadow: var(--shadow);
    flex-shrink: 0;
    margin-top: -48px; /* only logo overlaps the cover */
    position: relative;
}
.biz-id { flex: 1; min-width: 0; padding-top: 4px; }
.biz-name { font-weight: 900; font-size: 18px; }
.biz-meta { display: flex; align-items: center; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
.biz-head-cta { display: none; }

.biz-tabs {
    margin-top: 14px;
    border-bottom: 1px solid var(--line);
    padding: 0 14px;
    display: flex;
    gap: 16px;
    overflow-x: auto;
    scrollbar-width: none;
    flex-shrink: 0;
}
.biz-tabs::-webkit-scrollbar { display: none; }
.biz-tab {
    padding: 10px 0;
    font-size: 12px;
    font-weight: 700;
    color: var(--ink-3);
    white-space: nowrap;
}
.biz-tab.is-active {
    color: var(--teal);
    border-bottom: 2px solid var(--teal);
    margin-bottom: -1px;
    font-weight: 800;
}

.biz-body {
    padding: 14px;
    padding-bottom: 90px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    flex: 1;
}

.biz-quick { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.biz-quick-btn { flex-direction: column; padding: 12px 6px; gap: 4px; font-size: 11px; }

.biz-info { padding: 12px; }
.biz-info-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; }
.biz-info-ico { color: var(--teal); flex-shrink: 0; }
.biz-info-sep { height: 1px; background: var(--line); }

/* ── Gallery ─────────────────────────────────────────────────── */
.biz-gallery {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
}
.biz-gallery-item {
    background: var(--ink-7, #F1F4F7);
    border: none;
    padding: 0;
    border-radius: 12px;
    overflow: hidden;
    aspect-ratio: 1;
    cursor: zoom-in;
    transition: transform .15s ease;
}
.biz-gallery-item:active { transform: scale(0.97); }
.biz-gallery-item img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}

.biz-lightbox {
    position: fixed; inset: 0;
    background: rgba(0, 27, 42, 0.92);
    -webkit-backdrop-filter: blur(8px);
    backdrop-filter: blur(8px);
    z-index: 100;
    display: grid;
    place-items: center;
    padding: 24px;
}
.biz-lightbox[hidden] { display: none; }
.biz-lightbox img {
    max-width: 100%; max-height: 100%;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0,0,0,.4);
}
.biz-lightbox-close {
    position: absolute;
    top: calc(14px + env(safe-area-inset-top));
    right: 14px;
    width: 40px; height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,.15);
    color: white;
    border: none;
    font-size: 18px;
    cursor: pointer;
}

/* ── Reviews ─────────────────────────────────────────────────── */
.biz-reviews-list { display: flex; flex-direction: column; gap: 14px; }
.biz-review { border-bottom: 1px solid var(--line); padding-bottom: 14px; }
.biz-review:last-child { border-bottom: none; padding-bottom: 0; }
.biz-review-head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 6px;
}
.biz-review-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: var(--teal-50);
    color: var(--teal);
    display: grid; place-items: center;
    font-weight: 900;
    font-size: 14px;
    flex-shrink: 0;
}
.biz-review-rating { display: inline-flex; gap: 1px; flex-shrink: 0; }
.biz-review-body { margin: 4px 0 0; line-height: 1.7; color: var(--ink-2); font-size: 13px; }

.biz-review-replies {
    margin-top: 8px;
    padding-right: 14px;
    border-right: 2px solid var(--line);
    display: flex; flex-direction: column; gap: 6px;
}
.biz-review-reply { font-size: 12px; color: var(--ink-3); }

/* ── Claim dialog ────────────────────────────────────────────── */
.biz-claim-card { background: linear-gradient(135deg, rgba(13,148,136,.05), rgba(0,27,42,.02)); }

.biz-claim-dialog {
    border: none;
    border-radius: 20px;
    padding: 0;
    width: min(92vw, 440px);
    max-height: 90dvh;
    background: white;
    box-shadow: 0 30px 80px -10px rgba(0,27,42,.35), 0 8px 24px -8px rgba(0,27,42,.18);
    overflow: hidden;
    color: var(--ink-1);
    /* Explicit centering — overrides any UA quirks under RTL */
    position: fixed;
    inset: 0;
    margin: auto;
    top: 50%;
    left: 50%;
    right: auto;
    bottom: auto;
    transform: translate(-50%, -50%);
}
.biz-claim-dialog::backdrop {
    background: rgba(0,27,42,.55);
    -webkit-backdrop-filter: blur(6px);
    backdrop-filter: blur(6px);
}

/* Dialog opening animation — preserves the centering translate */
.biz-claim-dialog[open] {
    animation: claimIn .22s cubic-bezier(.2,.9,.3,1.2);
}
@keyframes claimIn {
    from { transform: translate(-50%, calc(-50% + 12px)) scale(.96); opacity: 0; }
    to   { transform: translate(-50%, -50%)              scale(1);   opacity: 1; }
}

.biz-claim-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px 20px 14px;
    background: linear-gradient(135deg, rgba(13,148,136,.07), rgba(13,148,136,.0));
    border-bottom: 1px solid rgba(0,27,42,.06);
}
.biz-claim-badge {
    width: 42px; height: 42px;
    border-radius: 13px;
    background: white;
    border: 1px solid rgba(13,148,136,.18);
    box-shadow: 0 4px 12px -4px rgba(13,148,136,.35);
    display: grid; place-items: center;
    flex-shrink: 0;
}
.biz-claim-title {
    font-weight: 900;
    font-size: 15px;
    color: var(--ink-1);
}
.biz-claim-sub {
    font-size: 12px;
    color: var(--ink-3);
    margin-top: 2px;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 280px;
}

.biz-claim-close {
    position: absolute;
    top: 14px;
    left: 14px;   /* RTL: ✕ at far end */
    background: rgba(0, 27, 42, 0.06);
    border: none;
    width: 30px; height: 30px;
    border-radius: 50%;
    cursor: pointer;
    color: var(--ink-2);
    display: grid;
    place-items: center;
    transition: background .15s ease, transform .15s ease;
    z-index: 2;
}
.biz-claim-close:hover { background: rgba(0,27,42,.12); }
.biz-claim-close:active { transform: scale(.92); }

.biz-claim-form {
    padding: 16px 20px 20px;
    overflow-y: auto;
}
.biz-claim-label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--ink-2);
    margin: 12px 0 6px;
}
.biz-claim-label:first-child { margin-top: 0; }
.biz-claim-opt {
    color: var(--ink-4);
    font-weight: 600;
    font-size: 11px;
}
.biz-claim-input {
    width: 100%;
    padding: 11px 13px;
    border: 1px solid rgba(0,27,42,.12);
    border-radius: 11px;
    font-family: inherit;
    font-size: 13.5px;
    background: #FAFBFC;
    color: var(--ink-1);
    transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
}
.biz-claim-input::placeholder { color: var(--ink-4); font-weight: 500; }
.biz-claim-input:focus {
    outline: none;
    background: white;
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(13,148,136,.14);
}
textarea.biz-claim-input { resize: vertical; min-height: 70px; line-height: 1.6; }

.biz-claim-submit {
    width: 100%;
    padding: 13px;
    font-size: 13.5px;
    font-weight: 800;
    margin-top: 16px;
    border-radius: 12px;
}
.biz-claim-note {
    text-align: center;
    font-size: 11.5px;
    color: var(--ink-4);
    margin-top: 10px;
    font-weight: 600;
}

/* ── Toast notification (top of page) ───────────────────────── */
.biz-toast {
    position: fixed;
    top: calc(16px + env(safe-area-inset-top));
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 16px;
    background: white;
    box-shadow: 0 20px 50px -12px rgba(0, 27, 42, 0.35),
                0 6px 16px -6px rgba(0, 27, 42, 0.14);
    z-index: 99999;
    width: min(92vw, 440px);
    font-size: 14px;
    font-weight: 700;
    line-height: 1.55;
    animation: biztoastIn .35s cubic-bezier(.2,.9,.3,1.3);
}
@keyframes biztoastIn {
    0%   { opacity: 0; transform: translate(-50%, -20px) scale(.9); }
    60%  { opacity: 1; transform: translate(-50%, 4px)  scale(1.03); }
    100% { opacity: 1; transform: translate(-50%, 0)    scale(1); }
}
.biz-toast-success { color: #047857; border-right: 4px solid #10B981; }
.biz-toast-warn    { color: #92400E; border-right: 4px solid #F59E0B; }
.biz-toast-icon {
    flex-shrink: 0;
    width: 30px; height: 30px;
    border-radius: 50%;
    display: grid;
    place-items: center;
}
.biz-toast-success .biz-toast-icon { background: rgba(16,185,129,.12); color: #047857; }
.biz-toast-warn    .biz-toast-icon { background: rgba(245,158,11,.14); color: #B45309; }
.biz-toast-text {
    flex: 1;
    min-width: 0;
    color: var(--ink-1);
}
.biz-toast-close {
    background: transparent;
    border: none;
    color: var(--ink-4);
    font-size: 16px;
    cursor: pointer;
    padding: 4px 6px;
    border-radius: 6px;
    flex-shrink: 0;
}
.biz-toast-close:hover { background: rgba(0,27,42,.06); }

.biz-toast.is-hiding {
    animation: biztoastOut .25s ease forwards;
}
@keyframes biztoastOut {
    to { opacity: 0; transform: translate(-50%, -16px) scale(.95); }
}

/* ── Report button + reasons ─────────────────────────────────── */
.biz-report-row {
    text-align: center;
    margin-top: 14px;
    padding: 4px;
}
.biz-report-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: transparent;
    border: none;
    color: var(--ink-4);
    font-family: inherit;
    font-size: 12px;
    font-weight: 700;
    padding: 8px 12px;
    border-radius: 10px;
    cursor: pointer;
    transition: color .15s ease, background .15s ease;
    -webkit-tap-highlight-color: transparent;
}
.biz-report-btn:hover {
    color: #DC2626;
    background: rgba(220,38,38,.06);
}
.biz-report-btn:active { transform: scale(0.97); }
.biz-report-thanks {
    display: inline-block;
    color: var(--teal);
    font-size: 12.5px;
    font-weight: 800;
    padding: 8px 12px;
}

.report-reasons {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-top: 4px;
}
.report-reason {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border: 1px solid rgba(0,27,42,.12);
    border-radius: 10px;
    background: #FAFBFC;
    cursor: pointer;
    transition: background .15s ease, border-color .15s ease;
}
.report-reason input { accent-color: #DC2626; flex-shrink: 0; }
.report-reason span { font-size: 13px; font-weight: 700; color: var(--ink-1); }
.report-reason:has(input:checked) {
    border-color: #DC2626;
    background: rgba(220,38,38,.05);
}

.biz-mini-map {
    position: relative;
    height: 220px;
    overflow: hidden;
}
.biz-route-info {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255,255,255,.96);
    -webkit-backdrop-filter: blur(8px);
    backdrop-filter: blur(8px);
    padding: 7px 11px;
    border-radius: 11px;
    box-shadow: 0 6px 16px -4px rgba(0,27,42,.18);
    z-index: 500;
    font-size: 12px;
    font-weight: 800;
    color: var(--ink-1);
}
.biz-route-info-row { display: flex; align-items: center; gap: 6px; }
.biz-route-info-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px rgba(13,148,136,.18);
}
.biz-locate-btn {
    position: absolute;
    top: 10px;
    left: 10px;
    width: 32px; height: 32px;
    border-radius: 10px;
    background: white;
    border: 1px solid var(--line);
    color: var(--ink-1);
    display: grid; place-items: center;
    cursor: pointer;
    box-shadow: 0 6px 16px -4px rgba(0,27,42,.16);
    z-index: 500;
    transition: background .12s ease;
}
.biz-locate-btn:hover { background: #FAFBFC; }
.biz-locate-btn.is-loading svg { animation: locateSpin .8s linear infinite; }
@keyframes locateSpin { to { transform: rotate(360deg); } }
.banhawy-route-line { filter: drop-shadow(0 2px 4px rgba(13,148,136,.4)); }
.banhawy-user-pin { background: transparent !important; border: none !important; }
.biz-mini-map-cta {
    position: absolute;
    bottom: 12px; right: 12px; left: 12px;
    background: white;
    border-radius: 10px;
    padding: 8px;
    text-align: center;
    font-size: 12px; font-weight: 800;
    box-shadow: var(--shadow);
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
}

.biz-sticky-cta {
    position: sticky;
    bottom: 0;
    padding: 10px 14px;
    padding-bottom: calc(14px + env(safe-area-inset-bottom));
    background: rgba(255, 255, 255, 0.94);
    -webkit-backdrop-filter: saturate(180%) blur(16px);
    backdrop-filter: saturate(180%) blur(16px);
    border-top: 1px solid rgba(0, 27, 42, 0.08);
    box-shadow: 0 -8px 24px -12px rgba(0, 27, 42, 0.18);
    display: flex;
    gap: 8px;
    flex-shrink: 0;
    z-index: 30;
}

/* ─── DESKTOP ≥ 1024px ─────────────────────────────────────── */
@media (min-width: 1024px) {
    .biz-cover {
        height: 240px;
        border-radius: 0 0 24px 24px;
        margin-bottom: 0;
    }
    .biz-cover-actions { display: none; } /* topbar handles nav on desktop */

    /* Name moves OUT of cover overlap - sits cleanly below */
    .biz-head {
        max-width: var(--content-max);
        margin: 24px auto 0;
        padding: 0 32px;
        align-items: center;
        gap: 20px;
    }
    .biz-logo {
        width: 100px; height: 100px;
        border-radius: 24px;
        margin-top: -70px; /* lift over cover edge cleanly, but only 30px overlap */
        position: relative;
        z-index: 2;
    }
    .biz-id { padding-bottom: 0; }
    .biz-name { font-size: 28px; letter-spacing: -.5px; }
    .biz-id .label-meta { font-size: 13px; }
    .biz-meta { margin-top: 10px; }

    .biz-head-cta {
        display: flex;
        gap: 10px;
        margin-right: auto; /* RTL: push to far end */
    }

    .biz-tabs {
        max-width: var(--content-max);
        margin: 24px auto 0;
        padding: 0 32px;
        gap: 28px;
    }
    .biz-tab { padding: 14px 0; font-size: 14px; }

    .biz-body {
        max-width: var(--content-max);
        margin: 0 auto;
        padding: 28px 32px 48px;
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 28px;
        align-items: start;
    }

    .biz-main { display: flex; flex-direction: column; gap: 16px; }
    .biz-side { display: flex; flex-direction: column; gap: 16px; position: sticky; top: 88px; }

    .biz-quick { grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .biz-quick-btn { padding: 16px; font-size: 13px; }

    /* Hide mobile sticky CTA on desktop */
    .biz-sticky-cta { display: none; }

    .biz-mini-map { height: 220px; }
}

@media (min-width: 1440px) {
    .biz-cover { height: 280px; }
    .biz-name { font-size: 32px; }
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    var el = document.getElementById('biz-mini-map');
    if (!el || typeof L === 'undefined') return;

    var lat  = parseFloat(el.dataset.lat);
    var lng  = parseFloat(el.dataset.lng);
    var name = el.dataset.name || '';
    if (isNaN(lat) || isNaN(lng)) return;

    // Compact, non-interactive map: drag/scroll are fine, but no zoom UI clutter
    var map = L.map(el, {
        zoomControl: false,
        attributionControl: false,
        scrollWheelZoom: false,
        dragging: true,
        doubleClickZoom: false,
    }).setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    // Business pin (destination)
    var bizIcon = L.divIcon({
        className: 'banhawy-pin',
        html: '<svg viewBox="0 0 28 32" width="34" height="38">' +
              '<path d="M14 0a14 14 0 0 0-14 14c0 10 14 18 14 18s14-8 14-18A14 14 0 0 0 14 0z" fill="#0D9488"/>' +
              '<circle cx="14" cy="14" r="5" fill="white"/></svg>',
        iconSize:   [34, 38],
        iconAnchor: [17, 38],
    });
    L.marker([lat, lng], { icon: bizIcon, title: name }).addTo(map);

    // ── Routing: user → business via OSRM ─────────────────────────
    var routeLayer  = null;
    var userMarker  = null;
    var routeInfoEl = document.getElementById('biz-route-info');
    var distanceEl  = document.getElementById('biz-route-distance');
    var durationEl  = document.getElementById('biz-route-duration');
    var locateBtn   = document.getElementById('biz-locate-btn');

    var userIcon = L.divIcon({
        className: 'banhawy-user-pin',
        html: '<svg viewBox="0 0 24 24" width="22" height="22">' +
              '<circle cx="12" cy="12" r="10" fill="#fff" opacity=".85"/>' +
              '<circle cx="12" cy="12" r="6"  fill="#0D6EFD"/>' +
              '<circle cx="12" cy="12" r="3"  fill="#fff"/></svg>',
        iconSize:   [22, 22],
        iconAnchor: [11, 11],
    });

    function fmtDistance(meters) {
        if (meters < 1000) return Math.round(meters) + ' متر';
        return (meters / 1000).toFixed(meters < 10000 ? 1 : 0) + ' كم';
    }
    function fmtDuration(seconds) {
        var mins = Math.round(seconds / 60);
        if (mins < 60) return mins + ' دقيقة';
        var h = Math.floor(mins / 60);
        var m = mins % 60;
        return h + ' س' + (m > 0 ? ' و ' + m + ' د' : '');
    }

    function clearRoute() {
        if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }
        if (userMarker) { map.removeLayer(userMarker); userMarker = null; }
        if (routeInfoEl) routeInfoEl.hidden = true;
    }

    function drawStraightLine(uLat, uLng) {
        // Fallback when OSRM fails: a dashed straight line
        routeLayer = L.polyline([[uLat, uLng], [lat, lng]], {
            color: '#0D9488',
            weight: 4,
            opacity: 0.85,
            dashArray: '8 6',
            className: 'banhawy-route-line',
        }).addTo(map);

        var meters = map.distance([uLat, uLng], [lat, lng]);
        if (distanceEl) distanceEl.textContent = fmtDistance(meters);
        if (durationEl) durationEl.textContent = '≈ ' + fmtDuration(meters / 8.33); // ~30 km/h
        if (routeInfoEl) routeInfoEl.hidden = false;
    }

    function fetchRoute(uLat, uLng) {
        // OSRM public demo — fine for low-volume usage
        var url = 'https://router.project-osrm.org/route/v1/driving/'
                + uLng + ',' + uLat + ';' + lng + ',' + lat
                + '?overview=full&geometries=geojson';

        return fetch(url).then(function (r) {
            if (!r.ok) throw new Error('osrm http ' + r.status);
            return r.json();
        }).then(function (data) {
            if (!data.routes || !data.routes[0]) throw new Error('no routes');
            var route = data.routes[0];
            var coords = route.geometry.coordinates.map(function (c) { return [c[1], c[0]]; });

            // Outline (white) + main line (teal) for nice depth
            var outline = L.polyline(coords, {
                color: '#ffffff', weight: 8, opacity: 0.95, lineCap: 'round', lineJoin: 'round',
            });
            var line = L.polyline(coords, {
                color: '#0D9488', weight: 5, opacity: 0.95, lineCap: 'round', lineJoin: 'round',
                className: 'banhawy-route-line',
            });
            routeLayer = L.layerGroup([outline, line]).addTo(map);

            if (distanceEl) distanceEl.textContent = fmtDistance(route.distance);
            if (durationEl) durationEl.textContent = fmtDuration(route.duration);
            if (routeInfoEl) routeInfoEl.hidden = false;
        });
    }

    function plotRoute(uLat, uLng) {
        clearRoute();
        userMarker = L.marker([uLat, uLng], { icon: userIcon, title: 'موقعك' }).addTo(map);

        // Fit bounds to user + business
        var bounds = L.latLngBounds([[uLat, uLng], [lat, lng]]);
        map.fitBounds(bounds, { padding: [30, 30], maxZoom: 16 });

        fetchRoute(uLat, uLng).catch(function () {
            // Network/OSRM failure → fall back to straight line so users still see the path
            drawStraightLine(uLat, uLng);
        });
    }

    function locate(forcePrompt) {
        if (! ('geolocation' in navigator)) return;
        if (locateBtn) locateBtn.classList.add('is-loading');

        navigator.geolocation.getCurrentPosition(function (pos) {
            if (locateBtn) locateBtn.classList.remove('is-loading');
            plotRoute(pos.coords.latitude, pos.coords.longitude);
        }, function () {
            if (locateBtn) locateBtn.classList.remove('is-loading');
            if (forcePrompt) {
                // Only alert when user explicitly clicked the locate button
                // Silent fail on auto-attempt so the map still shows the destination
            }
        }, {
            enableHighAccuracy: false,
            timeout: 8000,
            maximumAge: 60000,
        });
    }

    if (locateBtn) {
        locateBtn.addEventListener('click', function () { locate(true); });
    }

    // Auto-attempt on page load (browser will prompt the first time only)
    locate(false);
})();
</script>

<script>
// Image lightbox
(function () {
    var box = document.getElementById('biz-lightbox');
    if (!box) return;
    var img = box.querySelector('img');

    document.querySelectorAll('.biz-gallery-item').forEach(function (btn) {
        btn.addEventListener('click', function () {
            img.src = btn.dataset.img;
            box.hidden = false;
            document.body.style.overflow = 'hidden';
        });
    });

    function close() {
        box.hidden = true;
        img.src = '';
        document.body.style.overflow = '';
    }
    box.addEventListener('click', function (e) {
        if (e.target === box || e.target.classList.contains('biz-lightbox-close')) close();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !box.hidden) close();
    });
})();

// Generic modal dialog binder (claim + report share the same UX)
(function () {
    function bind(dlgId, openId, closeId) {
        var dlg = document.getElementById(dlgId);
        var openBtn = document.getElementById(openId);
        var closeBtn = document.getElementById(closeId);
        if (!dlg || !openBtn) return;

        openBtn.addEventListener('click', function () {
            if (typeof dlg.showModal === 'function') dlg.showModal();
            else dlg.setAttribute('open', '');
        });
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                if (typeof dlg.close === 'function') dlg.close();
                else dlg.removeAttribute('open');
            });
        }
        dlg.addEventListener('click', function (e) {
            var rect = dlg.getBoundingClientRect();
            var inside = e.clientX >= rect.left && e.clientX <= rect.right
                      && e.clientY >= rect.top && e.clientY <= rect.bottom;
            if (!inside) dlg.close();
        });
    }
    bind('claim-dialog',  'claim-open',  'claim-close');
    bind('report-dialog', 'report-open', 'report-close');
})();

// Auto-dismiss flash toast after 6s
(function () {
    var t = document.getElementById('biz-toast');
    if (!t) return;
    // Also log to console for debugging
    console.log('[banhawy] showing toast:', t.querySelector('.biz-toast-text')?.textContent);
    setTimeout(function () {
        t.classList.add('is-hiding');
        setTimeout(function () { t.remove(); }, 280);
    }, 6000);
})();

// Reviews "show all"
(function () {
    var btn = document.getElementById('reviews-show-more');
    if (!btn) return;
    btn.addEventListener('click', function () {
        // Hidden items are off the DOM (we only rendered first 10).
        // Simplest: navigate to a query-flagged version that renders all.
        var u = new URL(window.location.href);
        u.searchParams.set('all_reviews', '1');
        u.hash = 'biz-reviews';
        window.location.href = u.toString();
    });
})();
</script>
@endpush
