<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentReceipt;
use App\Models\Subscription;
use App\Services\PushSender;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentReceiptController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status', 'pending');

        $receipts = PaymentReceipt::with(['business', 'user', 'plan', 'reviewer'])
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true),
                fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending'  => PaymentReceipt::where('status', 'pending')->count(),
            'approved' => PaymentReceipt::where('status', 'approved')->count(),
            'rejected' => PaymentReceipt::where('status', 'rejected')->count(),
        ];

        return view('admin.receipts.index', compact('receipts', 'status', 'counts'));
    }

    public function approve(Request $request, PaymentReceipt $receipt): RedirectResponse
    {
        abort_unless($receipt->isPending(), 409, 'هذا الإيصال تم مراجعته بالفعل.');

        DB::transaction(function () use ($receipt) {
            $business = $receipt->business;
            $now      = now();

            $monthsToAdd = $receipt->billing_cycle === 'yearly' ? 12 : 1;

            $activeSub = Subscription::where('business_id', $business->id)
                ->where('status', 'active')
                ->where(function ($q) use ($now) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                })
                ->latest()
                ->first();

            $startsAt = ($activeSub && $activeSub->plan_id === $receipt->plan_id && $activeSub->ends_at)
                ? $activeSub->ends_at
                : $now;
            $endsAt = (clone $startsAt)->addMonths($monthsToAdd);

            if ($activeSub && $activeSub->plan_id !== $receipt->plan_id) {
                $activeSub->update(['status' => 'cancelled', 'ends_at' => $now]);
            }

            Subscription::create([
                'business_id' => $business->id,
                'plan_id'     => $receipt->plan_id,
                'status'      => 'active',
                'starts_at'   => $startsAt,
                'ends_at'     => $endsAt,
                'amount'      => $receipt->amount,
            ]);

            $business->update(['plan_id' => $receipt->plan_id]);

            $receipt->update([
                'status'      => PaymentReceipt::STATUS_APPROVED,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => $now,
            ]);
        });

        $this->notifyMerchant($receipt, [
            'title' => '🎉 تم تفعيل اشتراكك!',
            'body'  => 'خطة '.$receipt->plan->name.' دلوقتي شغالة على '.$receipt->business->name,
            'url'   => route('merchant.dashboard'),
            'tag'   => 'merchant-receipt-approved-'.$receipt->id,
        ]);

        return back()->with('flash', 'تمت الموافقة وتفعيل الاشتراك ✓');
    }

    public function reject(Request $request, PaymentReceipt $receipt): RedirectResponse
    {
        abort_unless($receipt->isPending(), 409, 'هذا الإيصال تم مراجعته بالفعل.');

        $data = $request->validate([
            'admin_note' => ['required', 'string', 'min:3', 'max:500'],
        ], [], ['admin_note' => 'سبب الرفض']);

        $receipt->update([
            'status'      => PaymentReceipt::STATUS_REJECTED,
            'admin_note'  => $data['admin_note'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $this->notifyMerchant($receipt, [
            'title' => '❌ تم رفض إيصالك',
            'body'  => 'سبب الرفض: '.$data['admin_note'],
            'url'   => route('merchant.upgrade', ['plan' => $receipt->plan->slug, 'cycle' => $receipt->billing_cycle]),
            'tag'   => 'merchant-receipt-rejected-'.$receipt->id,
        ]);

        return back()->with('flash', 'تم رفض الإيصال ✓');
    }

    private function notifyMerchant(PaymentReceipt $receipt, array $payload): void
    {
        try {
            if ($receipt->user) {
                app(PushSender::class)->toUser($receipt->user, $payload);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[push merchant receipt] '.$e->getMessage());
        }
    }
}
