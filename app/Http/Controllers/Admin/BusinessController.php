<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function index(Request $request): View
    {
        $q       = $request->input('q');
        $type    = $request->input('type');
        $status  = $request->input('status'); // all|active|inactive|unclaimed|verified|featured

        $businesses = Business::with('type', 'owner')
            ->when($q, fn ($qb) => $qb->where(fn ($w) =>
                $w->where('name', 'like', "%$q%")
                  ->orWhere('slug', 'like', "%$q%")
                  ->orWhere('phone', 'like', "%$q%")
                  ->orWhere('whatsapp', 'like', "%$q%")
            ))
            ->when($type, fn ($qb) => $qb->whereHas('type', fn ($t) => $t->where('slug', $type)))
            ->when($status === 'active',    fn ($qb) => $qb->where('is_active', true))
            ->when($status === 'inactive',  fn ($qb) => $qb->where('is_active', false))
            ->when($status === 'unclaimed', fn ($qb) => $qb->whereNull('owner_id'))
            ->when($status === 'verified',  fn ($qb) => $qb->where('is_verified', true))
            ->when($status === 'featured',  fn ($qb) => $qb->where('is_featured', true))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $types = BusinessType::orderBy('sort')->get();

        return view('admin.businesses.index', compact('businesses', 'types', 'q', 'type', 'status'));
    }

    public function edit(Business $business): View
    {
        $business->load('type', 'owner');
        $types = BusinessType::orderBy('sort')->get();
        return view('admin.businesses.edit', compact('business', 'types'));
    }

    public function update(Request $request, Business $business): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:160',
            'category'     => 'nullable|string|max:160',
            'description'  => 'nullable|string|max:2000',
            'phone'        => 'nullable|string|max:30',
            'whatsapp'     => 'required|string|max:30',
            'email'        => 'nullable|email|max:120',
            'address'      => 'required|string|max:255',
            'lat'          => 'required|numeric|between:-90,90',
            'lng'          => 'required|numeric|between:-180,180',
            'business_type_id' => 'required|exists:business_types,id',
            'price_range'  => 'required|in:low,medium,high',
            'is_active'    => 'sometimes|boolean',
            'is_verified'  => 'sometimes|boolean',
            'is_featured'  => 'sometimes|boolean',
        ]);

        $data['is_active']   = $request->boolean('is_active');
        $data['is_verified'] = $request->boolean('is_verified');
        $data['is_featured'] = $request->boolean('is_featured');

        $business->update($data);
        return redirect()->route('admin.businesses.edit', $business)->with('flash', 'تم تحديث المتجر ✓');
    }

    public function toggle(Request $request, Business $business): RedirectResponse
    {
        $field = $request->input('field');
        abort_unless(in_array($field, ['is_active', 'is_verified', 'is_featured']), 422);
        $business->update([$field => ! $business->$field]);
        return back()->with('flash', 'تم التحديث ✓');
    }

    public function destroy(Business $business): RedirectResponse
    {
        $name = $business->name;
        $business->delete();
        return redirect()->route('admin.businesses.index')->with('flash', "تم حذف \"$name\" ✓");
    }
}
