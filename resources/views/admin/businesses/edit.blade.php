<x-admin-layout title="تحرير: {{ $business->name }}">

    <div style="margin-bottom: 14px;">
        <a href="{{ route('admin.businesses.index') }}" class="a-btn a-btn-line a-btn-sm">← رجوع للقائمة</a>
        <a href="{{ route('business.show', $business) }}" target="_blank" class="a-btn a-btn-line a-btn-sm">عرض في الموقع</a>
    </div>

    <form method="post" action="{{ route('admin.businesses.update', $business) }}">
        @csrf @method('PATCH')

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
            <div class="a-card">
                <div class="a-card-title" style="margin-bottom: 14px;">معلومات أساسية</div>

                <div class="a-form-row">
                    <label>الاسم</label>
                    <input type="text" name="name" value="{{ old('name', $business->name) }}" required>
                    @error('name')<div style="color: #B91C1C; font-size: 12px;">{{ $message }}</div>@enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="a-form-row">
                        <label>النوع</label>
                        <select name="business_type_id" required>
                            @foreach($types as $t)
                                <option value="{{ $t->id }}" @selected($business->business_type_id === $t->id)>{{ $t->name_ar }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="a-form-row">
                        <label>التصنيف</label>
                        <input type="text" name="category" value="{{ old('category', $business->category) }}">
                    </div>
                </div>

                <div class="a-form-row">
                    <label>وصف</label>
                    <textarea name="description" rows="3">{{ old('description', $business->description) }}</textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                    <div class="a-form-row">
                        <label>تليفون</label>
                        <input type="text" name="phone" value="{{ old('phone', $business->phone) }}">
                    </div>
                    <div class="a-form-row">
                        <label>واتساب</label>
                        <input type="text" name="whatsapp" value="{{ old('whatsapp', $business->whatsapp) }}" required>
                    </div>
                    <div class="a-form-row">
                        <label>الإيميل</label>
                        <input type="email" name="email" value="{{ old('email', $business->email) }}">
                    </div>
                </div>

                <div class="a-form-row">
                    <label>العنوان</label>
                    <input type="text" name="address" value="{{ old('address', $business->address) }}" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                    <div class="a-form-row">
                        <label>خط العرض (lat)</label>
                        <input type="text" name="lat" value="{{ old('lat', $business->lat) }}" required>
                    </div>
                    <div class="a-form-row">
                        <label>خط الطول (lng)</label>
                        <input type="text" name="lng" value="{{ old('lng', $business->lng) }}" required>
                    </div>
                    <div class="a-form-row">
                        <label>الفئة السعرية</label>
                        <select name="price_range">
                            <option value="low"    @selected($business->price_range === 'low')>اقتصادية</option>
                            <option value="medium" @selected($business->price_range === 'medium')>متوسطة</option>
                            <option value="high"   @selected($business->price_range === 'high')>مرتفعة</option>
                        </select>
                    </div>
                </div>

                <div class="a-form-actions">
                    <button type="submit" class="a-btn a-btn-teal">حفظ التغييرات</button>
                </div>
            </div>

            <div class="a-card">
                <div class="a-card-title" style="margin-bottom: 14px;">الحالة</div>

                <label style="display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid var(--line); border-radius: 10px; margin-bottom: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" @checked($business->is_active)>
                    <div>
                        <div style="font-weight: 800; font-size: 13px;">نشط</div>
                        <div style="font-size: 11px; color: var(--ink-3);">يظهر في الدليل والبحث</div>
                    </div>
                </label>
                <label style="display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid var(--line); border-radius: 10px; margin-bottom: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_verified" value="1" @checked($business->is_verified)>
                    <div>
                        <div style="font-weight: 800; font-size: 13px;">موثّق</div>
                        <div style="font-size: 11px; color: var(--ink-3);">علامة ✓ بجانب الاسم</div>
                    </div>
                </label>
                <label style="display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid var(--line); border-radius: 10px; cursor: pointer;">
                    <input type="checkbox" name="is_featured" value="1" @checked($business->is_featured)>
                    <div>
                        <div style="font-weight: 800; font-size: 13px;">مميّز</div>
                        <div style="font-size: 11px; color: var(--ink-3);">يظهر في الأقسام المميزة</div>
                    </div>
                </label>

                <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--line);">
                    <div style="font-size: 11px; color: var(--ink-3); font-weight: 800; margin-bottom: 6px;">المالك</div>
                    @if($business->owner)
                        <div style="font-weight: 800;">{{ $business->owner->name }}</div>
                        <div style="font-size: 12px; color: var(--ink-3);">{{ $business->owner->email }}</div>
                    @else
                        <span class="a-pill a-pill-amber">بدون مالك</span>
                    @endif
                </div>

                <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--line); font-size: 12px; color: var(--ink-3);">
                    <div>الزيارات: <strong>{{ number_format($business->views_count) }}</strong></div>
                    <div>ضغطات واتساب: <strong>{{ number_format($business->whatsapp_clicks) }}</strong></div>
                    <div>تقييمات: <strong>{{ $business->reviews_count }}</strong> (★ {{ number_format($business->rating, 1) }})</div>
                    <div>أضيف: <strong>{{ $business->created_at?->format('Y-m-d') }}</strong></div>
                </div>
            </div>
        </div>
    </form>

    <div class="a-card" style="margin-top: 16px; border-color: rgba(220,38,38,.30);">
        <div class="a-card-title" style="color: #B91C1C; margin-bottom: 8px;">منطقة خطر</div>
        <p style="font-size: 12.5px; color: var(--ink-3); margin-bottom: 12px;">حذف المتجر هيمسح كل تقييماته وطلباته. الإجراء لا يمكن التراجع عنه.</p>
        <form method="post" action="{{ route('admin.businesses.destroy', $business) }}" onsubmit="return confirm('متأكد إنك عاوز تحذف هذا المتجر نهائياً؟')">
            @csrf @method('DELETE')
            <button type="submit" class="a-btn a-btn-danger">حذف المتجر</button>
        </form>
    </div>

</x-admin-layout>
