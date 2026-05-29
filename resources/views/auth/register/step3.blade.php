@extends('layouts.mobile')

@section('title', 'بيانات النشاط · بنهاوي')

@section('content')
@php
    // Type-aware copy: shipping vs service
    $isShipping = $type?->slug === 'shipping';
    $copy = $isShipping
        ? [
            'heading'        => 'بيانات شركة الشحن',
            'sub'            => 'دي اللي هتظهر للعميل لما يدور على شحن في بنها.',
            'name_label'     => 'اسم الشركة',
            'name_ph'        => 'مثلًا: Mostafa Express',
            'cat_label'      => 'نوع خدمة الشحن',
            'cat_ph'         => 'مثلًا: شحن داخلي وخارجي',
            'cat_suggestions'=> ['شحن داخل بنها', 'شحن بين المحافظات', 'شحن دولي', 'بضائع وأثاث', 'شحن سريع 24 ساعة'],
            'wa_ph'          => '01xxxxxxxxx · رقم خدمة العملاء',
            'address_label'  => 'مقرّ الشركة',
            'address_ph'     => 'مثلًا: مدخل بنها الرئيسي',
        ]
        : [
            'heading'        => 'بيانات خدمتك',
            'sub'            => 'دي اللي هتظهر للعميل لما يدور على صنايعي في بنها.',
            'name_label'     => 'الاسم أو اسم الورشة',
            'name_ph'        => 'مثلًا: سباك الحاج محمود',
            'cat_label'      => 'نوع الخدمة',
            'cat_ph'         => 'مثلًا: سباك / كهربائي / نجار',
            'cat_suggestions'=> ['سباكة وصيانة مواسير', 'كهرباء وتمديدات', 'صيانة وتركيب تكييفات', 'موبيليا وأبواب', 'دهانات وديكور', 'تنظيف منازل', 'نقل أثاث'],
            'wa_ph'          => '01xxxxxxxxx · للتواصل والمواعيد',
            'address_label'  => 'مكانك أو منطقة عملك',
            'address_ph'     => 'مثلًا: حدائق بنها · شارع ٦',
        ];
@endphp

<div class="app-head">
    <a href="{{ route('register.step2') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">{{ $copy['heading'] }}</div>
</div>

@include('partials.wizard-progress', ['step' => 3])

<form method="post" action="{{ route('register.step3.store') }}" style="flex: 1; display: flex; flex-direction: column;">
    @csrf
    <div class="scroll" style="padding: 0 18px 90px;">

        {{-- Type pill — confirms what they picked in step 2 --}}
        @if($type)
            <div style="display: inline-flex; align-items: center; gap: 8px; padding: 5px 11px; background: rgba(13,148,136,.10); color: var(--teal); border-radius: 8px; font-size: 11.5px; font-weight: 800; margin-bottom: 12px;">
                <x-icon :name="$type->icon ?? 'briefcase'" :size="13" stroke="#0D9488"/>
                {{ $type->name_ar }}
            </div>
        @endif

        <h2 style="font-size: 18px; font-weight: 900; color: var(--navy);">{{ $copy['heading'] }}</h2>
        <p class="tiny" style="color: var(--ink-3); margin-top: 4px; margin-bottom: 16px; line-height: 1.7;">{{ $copy['sub'] }}</p>

        <div style="display: flex; flex-direction: column; gap: 11px;">
            <div>
                <label class="field-label">{{ $copy['name_label'] }}</label>
                <div class="field">
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ $copy['name_ph'] }}" required>
                </div>
                @error('name') <div class="tiny" style="color: #B91C1C; margin-top: 5px;">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="field-label">{{ $copy['cat_label'] }}</label>
                <div class="field">
                    <input type="text" name="category" id="cat-input" value="{{ old('category') }}" placeholder="{{ $copy['cat_ph'] }}" required list="cat-suggestions">
                </div>
                <datalist id="cat-suggestions">
                    @foreach($copy['cat_suggestions'] as $s)
                        <option value="{{ $s }}"></option>
                    @endforeach
                </datalist>
                <div style="display: flex; gap: 5px; margin-top: 8px; flex-wrap: wrap;">
                    @foreach($copy['cat_suggestions'] as $s)
                        <button type="button" class="cat-suggest-chip" data-val="{{ $s }}">{{ $s }}</button>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="field-label">رقم واتساب الأعمال</label>
                <div class="field">
                    <span style="color: var(--wa-600);"><x-icon name="whatsapp" :size="16" stroke="#1FB855"/></span>
                    <input type="tel" name="whatsapp" value="{{ old('whatsapp', auth()->user()->phone ?? '') }}" placeholder="{{ $copy['wa_ph'] }}" required dir="ltr" style="text-align: right;">
                </div>
            </div>
            <div>
                <label class="field-label">{{ $copy['address_label'] }}</label>
                <div class="field">
                    <input type="text" name="address" value="{{ old('address') }}" placeholder="{{ $copy['address_ph'] }}" required>
                </div>
            </div>

            <div>
                <label class="field-label" style="display: flex; justify-content: space-between;">
                    <span>حدد موقعك على الخريطة</span>
                    <button type="button" id="use-my-location" style="border: none; background: transparent; color: var(--teal); font-weight: 800; font-size: 11px;">استخدم موقعي</button>
                </label>
                <div style="position: relative; height: 150px; border-radius: 14px; overflow: hidden; border: 1px solid var(--line-2);">
                    <div class="map-bg map-roads" style="position: absolute; inset: 0;"></div>
                    <div style="position: absolute; top: 45%; left: 50%; transform: translate(-50%, -100%);">
                        <svg viewBox="0 0 28 32" width="34" height="38">
                            <path d="M14 0a14 14 0 0 0-14 14c0 10 14 18 14 18s14-8 14-18A14 14 0 0 0 14 0z" fill="#0D9488"/>
                            <circle cx="14" cy="14" r="5" fill="white"/>
                        </svg>
                    </div>
                    <div id="coords-display" style="position: absolute; bottom: 8px; right: 8px; background: white; padding: 4px 8px; border-radius: 8px; font-size: 10px; font-weight: 800;">
                        30.4582, 31.1797
                    </div>
                </div>
                <input type="hidden" name="lat" id="lat" value="30.4582">
                <input type="hidden" name="lng" id="lng" value="31.1797">
            </div>
        </div>
    </div>

    <div class="cta-bar">
        <button type="submit" class="btn btn-navy btn-full" style="padding: 13px; font-size: 14px;">
            التالي <x-icon name="arrow-l" :size="14" stroke="white" w="2.4"/>
        </button>
    </div>
</form>

<style>
.cat-suggest-chip {
    background: white;
    border: 1px solid var(--line);
    padding: 5px 10px;
    border-radius: 8px;
    font-family: inherit;
    font-size: 11.5px;
    font-weight: 700;
    color: var(--ink-2);
    cursor: pointer;
    transition: background .12s ease, border-color .12s ease, color .12s ease;
}
.cat-suggest-chip:hover { background: #FAFBFC; border-color: var(--teal); color: var(--teal); }
.cat-suggest-chip:active { transform: scale(.95); }
</style>

<script>
document.getElementById('use-my-location')?.addEventListener('click', function () {
    if (!navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition(pos => {
        document.getElementById('lat').value = pos.coords.latitude.toFixed(4);
        document.getElementById('lng').value = pos.coords.longitude.toFixed(4);
        document.getElementById('coords-display').textContent = pos.coords.latitude.toFixed(4) + ', ' + pos.coords.longitude.toFixed(4);
    });
});

// Click a suggestion chip → fill the category input
document.querySelectorAll('.cat-suggest-chip').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById('cat-input');
        if (input) {
            input.value = btn.dataset.val;
            input.focus();
        }
    });
});
</script>
@endsection
