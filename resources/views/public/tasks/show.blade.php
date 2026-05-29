@extends('layouts.mobile')

@section('title', $task->title . ' · بنهاوي')
@section('page-title', 'تفاصيل المهمة')
@section('shell-class', 'no-bnav')

@section('content')
<div class="app-head">
    <a href="{{ route('tasks.index') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">تفاصيل المهمة</div>
</div>

@if(session('flash'))
    <div class="flash">{{ session('flash') }}</div>
@endif

<div style="padding: 14px;">

    <div class="card" style="padding: 16px;">
        <div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px;">
            <div style="flex: 1; min-width: 0;">
                <h2 style="margin: 0; font-size: 18px; font-weight: 900; line-height: 1.4;">{{ $task->title }}</h2>
                <div style="display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap;">
                    <span class="task-cat">{{ \App\Models\Task::CATEGORIES[$task->category] ?? $task->category }}</span>
                    @if($task->urgency === 'urgent')
                        <span class="task-urgency is-urgent">مستعجل</span>
                    @endif
                    @if($task->status !== 'open')
                        <span class="task-urgency" style="background: rgba(0,27,42,.06); color: var(--ink-3);">
                            {{ ['in_progress'=>'قيد التنفيذ', 'completed'=>'منتهية', 'cancelled'=>'ملغية'][$task->status] ?? $task->status }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <p style="font-size: 14px; line-height: 1.8; color: var(--ink-2); margin: 0 0 14px;">{{ $task->description }}</p>

        <div style="display: grid; gap: 10px; padding: 12px; background: #FAFBFC; border-radius: 11px;">
            @if($task->location)
                <div style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
                    <x-icon name="pin" :size="14" stroke="#0D9488"/>
                    <span style="font-weight: 700;">{{ $task->location }}</span>
                </div>
            @endif

            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
                <x-icon name="tag" :size="14" stroke="#0D9488"/>
                <span style="font-weight: 700;">
                    @if($task->budget)
                        الميزانية: {{ number_format($task->budget) }} ج
                    @else
                        الميزانية بالتفاوض
                    @endif
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
                <x-icon name="clock" :size="14" stroke="#0D9488"/>
                <span style="font-weight: 700;">نُشرت {{ $task->created_at?->diffForHumans() }}</span>
            </div>
        </div>
    </div>

    @if($task->status === 'open')
        <div class="card" style="padding: 14px; margin-top: 10px;">
            <div class="label-strong" style="margin-bottom: 10px;">تواصل مع {{ $task->contact_name }}</div>

            <div style="display: grid; gap: 8px;">
                <a href="tel:{{ $task->contact_phone }}" class="btn btn-line" style="padding: 12px; font-size: 13px; justify-content: center;">
                    <x-icon name="phone" :size="14" stroke="#0D9488"/>
                    اتصل · {{ $task->contact_phone }}
                </a>
                @php
                    $waNum = preg_replace('/[^\d]/', '', $task->contact_whatsapp ?: $task->contact_phone);
                    if (str_starts_with($waNum, '0') && strlen($waNum) === 11) $waNum = '20' . substr($waNum, 1);
                    $waMsg = rawurlencode("السلام عليكم، شفت مهمتك على بنهاوي: " . $task->title);
                @endphp
                <a href="https://wa.me/{{ $waNum }}?text={{ $waMsg }}" target="_blank" class="btn btn-wa" style="padding: 12px; font-size: 13px; justify-content: center;">
                    <x-icon name="whatsapp" :size="14" stroke="white"/>
                    تواصل واتساب
                </a>
            </div>
        </div>
    @endif

    @auth
        @if(auth()->id() === $task->user_id || auth()->user()->isAdmin())
            <form method="post" action="{{ route('tasks.close', $task) }}" style="margin-top: 10px;" onsubmit="return confirm('تأكيد إقفال المهمة؟')">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-line btn-full" style="padding: 11px; font-size: 13px;">
                    إقفال المهمة (تم إنجازها)
                </button>
            </form>
        @endif
    @endauth
</div>

<style>
.task-cat {
    background: rgba(13,148,136,.10);
    color: var(--teal);
    padding: 3px 9px;
    border-radius: 7px;
    font-size: 11.5px;
    font-weight: 800;
}
.task-urgency {
    padding: 3px 9px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 800;
}
.task-urgency.is-urgent { background: rgba(220,38,38,.10); color: #B91C1C; }
</style>
@endsection
