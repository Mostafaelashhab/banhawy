<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LostItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LostItemController extends Controller
{
    public function index(Request $request): View
    {
        $status   = $request->input('status', 'open');
        $kind     = $request->input('kind');
        $category = $request->input('category');
        $q        = $request->input('q');

        $items = LostItem::with('user')
            ->when($status !== 'all', fn ($qb) => $qb->where('status', $status))
            ->when($kind, fn ($qb) => $qb->where('kind', $kind))
            ->when($category, fn ($qb) => $qb->where('category', $category))
            ->when($q, fn ($qb) => $qb->where(fn ($w) =>
                $w->where('title', 'like', "%$q%")
                  ->orWhere('description', 'like', "%$q%")
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.lost.index', compact('items', 'status', 'kind', 'category', 'q'));
    }

    public function update(Request $request, LostItem $lost): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'resolved', 'expired'])],
        ]);

        $lost->update([
            'status'      => $data['status'],
            'resolved_at' => $data['status'] === 'resolved' ? now() : null,
        ]);

        return back()->with('flash', 'تم التحديث ✓');
    }

    public function destroy(LostItem $lost): RedirectResponse
    {
        $lost->delete();
        return back()->with('flash', 'تم حذف البلاغ ✓');
    }
}
