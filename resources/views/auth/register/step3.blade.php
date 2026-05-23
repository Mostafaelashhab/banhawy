@extends('layouts.mobile')

@section('title', 'بيانات النشاط · بنهاوي')

@section('content')
<div class="app-head">
    <a href="{{ route('register.step2') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">بيانات النشاط</div>
</div>

@include('partials.wizard-progress', ['step' => 3])

<form method="post" action="{{ route('register.step3.store') }}" style="flex: 1; display: flex; flex-direction: column;">
    @csrf
    <div class="scroll" style="padding: 0 18px 90px;">
        <h2 style="font-size: 18px; font-weight: 900; color: var(--navy);">معلومات نشاطك</h2>
        <p class="tiny" style="color: var(--ink-3); margin-top: 4px; margin-bottom: 16px;">دي اللي هتظهر على صفحة موقعك</p>

        <div style="display: flex; flex-direction: column; gap: 11px;">
            <div>
                <label class="field-label">اسم النشاط</label>
                <div class="field">
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="مثلًا: Pizza Zone" required>
                </div>
                @error('name') <div class="tiny" style="color: #B91C1C; margin-top: 5px;">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="field-label">التصنيف</label>
                <div class="field">
                    <input type="text" name="category" value="{{ old('category') }}" placeholder="مثلًا: مطعم بيتزا" required>
                    <span style="color: var(--ink-4);"><x-icon name="chev-d" :size="16"/></span>
                </div>
            </div>
            <div>
                <label class="field-label">رقم واتساب الأعمال</label>
                <div class="field">
                    <span style="color: var(--wa-600);"><x-icon name="whatsapp" :size="16" stroke="#1FB855"/></span>
                    <input type="tel" name="whatsapp" value="{{ old('whatsapp', auth()->user()->phone ?? '') }}" placeholder="+20 1xx xxx xxxx" required dir="ltr" style="text-align: right;">
                </div>
            </div>
            <div>
                <label class="field-label">العنوان</label>
                <div class="field">
                    <input type="text" name="address" value="{{ old('address') }}" placeholder="مثلًا: شارع فريد ندا، بنها" required>
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

<script>
document.getElementById('use-my-location')?.addEventListener('click', function () {
    if (!navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition(pos => {
        document.getElementById('lat').value = pos.coords.latitude.toFixed(4);
        document.getElementById('lng').value = pos.coords.longitude.toFixed(4);
        document.getElementById('coords-display').textContent = pos.coords.latitude.toFixed(4) + ', ' + pos.coords.longitude.toFixed(4);
    });
});
</script>
@endsection
