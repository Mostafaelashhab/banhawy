<x-admin-layout title="المستخدمين">

    <form method="get" class="a-card" style="margin-bottom: 16px;">
        <div style="display: grid; grid-template-columns: 1fr 200px auto; gap: 10px; align-items: end;">
            <div class="a-form-row" style="margin: 0;">
                <label>بحث</label>
                <input type="search" name="q" value="{{ $q }}" placeholder="اسم، إيميل، تليفون...">
            </div>
            <div class="a-form-row" style="margin: 0;">
                <label>الصلاحية</label>
                <select name="role">
                    <option value="all"      @selected($role === 'all')>الكل</option>
                    <option value="admin"    @selected($role === 'admin')>أدمن</option>
                    <option value="owner"    @selected($role === 'owner')>تاجر</option>
                    <option value="customer" @selected($role === 'customer')>عميل</option>
                </select>
            </div>
            <button class="a-btn a-btn-primary" style="padding: 10px 18px;">بحث</button>
        </div>
    </form>

    <div class="a-table-wrap">
        <table class="a-table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>الإيميل</th>
                    <th>التليفون</th>
                    <th>الصلاحية</th>
                    <th>المتاجر</th>
                    <th>سجّل</th>
                    <th style="text-align: left;">إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td><span style="font-weight: 800;">{{ $u->name }}</span></td>
                        <td style="direction: ltr; text-align: right; font-size: 12px;">{{ $u->email }}</td>
                        <td style="direction: ltr; text-align: right; font-family: ui-monospace, monospace;">{{ $u->phone ?? '—' }}</td>
                        <td>
                            <form method="post" action="{{ route('admin.users.role', $u) }}" style="display: inline;">
                                @csrf @method('PATCH')
                                <select name="role" onchange="if(confirm('تأكيد تغيير الصلاحية لـ {{ $u->name }}؟')) this.form.submit()" style="padding: 5px 8px; border-radius: 7px; border: 1px solid var(--line); font-family: inherit; font-size: 11.5px; font-weight: 800;">
                                    <option value="customer" @selected($u->role === 'customer')>عميل</option>
                                    <option value="owner"    @selected($u->role === 'owner')>تاجر</option>
                                    <option value="admin"    @selected($u->role === 'admin')>أدمن</option>
                                </select>
                            </form>
                        </td>
                        <td>{{ $u->businesses_count }}</td>
                        <td>{{ $u->created_at?->diffForHumans() }}</td>
                        <td style="text-align: left;">
                            @if($u->id !== auth()->id())
                                <form method="post" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('تأكيد حذف الحساب نهائياً؟')" style="display: inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="a-btn a-btn-line a-btn-sm" style="color: #B91C1C;">حذف</button>
                                </form>
                            @else
                                <span class="a-pill a-pill-teal">أنت</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="a-empty">مفيش مستخدمين</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}

</x-admin-layout>
