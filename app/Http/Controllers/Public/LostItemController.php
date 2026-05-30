<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LostItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LostItemController extends Controller
{
    public function index(Request $request): View
    {
        $kind     = $request->input('kind');           // null | lost | found
        $category = $request->input('category');
        $q        = $request->input('q');

        // Server returns everything for the chosen kind/category — text filter
        // is handled client-side in realtime, so clearing the search restores
        // results instantly without a reload.
        $items = LostItem::whereIn('status', ['open', 'resolved', 'expired'])
            ->when($kind, fn ($qb) => $qb->where('kind', $kind))
            ->when($category, fn ($qb) => $qb->where('category', $category))
            ->orderByRaw("CASE status WHEN 'open' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(40)
            ->withQueryString();

        return view('public.lost.index', compact('items', 'kind', 'category', 'q'));
    }

    public function create(Request $request): View
    {
        $kind = $request->input('kind', 'lost');
        return view('public.lost.create', compact('kind'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kind'          => ['required', Rule::in(array_keys(LostItem::KINDS))],
            'title'         => 'required|string|min:5|max:160',
            'category'      => ['required', Rule::in(array_keys(LostItem::CATEGORIES))],
            'description'   => 'required|string|min:15|max:2000',
            'location'      => 'nullable|string|max:160',
            'happened_on'   => 'nullable|date|before_or_equal:today',
            'reward'        => 'nullable|integer|min:0|max:1000000',
            'contact_name'  => 'required|string|max:120',
            'contact_phone' => 'required|string|min:8|max:30',
            'image'         => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = Str::lower(Str::random(20)) . '.' . $file->getClientOriginalExtension();
            $stored = $file->storeAs('lost-items', $name, 'public');
            $path = '/storage/' . $stored;
        }

        $item = LostItem::create([
            ...$data,
            'image'   => $path,
            'user_id' => Auth::id(),
            'status'  => 'open',
        ]);

        try {
            $kindLabel = $item->kind === 'found' ? '✋ حد لقى حاجة' : '🔍 بلاغ مفقودات جديد';
            app(\App\Services\PushSender::class)->toAdmins([
                'title' => $kindLabel,
                'body'  => mb_substr($item->title, 0, 80).' · '.($item->location ?? 'بنها'),
                'url'   => route('admin.lost.index'),
                'tag'   => 'admin-lost-'.$item->id,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[push admins lost] '.$e->getMessage());
        }

        return redirect()->route('lost.show', $item)->with('flash', 'تم نشر البلاغ ✓');
    }

    public function show(LostItem $lost): View
    {
        $lost->increment('views_count');
        $lost->load('user');
        return view('public.lost.show', ['item' => $lost]);
    }

    public function resolve(LostItem $lost): RedirectResponse
    {
        abort_unless($lost->user_id === Auth::id() || Auth::user()?->isAdmin(), 403);
        $lost->update(['status' => 'resolved', 'resolved_at' => now()]);
        return back()->with('flash', 'تم إقفال البلاغ');
    }
}
