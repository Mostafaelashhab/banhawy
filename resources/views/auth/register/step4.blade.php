@extends('layouts.mobile')

@section('title', 'اختر الباقة · بنهاوي')
@section('screen-class', 'bg-gray')

@section('content')
<div class="app-head">
    <a href="{{ route('register.step3') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">اختر الباقة المناسبة</div>
</div>

@include('partials.wizard-progress', ['step' => 4])

<form method="post" action="{{ route('register.step4.store') }}" style="flex: 1; display: flex; flex-direction: column;">
    @csrf
    @php $featured = $plans->firstWhere('is_featured', true); @endphp

    <div class="scroll" style="padding: 0 14px 14px;">
        @foreach($plans as $plan)
            <label data-plan-card style="display: block; cursor: pointer; margin-bottom: 10px; position: relative;">
                <input type="radio" name="plan_id" value="{{ $plan->id }}" style="position: absolute; opacity: 0; pointer-events: none;" @if($plan->is_featured) checked @endif>
                <div class="plan-card @if($plan->is_featured) is-featured @endif" style="background: white; border-radius: 18px; padding: 14px; border: 1px solid var(--line); transition: all .15s;">
                    @if($plan->is_featured)
                        <span style="position: absolute; top: -10px; right: 14px; background: var(--teal); color: white; padding: 3px 12px; border-radius: 999px; font-size: 10px; font-weight: 800;">الأكثر اختيارًا</span>
                    @endif
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="font-weight: 800; font-size: 14px;">{{ $plan->name }}</div>
                            <div class="label-meta">{{ $plan->tagline_ar }}</div>
                        </div>
                        <div style="text-align: left;">
                            <span style="font-weight: 900; font-size: 20px; color: {{ $plan->is_featured ? 'var(--teal)' : 'var(--navy)' }};">{{ $plan->price_monthly }}</span>
                            <span class="tiny" style="color: var(--ink-3);">ج/شهر</span>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 10px;">
                        @foreach($plan->features as $feat)
                            <div style="display: flex; gap: 6px; font-size: 11px; font-weight: 600; color: var(--gray-700);">
                                <span style="color: var(--teal);"><x-icon name="check" :size="12" stroke="#0D9488" w="3"/></span>
                                {{ $feat }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </label>
        @endforeach
    </div>

    <div class="cta-bar">
        <button type="submit" class="btn btn-navy btn-full" style="padding: 13px; font-size: 14px;">
            ابدأ خطة <span id="selected-plan-name">{{ $featured?->name ?? 'Pro' }}</span> · <span id="selected-plan-price">{{ $featured?->price_monthly ?? 399 }}</span>ج/شهر
        </button>
    </div>
</form>

<style>
[data-plan-card] input:checked + .plan-card {
    border-color: var(--teal); border-width: 2px;
    box-shadow: 0 0 0 6px rgba(13,148,136,.07);
}
</style>

<script>
document.querySelectorAll('[data-plan-card] input').forEach(r => {
    r.addEventListener('change', () => {
        const card = r.closest('[data-plan-card]').querySelector('.plan-card');
        document.getElementById('selected-plan-name').textContent = card.querySelector('[style*="font-size: 14px"]').textContent;
        document.getElementById('selected-plan-price').textContent = card.querySelector('[style*="font-size: 20px"]').textContent;
    });
});
</script>
@endsection
