@extends('layouts.mobile')

@section('title', 'نشر مهمة · بنهاوي')
@section('page-title', 'نشر مهمة')
@section('shell-class', 'no-bnav')

@section('content')
<div class="app-head">
    <a href="{{ route('tasks.index') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">نشر مهمة جديدة</div>
</div>

<form method="post" action="{{ route('tasks.store') }}" style="padding: 16px 16px 24px; flex: 1; display: flex; flex-direction: column; gap: 14px;">
    @csrf

    @if($errors->any())
        <div class="flash err">{{ $errors->first() }}</div>
    @endif

    <div class="card" style="padding: 14px; background: linear-gradient(135deg, rgba(13,148,136,.06), rgba(0,27,42,.02));">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="width: 38px; height: 38px; border-radius: 12px; background: white; display: grid; place-items: center; color: var(--teal);">
                <x-icon name="task" :size="20" stroke="#0D9488"/>
            </span>
            <div>
                <div class="label-strong">اشرح المهمة بالتفصيل</div>
                <div class="label-meta" style="margin-top: 2px;">معلومات أوضح = ردود أسرع وأنسب.</div>
            </div>
        </div>
    </div>

    <div>
        <label class="field-label">عنوان المهمة</label>
        <input type="text" name="title" value="{{ old('title') }}" required maxlength="160" placeholder="مثال: عاوز حد ينضّفلي شقة الجمعة" class="form-input">
    </div>

    <div>
        <label class="field-label">التصنيف</label>
        <select name="category" required class="form-input">
            <option value="">اختر التصنيف...</option>
            @foreach(\App\Models\Task::CATEGORIES as $key => $label)
                <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="field-label">شرح تفصيلي</label>
        <textarea name="description" required minlength="15" maxlength="2000" rows="4" placeholder="اكتب كل التفاصيل اللي محتاجها لأي حد يساعدك..." class="form-input">{{ old('description') }}</textarea>
    </div>

    <div>
        <label class="field-label">المكان <span style="color: var(--ink-4); font-weight: 600;">(اختياري)</span></label>
        <input type="text" name="location" value="{{ old('location') }}" maxlength="160" placeholder="مثال: حدائق بنها · شارع ٦" class="form-input">
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
        <div>
            <label class="field-label">الميزانية بالجنيه <span style="color: var(--ink-4); font-weight: 600;">(اختياري)</span></label>
            <input type="number" name="budget" value="{{ old('budget') }}" min="0" max="1000000" placeholder="مثال: 200" class="form-input" inputmode="numeric">
        </div>
        <div>
            <label class="field-label">درجة الأولوية</label>
            <select name="urgency" required class="form-input">
                @foreach(\App\Models\Task::URGENCIES as $key => $label)
                    <option value="{{ $key }}" @selected(old('urgency', 'normal') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div style="padding: 12px 14px; background: #FAFBFC; border: 1px solid var(--line); border-radius: 12px;">
        <div class="label-strong" style="margin-bottom: 8px; font-size: 13px;">معلومات التواصل</div>

        <div style="margin-bottom: 10px;">
            <label class="field-label">اسمك</label>
            <input type="text" name="contact_name" value="{{ old('contact_name', auth()->user()?->name) }}" required maxlength="120" class="form-input">
        </div>

        <div style="margin-bottom: 10px;">
            <label class="field-label">رقم التليفون</label>
            <input type="tel" name="contact_phone" value="{{ old('contact_phone', auth()->user()?->phone) }}" required minlength="8" maxlength="30" dir="ltr" style="text-align: right;" class="form-input" placeholder="01xxxxxxxxx">
        </div>

        <div>
            <label class="field-label">رقم واتساب <span style="color: var(--ink-4); font-weight: 600;">(اختياري)</span></label>
            <input type="tel" name="contact_whatsapp" value="{{ old('contact_whatsapp') }}" maxlength="30" dir="ltr" style="text-align: right;" class="form-input" placeholder="01xxxxxxxxx">
        </div>
    </div>

    <button type="submit" class="btn btn-teal btn-full" style="padding: 14px; font-size: 14px;">نشر المهمة</button>
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
    transition: border-color .15s ease, box-shadow .15s ease;
}
.form-input:focus {
    outline: none;
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(13,148,136,.14);
}
textarea.form-input { resize: vertical; min-height: 90px; line-height: 1.7; }
</style>
@endsection
