@extends('layouts.mobile')

@section('title', 'المهام · بنهاوي')
@section('page-title', 'المهام')

@section('content')
<div class="app-head">
    <a href="{{ route('home') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">لوحة المهام</div>
    <a href="{{ route('tasks.create') }}" class="ico-btn" aria-label="نشر مهمة">
        <x-icon name="plus" :size="18"/>
    </a>
</div>

@if(session('flash'))
    <div class="flash">{{ session('flash') }}</div>
@endif

<form method="get" id="tasks-form" autocomplete="off" style="padding: 10px 14px;">
    <label class="field" style="margin-bottom: 8px;">
        <span style="color: var(--ink-4);"><x-icon name="search" :size="16"/></span>
        <input type="search" name="q" id="tasks-search" value="{{ $q }}" placeholder="ابحث في المهام..." inputmode="search">
        <span class="tiny" id="tasks-count" style="color: var(--ink-3);"></span>
    </label>

    <div class="chip-scroll-wrap" style="margin: 6px -14px 0;">
        <div class="chip-scroll" style="padding: 0 14px;">
            <a href="{{ route('tasks.index') }}" class="chip @if(! $category) active @endif">الكل</a>
            @foreach(\App\Models\Task::CATEGORIES as $key => $label)
                <a href="{{ route('tasks.index', ['category' => $key]) }}" class="chip @if($category === $key) active @endif">{{ $label }}</a>
            @endforeach
        </div>
    </div>
</form>

<div class="scroll" style="padding: 4px 14px 24px;">
    @forelse($tasks as $task)
        @php
            $isOpen = $task->status === 'open';
            $hay = mb_strtolower($task->title . ' ' . $task->description . ' ' . ($task->location ?? '') . ' ' . (\App\Models\Task::CATEGORIES[$task->category] ?? ''));
        @endphp
        @if($isOpen)
            <a href="{{ route('tasks.show', $task) }}" class="card task-card task-item" data-haystack="{{ $hay }}">
        @else
            {{-- Closed tasks: render as static div (not clickable) so seed/history doesn't dilute the UX --}}
            <div class="card task-card is-closed task-item" data-haystack="{{ $hay }}" aria-disabled="true">
        @endif
            <div class="task-head">
                <div style="flex: 1; min-width: 0;">
                    <div class="task-title">{{ $task->title }}</div>
                    <div class="task-meta">
                        <span class="task-cat">{{ \App\Models\Task::CATEGORIES[$task->category] ?? $task->category }}</span>
                        @if($task->location)
                            <span class="task-dot"></span>
                            <span class="task-loc">
                                <x-icon name="pin" :size="11" stroke="#5E6A77"/>
                                {{ $task->location }}
                            </span>
                        @endif
                    </div>
                </div>
                @if(! $isOpen)
                    <span class="task-urgency is-done">
                        @switch($task->status)
                            @case('completed')  منتهية ✓ @break
                            @case('cancelled')  ملغية @break
                            @case('in_progress') قيد التنفيذ @break
                        @endswitch
                    </span>
                @elseif($task->urgency === 'urgent')
                    <span class="task-urgency is-urgent">مستعجل</span>
                @elseif($task->urgency === 'low')
                    <span class="task-urgency is-low">مش مستعجل</span>
                @endif
            </div>

            <p class="task-desc">{{ Str::limit($task->description, 140) }}</p>

            <div class="task-foot">
                @if($task->budget)
                    <span class="task-budget">{{ number_format($task->budget) }} ج</span>
                @else
                    <span class="task-budget muted">الميزانية بالتفاوض</span>
                @endif
                <span class="task-time">{{ $task->created_at?->diffForHumans() }}</span>
            </div>
        @if($isOpen)
            </a>
        @else
            </div>
        @endif
    @empty
        <div style="text-align: center; padding: 40px 16px;">
            <div style="width: 80px; height: 80px; border-radius: 24px; background: rgba(13,148,136,.08); display: grid; place-items: center; margin: 0 auto 12px;">
                <x-icon name="task" :size="38" stroke="#0D9488"/>
            </div>
            <div class="label-strong" style="font-size: 16px;">مفيش مهام مفتوحة دلوقتي</div>
            <p class="muted" style="margin-top: 6px;">كن أول من ينشر مهمة — هتلاقي من يساعدك.</p>
            <a href="{{ route('tasks.create') }}" class="btn btn-teal" style="padding: 11px 22px; font-size: 13px; margin-top: 16px;">
                <x-icon name="plus" :size="14" stroke="white"/>
                انشر مهمة
            </a>
        </div>
    @endforelse

    {{-- Realtime-search empty state --}}
    <div id="tasks-empty" hidden style="text-align: center; padding: 30px 16px;">
        <div style="width: 70px; height: 70px; border-radius: 20px; background: rgba(0,27,42,.06); display: grid; place-items: center; margin: 0 auto 10px; color: var(--ink-4);">
            <x-icon name="search" :size="28"/>
        </div>
        <div class="label-strong">مفيش نتائج تطابق بحثك</div>
        <p class="muted" style="margin-top: 4px; font-size: 12px;">جرّب كلمة تانية.</p>
    </div>

    @if($tasks->hasPages())
        <div style="margin-top: 14px;">{{ $tasks->links() }}</div>
    @endif
</div>

<style>
.task-card {
    display: block;
    padding: 14px;
    margin-bottom: 10px;
    text-decoration: none;
    color: var(--ink-1);
    transition: transform .12s ease, box-shadow .15s ease;
}
.task-card:active { transform: scale(.99); }
.task-head { display: flex; align-items: flex-start; gap: 10px; }
.task-title { font-weight: 900; font-size: 14.5px; line-height: 1.4; }
.task-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 6px;
    font-size: 11.5px;
    color: var(--ink-3);
    font-weight: 700;
    flex-wrap: wrap;
}
.task-cat {
    background: rgba(13,148,136,.10);
    color: var(--teal);
    padding: 2px 8px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 800;
}
.task-dot { width: 3px; height: 3px; background: var(--ink-4); border-radius: 50%; }
.task-loc { display: inline-flex; align-items: center; gap: 3px; }
.task-urgency {
    padding: 3px 8px;
    border-radius: 7px;
    font-size: 10.5px;
    font-weight: 800;
    flex-shrink: 0;
}
.task-urgency.is-urgent { background: rgba(220,38,38,.10); color: #B91C1C; }
.task-urgency.is-low    { background: rgba(0,27,42,.06); color: var(--ink-3); }
.task-urgency.is-done   { background: rgba(16,185,129,.14); color: #047857; }

/* Closed/finished tasks: visible but inert — clearly not actionable */
.task-card.is-closed {
    opacity: 0.7;
    background: #FAFBFC;
    cursor: default;
    pointer-events: none;
}
.task-card.is-closed .task-title { color: var(--ink-3); }
.task-card.is-closed .task-budget,
.task-card.is-closed .task-cat {
    filter: grayscale(.35);
}
.task-desc {
    margin: 8px 0 10px;
    font-size: 12.5px;
    line-height: 1.65;
    color: var(--ink-2);
}
.task-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 10px;
    border-top: 1px dashed var(--line);
    font-size: 11.5px;
    font-weight: 700;
}
.task-budget { color: #047857; font-weight: 900; font-size: 13px; }
.task-budget.muted { color: var(--ink-4); font-weight: 700; font-size: 11.5px; }
.task-time { color: var(--ink-4); }
</style>

<script>
(function () {
    var input    = document.getElementById('tasks-search');
    var form     = document.getElementById('tasks-form');
    var items    = Array.from(document.querySelectorAll('.task-item'));
    var emptyEl  = document.getElementById('tasks-empty');
    var count    = document.getElementById('tasks-count');
    if (!input) return;

    form.addEventListener('submit', function (e) { e.preventDefault(); });

    function normalise(s) {
        return (s || '').toString().trim().toLowerCase()
            .replace(/[ً-ْٰ]/g, '')
            .replace(/[إأآا]/g, 'ا')
            .replace(/ى/g, 'ي')
            .replace(/ة/g, 'ه');
    }

    function apply() {
        var q = normalise(input.value);
        var shown = 0;
        if (q === '') {
            items.forEach(function (el) { el.hidden = false; });
            shown = items.length;
        } else {
            items.forEach(function (el) {
                var match = normalise(el.dataset.haystack).indexOf(q) !== -1;
                el.hidden = !match;
                if (match) shown++;
            });
        }
        if (count) count.textContent = q ? (shown + ' نتيجة') : '';
        if (emptyEl) emptyEl.hidden = !(q && shown === 0);
    }

    var t = null;
    input.addEventListener('input', function () {
        apply();
        clearTimeout(t);
        t = setTimeout(function () {
            var url = new URL(window.location.href);
            if (input.value.trim() === '') url.searchParams.delete('q');
            else url.searchParams.set('q', input.value);
            window.history.replaceState(null, '', url);
        }, 250);
    });

    if (input.value) apply();
})();
</script>

@include('partials.visitor-nav')
@endsection
