<x-admin-layout title="المهام">

    <div class="a-filters">
        @foreach(['open' => 'مفتوحة', 'in_progress' => 'قيد التنفيذ', 'completed' => 'منتهية', 'cancelled' => 'ملغية', 'all' => 'الكل'] as $key => $label)
            <a href="{{ route('admin.tasks.index', ['status' => $key]) }}" class="a-chip @if($status === $key) is-active @endif">{{ $label }}</a>
        @endforeach
    </div>

    <form method="get" class="a-card" style="margin-bottom: 16px;">
        <input type="hidden" name="status" value="{{ $status }}">
        <div style="display: grid; grid-template-columns: 1fr 200px auto; gap: 10px; align-items: end;">
            <div class="a-form-row" style="margin: 0;">
                <label>بحث</label>
                <input type="search" name="q" value="{{ $q }}" placeholder="عنوان، تليفون، تفاصيل...">
            </div>
            <div class="a-form-row" style="margin: 0;">
                <label>التصنيف</label>
                <select name="category">
                    <option value="">الكل</option>
                    @foreach(\App\Models\Task::CATEGORIES as $k => $v)
                        <option value="{{ $k }}" @selected($category === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <button class="a-btn a-btn-primary" style="padding: 10px 18px;">تصفية</button>
        </div>
    </form>

    <div class="a-table-wrap">
        <table class="a-table">
            <thead>
                <tr>
                    <th>العنوان</th>
                    <th>التصنيف</th>
                    <th>المنشِر</th>
                    <th>تليفون</th>
                    <th>ميزانية</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th style="text-align: left;">إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    <tr>
                        <td>
                            <a href="{{ route('tasks.show', $task) }}" target="_blank" style="color: var(--ink-1); font-weight: 800; text-decoration: none;">
                                {{ Str::limit($task->title, 50) }}
                            </a>
                            @if($task->urgency === 'urgent')
                                <span class="a-pill a-pill-red" style="margin-right: 4px;">مستعجل</span>
                            @endif
                        </td>
                        <td>{{ \App\Models\Task::CATEGORIES[$task->category] ?? $task->category }}</td>
                        <td>{{ $task->user?->name ?? $task->contact_name }}</td>
                        <td style="direction: ltr; text-align: right; font-family: ui-monospace, monospace; font-size: 11.5px;">{{ $task->contact_phone }}</td>
                        <td>{{ $task->budget ? number_format($task->budget) . ' ج' : '—' }}</td>
                        <td>
                            @switch($task->status)
                                @case('open')        <span class="a-pill a-pill-teal">مفتوحة</span> @break
                                @case('in_progress') <span class="a-pill a-pill-blue">قيد التنفيذ</span> @break
                                @case('completed')   <span class="a-pill a-pill-green">منتهية</span> @break
                                @case('cancelled')   <span class="a-pill a-pill-gray">ملغية</span> @break
                            @endswitch
                        </td>
                        <td>{{ $task->created_at?->diffForHumans() }}</td>
                        <td style="text-align: left;">
                            <form method="post" action="{{ route('admin.tasks.update', $task) }}" style="display: inline;">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 7px; border: 1px solid var(--line); font-family: inherit; font-size: 11.5px; font-weight: 800;">
                                    <option value="open"        @selected($task->status === 'open')>مفتوحة</option>
                                    <option value="in_progress" @selected($task->status === 'in_progress')>قيد التنفيذ</option>
                                    <option value="completed"   @selected($task->status === 'completed')>منتهية</option>
                                    <option value="cancelled"   @selected($task->status === 'cancelled')>ملغية</option>
                                </select>
                            </form>
                            <form method="post" action="{{ route('admin.tasks.destroy', $task) }}" onsubmit="return confirm('حذف نهائي للمهمة؟')" style="display: inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="a-btn a-btn-line a-btn-sm" style="color: #B91C1C;">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="a-empty">مفيش مهام بهذه الفلاتر</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $tasks->links() }}

</x-admin-layout>
