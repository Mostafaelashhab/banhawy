<x-admin-layout title="الرئيسية">

    {{-- Stats grid --}}
    <div class="a-stats">
        <div class="a-stat">
            <div class="a-stat-icon" style="background: rgba(13,148,136,.12); color: var(--teal);">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l1.5-5h15L21 9"/><path d="M3 9v11h18V9"/></svg>
            </div>
            <div class="a-stat-label">المتاجر</div>
            <div class="a-stat-value">{{ number_format($stats['businesses_total']) }}</div>
            <div class="a-stat-meta">{{ $stats['businesses_active'] }} نشط · {{ $stats['businesses_unclaimed'] }} بدون مالك</div>
        </div>
        <div class="a-stat">
            <div class="a-stat-icon" style="background: rgba(14,165,233,.12); color: #0369A1;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div class="a-stat-label">المستخدمين</div>
            <div class="a-stat-value">{{ number_format($stats['users_total']) }}</div>
            <div class="a-stat-meta">{{ $stats['users_owners'] }} تاجر · {{ $stats['users_admins'] }} أدمن</div>
        </div>
        <div class="a-stat">
            <div class="a-stat-icon" style="background: rgba(245,158,11,.15); color: #B45309;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <div class="a-stat-label">التقييمات</div>
            <div class="a-stat-value">{{ number_format($stats['reviews_total']) }}</div>
            <div class="a-stat-meta">إجمالي التقييمات</div>
        </div>
        <div class="a-stat">
            <div class="a-stat-icon" style="background: rgba(220,38,38,.12); color: #B91C1C;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22V4a3 3 0 0 1 3-3h11l-3 5 3 5H7"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
            </div>
            <div class="a-stat-label">بانتظار المراجعة</div>
            <div class="a-stat-value">{{ $stats['claims_pending'] + $stats['reports_pending'] }}</div>
            <div class="a-stat-meta">{{ $stats['claims_pending'] }} ملكية · {{ $stats['reports_pending'] }} بلاغ</div>
        </div>
        <div class="a-stat">
            <div class="a-stat-icon" style="background: rgba(13,148,136,.10); color: var(--teal);">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><polyline points="8 12 11 15 16 9"/></svg>
            </div>
            <div class="a-stat-label">المهام</div>
            <div class="a-stat-value">{{ number_format($stats['tasks_total']) }}</div>
            <div class="a-stat-meta">{{ $stats['tasks_open'] }} مفتوحة الآن</div>
        </div>
        <div class="a-stat">
            <div class="a-stat-icon" style="background: rgba(124,58,237,.12); color: #7C3AED;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="3"/><path d="M11 22s7-6 7-12a7 7 0 1 0-14 0c0 6 7 12 7 12z"/></svg>
            </div>
            <div class="a-stat-label">المفقودات</div>
            <div class="a-stat-value">{{ number_format($stats['lost_total']) }}</div>
            <div class="a-stat-meta">{{ $stats['lost_open'] }} مفتوحة الآن</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        {{-- Pending claims --}}
        <div class="a-card">
            <div class="a-card-head">
                <div class="a-card-title">طلبات ملكية بانتظار المراجعة</div>
                <a href="{{ route('admin.claims.index') }}" class="a-btn a-btn-line a-btn-sm">عرض الكل</a>
            </div>
            @forelse($recentClaims as $c)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--line);">
                    <div>
                        <div style="font-weight: 800; font-size: 13px;">{{ $c->claimant_name }}</div>
                        <div style="font-size: 12px; color: var(--ink-3); font-weight: 600;">{{ $c->business?->name ?? '—' }}</div>
                    </div>
                    <a href="{{ route('admin.claims.show', $c) }}" class="a-btn a-btn-line a-btn-sm">مراجعة</a>
                </div>
            @empty
                <div class="a-empty">مفيش طلبات معلّقة ✓</div>
            @endforelse
        </div>

        {{-- Pending reports --}}
        <div class="a-card">
            <div class="a-card-head">
                <div class="a-card-title">بلاغات بانتظار المراجعة</div>
                <a href="{{ route('admin.reports.index') }}" class="a-btn a-btn-line a-btn-sm">عرض الكل</a>
            </div>
            @forelse($recentReports as $r)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--line);">
                    <div>
                        <div style="font-weight: 800; font-size: 13px;">{{ \App\Models\BusinessReport::REASONS[$r->reason] ?? $r->reason }}</div>
                        <div style="font-size: 12px; color: var(--ink-3); font-weight: 600;">{{ $r->business?->name ?? '—' }}</div>
                    </div>
                    <a href="{{ route('admin.reports.show', $r) }}" class="a-btn a-btn-line a-btn-sm">مراجعة</a>
                </div>
            @empty
                <div class="a-empty">مفيش بلاغات معلّقة ✓</div>
            @endforelse
        </div>
    </div>

    <div class="a-card" style="margin-top: 16px;">
        <div class="a-card-head">
            <div class="a-card-title">آخر متاجر مضافة</div>
            <a href="{{ route('admin.businesses.index') }}" class="a-btn a-btn-line a-btn-sm">عرض الكل</a>
        </div>
        <table class="a-table">
            <thead>
                <tr><th>الاسم</th><th>النوع</th><th>الحالة</th><th>التقييم</th><th>أضيف</th></tr>
            </thead>
            <tbody>
                @foreach($recentBusinesses as $b)
                    <tr>
                        <td>
                            <a href="{{ route('admin.businesses.edit', $b) }}" style="color: var(--ink-1); font-weight: 800; text-decoration: none;">{{ $b->name }}</a>
                        </td>
                        <td>{{ $b->type?->name_ar ?? '—' }}</td>
                        <td>
                            @if($b->is_active)
                                <span class="a-pill a-pill-green">نشط</span>
                            @else
                                <span class="a-pill a-pill-gray">معطّل</span>
                            @endif
                            @if(! $b->owner_id)
                                <span class="a-pill a-pill-amber">بدون مالك</span>
                            @endif
                        </td>
                        <td>★ {{ number_format($b->rating, 1) }} ({{ $b->reviews_count }})</td>
                        <td>{{ $b->created_at?->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Community stats: tasks + lost items ─────────────────── --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px;">
        <div class="a-card">
            <div class="a-card-head">
                <div class="a-card-title">آخر المهام · {{ $stats['tasks_open'] }} مفتوحة</div>
                <a href="{{ route('admin.tasks.index') }}" class="a-btn a-btn-line a-btn-sm">عرض الكل</a>
            </div>
            @forelse($recentTasks as $t)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--line);">
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-weight: 800; font-size: 13px;">{{ Str::limit($t->title, 40) }}</div>
                        <div style="font-size: 11px; color: var(--ink-3); font-weight: 700; margin-top: 2px;">
                            {{ \App\Models\Task::CATEGORIES[$t->category] ?? '—' }} ·
                            {{ $t->contact_name }} ·
                            {{ $t->created_at?->diffForHumans() }}
                        </div>
                    </div>
                    @switch($t->status)
                        @case('open')        <span class="a-pill a-pill-teal">مفتوحة</span> @break
                        @case('in_progress') <span class="a-pill a-pill-blue">قيد التنفيذ</span> @break
                        @case('completed')   <span class="a-pill a-pill-green">منتهية</span> @break
                        @case('cancelled')   <span class="a-pill a-pill-gray">ملغية</span> @break
                    @endswitch
                </div>
            @empty
                <div class="a-empty">لسه مفيش مهام منشورة.</div>
            @endforelse
        </div>

        <div class="a-card">
            <div class="a-card-head">
                <div class="a-card-title">آخر المفقودات · {{ $stats['lost_open'] }} مفتوحة</div>
                <a href="{{ route('admin.lost.index') }}" class="a-btn a-btn-line a-btn-sm">عرض الكل</a>
            </div>
            @forelse($recentLost as $l)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--line); gap: 10px;">
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-weight: 800; font-size: 13px;">{{ Str::limit($l->title, 38) }}</div>
                        <div style="font-size: 11px; color: var(--ink-3); font-weight: 700; margin-top: 2px;">
                            {{ \App\Models\LostItem::CATEGORIES[$l->category] ?? '—' }} ·
                            {{ $l->created_at?->diffForHumans() }}
                        </div>
                    </div>
                    @if($l->kind === 'lost')
                        <span class="a-pill a-pill-red">ضايع</span>
                    @else
                        <span class="a-pill a-pill-green">موجود</span>
                    @endif
                </div>
            @empty
                <div class="a-empty">لسه مفيش بلاغات مفقودات.</div>
            @endforelse
        </div>
    </div>

</x-admin-layout>
