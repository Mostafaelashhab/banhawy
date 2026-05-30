<x-admin-layout title="إيصالات الدفع">

    @if(session('flash'))
        <div class="flash">{{ session('flash') }}</div>
    @endif

    <div style="display: flex; gap: 8px; margin-bottom: 16px;">
        <a href="{{ route('admin.receipts.index', ['status' => 'pending']) }}"
           class="a-chip @if($status==='pending') is-active @endif">
            بانتظار المراجعة ({{ $counts['pending'] }})
        </a>
        <a href="{{ route('admin.receipts.index', ['status' => 'approved']) }}"
           class="a-chip @if($status==='approved') is-active @endif">
            مفعّلة ({{ $counts['approved'] }})
        </a>
        <a href="{{ route('admin.receipts.index', ['status' => 'rejected']) }}"
           class="a-chip @if($status==='rejected') is-active @endif">
            مرفوضة ({{ $counts['rejected'] }})
        </a>
        <a href="{{ route('admin.receipts.index') }}" class="a-chip @if(!in_array($status, ['pending','approved','rejected'])) is-active @endif">
            الكل
        </a>
    </div>

    @if($receipts->isEmpty())
        <div class="a-card" style="text-align: center; padding: 40px;">
            <div style="color: var(--ink-3); font-size: 13px;">لا توجد إيصالات في هذه الحالة.</div>
        </div>
    @else
        <div style="display: grid; gap: 12px;">
            @foreach($receipts as $r)
                <div class="a-card" style="padding: 14px;">
                    <div style="display: grid; grid-template-columns: 120px 1fr auto; gap: 14px; align-items: start;">
                        <a href="{{ asset('storage/'.$r->receipt_path) }}" target="_blank" rel="noopener"
                           style="display: block; width: 120px; height: 120px; border-radius: 10px; overflow: hidden; background: #F4F5F7;">
                            <img src="{{ asset('storage/'.$r->receipt_path) }}" alt="إيصال"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </a>

                        <div style="min-width: 0;">
                            <div style="font-weight: 800; font-size: 15px; margin-bottom: 4px;">
                                {{ $r->business->name }}
                            </div>
                            <div style="color: var(--ink-3); font-size: 12px; margin-bottom: 8px;">
                                <strong>{{ $r->user->name }}</strong> · {{ $r->user->phone }}
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(2, auto); gap: 6px 18px; font-size: 12.5px;">
                                <div><span style="color: var(--ink-3);">الخطة:</span> <strong>{{ $r->plan->name }}</strong></div>
                                <div><span style="color: var(--ink-3);">المدة:</span> <strong>{{ $r->cycleLabel() }}</strong></div>
                                <div><span style="color: var(--ink-3);">المبلغ:</span> <strong>{{ number_format($r->amount) }} ج</strong></div>
                                <div><span style="color: var(--ink-3);">الطريقة:</span> <strong>{{ $r->methodLabel() }}</strong></div>
                                @if($r->reference_number)
                                    <div style="grid-column: 1 / -1;">
                                        <span style="color: var(--ink-3);">رقم العملية:</span>
                                        <strong style="direction: ltr; display: inline-block;">{{ $r->reference_number }}</strong>
                                    </div>
                                @endif
                                <div style="grid-column: 1 / -1; color: var(--ink-3); font-size: 11px;">
                                    أرسل: {{ $r->created_at->translatedFormat('d M Y · H:i') }}
                                </div>
                            </div>

                            @if($r->isApproved() && $r->reviewer)
                                <div style="margin-top: 8px; padding: 6px 10px; background: rgba(16,185,129,.10); color: #047857; border-radius: 8px; font-size: 11.5px; font-weight: 700;">
                                    وافق {{ $r->reviewer->name }} في {{ $r->reviewed_at?->translatedFormat('d M Y · H:i') }}
                                </div>
                            @elseif($r->isRejected())
                                <div style="margin-top: 8px; padding: 6px 10px; background: rgba(220,38,38,.10); color: #B91C1C; border-radius: 8px; font-size: 11.5px;">
                                    <strong>سبب الرفض:</strong> {{ $r->admin_note }}
                                    @if($r->reviewer)
                                        <span style="color: var(--ink-3); font-weight: 700;">— {{ $r->reviewer->name }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 6px; min-width: 130px;">
                            <span class="a-chip" style="text-align: center; font-weight: 800; @if($r->isApproved()) background: rgba(16,185,129,.14); color: #047857; @elseif($r->isRejected()) background: rgba(220,38,38,.14); color: #B91C1C; @else background: rgba(245,158,11,.14); color: #92400E; @endif">
                                {{ $r->statusLabel() }}
                            </span>

                            @if($r->isPending())
                                <form method="post" action="{{ route('admin.receipts.approve', $r) }}"
                                      onsubmit="return confirm('تأكيد الموافقة على هذا الإيصال وتفعيل الاشتراك؟')">
                                    @csrf
                                    <button type="submit" class="a-btn a-btn-primary" style="width: 100%; padding: 8px;">
                                        وافق وفعّل
                                    </button>
                                </form>

                                <button type="button" class="a-btn a-btn-danger" style="padding: 8px;"
                                        onclick="document.getElementById('reject-{{ $r->id }}').style.display='block'">
                                    رفض
                                </button>

                                <form id="reject-{{ $r->id }}" method="post" action="{{ route('admin.receipts.reject', $r) }}" style="display: none; margin-top: 6px;">
                                    @csrf
                                    <textarea name="admin_note" rows="2" required minlength="3" maxlength="500"
                                              placeholder="سبب الرفض..."
                                              style="width: 100%; padding: 6px; border: 1px solid var(--ink-5); border-radius: 6px; font-size: 12px; resize: vertical;"></textarea>
                                    <button type="submit" class="a-btn a-btn-danger" style="width: 100%; padding: 6px; margin-top: 4px; font-size: 11px;">
                                        تأكيد الرفض
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 16px;">
            {{ $receipts->links() }}
        </div>
    @endif

</x-admin-layout>
