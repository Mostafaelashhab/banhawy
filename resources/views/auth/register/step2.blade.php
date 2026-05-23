@extends('layouts.mobile')

@section('title', 'نوع النشاط · بنهاوي')
@section('screen-class', 'bg-gray')

@section('content')
<div class="app-head">
    <a href="{{ route('register.step1') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">نوع النشاط</div>
</div>

@include('partials.wizard-progress', ['step' => 2])

<form method="post" action="{{ route('register.step2.store') }}" id="type-form" style="flex: 1; display: flex; flex-direction: column;">
    @csrf
    <div style="padding: 0 18px 6px;">
        <h2 style="font-size: 18px; font-weight: 900; color: var(--navy);">نشاطك من أي نوع؟</h2>
        <p class="tiny" style="color: var(--ink-3); margin-top: 4px; line-height: 1.6;">اختار نوع نشاطك عشان نجهزلك أنسب قالب لموقعك.</p>
    </div>

    <div class="scroll" style="padding: 10px 14px 14px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            @foreach($types as $t)
                <label data-type-card style="cursor: pointer;">
                    <input type="radio" name="business_type_id" value="{{ $t->id }}" style="position: absolute; opacity: 0; pointer-events: none;" @if($loop->first) checked @endif>
                    <div class="type-card" data-card style="background: white; border: 1px solid var(--line); border-radius: 16px; padding: 16px 10px; display: flex; flex-direction: column; align-items: center; gap: 8px; transition: all .15s;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: var(--gray-100); display: grid; place-items: center; color: var(--navy);">
                            <x-icon :name="$t->icon" :size="22" stroke="#001B2A"/>
                        </div>
                        <div style="font-weight: 800; font-size: 12px;">{{ $t->name_ar }}</div>
                        <span class="label-meta" style="text-align: center;">{{ $t->description_ar }}</span>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    <div class="cta-bar" style="position: sticky; bottom: 0;">
        <button type="submit" class="btn btn-navy btn-full" style="padding: 13px; font-size: 14px;">
            التالي <x-icon name="arrow-l" :size="14" stroke="white" w="2.4"/>
        </button>
    </div>
</form>

<style>
[data-type-card] input:checked + .type-card {
    border-color: var(--teal); border-width: 2px;
    box-shadow: 0 0 0 4px rgba(13,148,136,.08);
}
[data-type-card] input:checked + .type-card > div:first-child {
    background: var(--teal-50); color: var(--teal);
}
[data-type-card] input:checked + .type-card > div:first-child svg { stroke: var(--teal); }
</style>
@endsection
