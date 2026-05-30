<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(Request $request, Business $business): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'body'   => ['nullable', 'string', 'max:1000'],
        ], [], [
            'rating' => 'التقييم',
            'body'   => 'التعليق',
        ]);

        $existing = Review::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->update([
                'rating' => $data['rating'],
                'body'   => $data['body'] ?? null,
            ]);
            $flash = 'تم تحديث تقييمك ✓';
        } else {
            Review::create([
                'business_id'    => $business->id,
                'user_id'        => $user->id,
                'reviewer_name'  => $user->name,
                'reviewer_phone' => $user->phone,
                'rating'         => $data['rating'],
                'body'           => $data['body'] ?? null,
            ]);
            $flash = 'تم إضافة تقييمك ✓';

            try {
                $stars = str_repeat('⭐', (int) $data['rating']);
                app(\App\Services\PushSender::class)->toAdmins([
                    'title' => '⭐ تقييم جديد لنشاط',
                    'body'  => $stars.' · '.$business->name.' · '.$user->name,
                    'url'   => route('admin.reviews.index'),
                    'tag'   => 'admin-review-'.$business->id.'-'.$user->id,
                ]);

                if ($business->owner_id && $business->owner_id !== $user->id) {
                    app(\App\Services\PushSender::class)->toUser($business->owner, [
                        'title' => $stars.' تقييم جديد على نشاطك',
                        'body'  => $user->name.' كتب: '.mb_substr($data['body'] ?? '—', 0, 100),
                        'url'   => route('business.show', $business).'#biz-reviews',
                        'tag'   => 'owner-review-'.$business->id.'-'.$user->id,
                    ]);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[push review] '.$e->getMessage());
            }
        }

        $this->recomputeAggregates($business);

        return redirect()
            ->route('business.show', $business)
            ->withFragment('biz-reviews')
            ->with('flash', $flash);
    }

    public function destroy(Request $request, Business $business, Review $review): RedirectResponse
    {
        abort_unless($review->business_id === $business->id, 404);
        abort_unless($review->user_id === $request->user()->id, 403);

        $review->delete();
        $this->recomputeAggregates($business);

        return redirect()
            ->route('business.show', $business)
            ->withFragment('biz-reviews')
            ->with('flash', 'تم حذف تقييمك ✓');
    }

    private function recomputeAggregates(Business $business): void
    {
        $stats = DB::table('reviews')
            ->where('business_id', $business->id)
            ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')
            ->first();

        $business->update([
            'reviews_count' => (int) ($stats->cnt ?? 0),
            'rating'        => $stats && $stats->cnt > 0 ? round((float) $stats->avg_rating, 2) : 0,
        ]);
    }
}
