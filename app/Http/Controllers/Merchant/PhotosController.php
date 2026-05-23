<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotosController extends Controller
{
    public function index()
    {
        $business = $this->ownedBusiness();
        return view('merchant.photos', compact('business'));
    }

    public function store(Request $request): RedirectResponse
    {
        $business = $this->ownedBusiness();

        $request->validate([
            'photos'   => 'required|array|max:10',
            'photos.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120', // 5MB each
        ]);

        $existing = is_array($business->images) ? $business->images : [];
        if (count($existing) + count($request->file('photos')) > 30) {
            return back()->with('flash_error', 'الحد الأقصى 30 صورة لكل متجر.');
        }

        $dir = 'businesses/'.$business->id;
        $added = [];

        foreach ($request->file('photos') as $file) {
            $name = Str::lower(Str::random(20)).'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs($dir, $name, 'public');
            $added[] = Storage::url($path);
        }

        $business->update(['images' => array_values(array_merge($existing, $added))]);

        // Auto-set logo/cover if not set yet
        $updates = [];
        if (! $business->logo && $added)  $updates['logo']  = $added[0];
        if (! $business->cover && $added) $updates['cover'] = $added[0];
        if ($updates) $business->update($updates);

        return back()->with('flash', count($added).' صورة اترفعت ✓');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $business = $this->ownedBusiness();
        $url = (string) $request->input('url');
        if ($url === '') return back();

        $images = array_values(array_filter(
            is_array($business->images) ? $business->images : [],
            fn ($u) => $u !== $url
        ));

        // Delete from disk only if it's a local upload
        $prefix = '/storage/';
        $pos = strpos($url, $prefix);
        if ($pos !== false) {
            $relative = substr($url, $pos + strlen($prefix));
            Storage::disk('public')->delete($relative);
        }

        $patches = ['images' => $images];
        if ($business->logo === $url)  $patches['logo']  = $images[0] ?? null;
        if ($business->cover === $url) $patches['cover'] = $images[0] ?? null;
        $business->update($patches);

        return back()->with('flash', 'تم حذف الصورة ✓');
    }

    public function setCover(Request $request): RedirectResponse
    {
        $business = $this->ownedBusiness();
        $request->validate(['url' => 'required|url']);
        $url = $request->input('url');

        if (! in_array($url, $business->images ?? [], true)) {
            return back()->with('flash_error', 'الصورة غير موجودة.');
        }

        $business->update(['cover' => $url]);
        return back()->with('flash', 'تم تعيين الغلاف ✓');
    }

    public function setLogo(Request $request): RedirectResponse
    {
        $business = $this->ownedBusiness();
        $request->validate(['url' => 'required|url']);
        $url = $request->input('url');

        if (! in_array($url, $business->images ?? [], true)) {
            return back()->with('flash_error', 'الصورة غير موجودة.');
        }

        $business->update(['logo' => $url]);
        return back()->with('flash', 'تم تعيين الشعار ✓');
    }

    private function ownedBusiness(): Business
    {
        $b = Auth::user()->businesses()->latest()->first();
        abort_unless($b, 404, 'لا يوجد نشاط مرتبط بحسابك.');
        return $b;
    }
}
