@extends('layouts.mobile')

@section('title', 'نشر بلاغ · بنهاوي')
@section('page-title', 'نشر بلاغ')
@section('shell-class', 'no-bnav')

@section('content')
<div class="app-head">
    <a href="{{ route('lost.index') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">نشر بلاغ مفقود</div>
</div>

<form method="post" action="{{ route('lost.store') }}" enctype="multipart/form-data" style="padding: 16px 16px 24px; flex: 1; display: flex; flex-direction: column; gap: 14px;">
    @csrf

    @if($errors->any())
        <div class="flash err">{{ $errors->first() }}</div>
    @endif

    <div>
        <label class="field-label">نوع البلاغ</label>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 6px;">
            @foreach(\App\Models\LostItem::KINDS as $key => $label)
                <label class="kind-card" style="cursor: pointer;">
                    <input type="radio" name="kind" value="{{ $key }}" @checked($kind === $key) required style="position: absolute; opacity: 0;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        @if($key === 'lost')
                            <span class="kind-card-ico" style="background: rgba(220,38,38,.10); color: #B91C1C;">
                                <x-icon name="search-loc" :size="18" stroke="#B91C1C"/>
                            </span>
                        @else
                            <span class="kind-card-ico" style="background: rgba(16,185,129,.14); color: #047857;">
                                <x-icon name="check" :size="18" stroke="#047857"/>
                            </span>
                        @endif
                        <div>
                            <div style="font-weight: 800;">{{ $label }}</div>
                            <div style="font-size: 11px; color: var(--ink-3); font-weight: 600;">
                                {{ $key === 'lost' ? 'حاجة ضاعت مني' : 'لقيت حاجة لحد' }}
                            </div>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <label class="field-label">عنوان البلاغ</label>
        <input type="text" name="title" value="{{ old('title') }}" required maxlength="160" placeholder="مثال: محفظة سوداء جلد فيها كارنيه" class="form-input">
    </div>

    <div>
        <label class="field-label">التصنيف</label>
        <select name="category" required class="form-input">
            <option value="">اختر التصنيف...</option>
            @foreach(\App\Models\LostItem::CATEGORIES as $key => $label)
                <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="field-label">الوصف بالتفصيل</label>
        <textarea name="description" required minlength="15" maxlength="2000" rows="4" placeholder="اللون، الحجم، علامات مميزة، آخر مكان شفته فيه..." class="form-input">{{ old('description') }}</textarea>
    </div>

    <div>
        <label class="field-label">صورة <span style="color: var(--ink-4); font-weight: 600;">(اختياري — بتساعد كتير)</span></label>
        <input type="file" name="image" accept="image/*" class="form-input" style="padding: 8px;">
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
        <div>
            <label class="field-label">المكان</label>
            <input type="text" name="location" value="{{ old('location') }}" maxlength="160" placeholder="حدائق بنها · شارع ٦" class="form-input">
        </div>
        <div>
            <label class="field-label">التاريخ</label>
            <input type="date" name="happened_on" value="{{ old('happened_on') }}" max="{{ now()->toDateString() }}" class="form-input">
        </div>
    </div>

    <div>
        <label class="field-label">مكافأة لو حد لقاه <span style="color: var(--ink-4); font-weight: 600;">(اختياري)</span></label>
        <input type="number" name="reward" value="{{ old('reward') }}" min="0" max="1000000" placeholder="مثال: 100 جنيه" class="form-input" inputmode="numeric">
    </div>

    <div style="padding: 12px 14px; background: #FAFBFC; border: 1px solid var(--line); border-radius: 12px;">
        <div class="label-strong" style="margin-bottom: 8px; font-size: 13px;">معلومات التواصل</div>

        <div style="margin-bottom: 10px;">
            <label class="field-label">اسمك</label>
            <input type="text" name="contact_name" value="{{ old('contact_name', auth()->user()?->name) }}" required maxlength="120" class="form-input">
        </div>

        <div>
            <label class="field-label">رقم التليفون</label>
            <input type="tel" name="contact_phone" value="{{ old('contact_phone', auth()->user()?->phone) }}" required minlength="8" maxlength="30" dir="ltr" style="text-align: right;" class="form-input" placeholder="01xxxxxxxxx">
        </div>
    </div>

    <button type="submit" class="btn btn-teal btn-full" style="padding: 14px; font-size: 14px;">نشر البلاغ</button>
</form>

<style>
.form-input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--line);
    border-radius: 11px;
    font-family: inherit;
    font-size: 14px;
    background: white;
    color: var(--ink-1);
    margin-top: 6px;
}
.form-input:focus {
    outline: none;
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(13,148,136,.14);
}
textarea.form-input { resize: vertical; min-height: 90px; line-height: 1.7; }

.kind-card {
    display: block;
    padding: 12px;
    border: 1.5px solid var(--line);
    border-radius: 12px;
    background: white;
    position: relative;
    transition: border-color .15s ease, background .15s ease;
}
.kind-card:has(input:checked) {
    border-color: var(--teal);
    background: rgba(13,148,136,.04);
}
.kind-card-ico {
    width: 36px; height: 36px;
    border-radius: 11px;
    display: grid; place-items: center;
    flex-shrink: 0;
}
</style>
@endsection
