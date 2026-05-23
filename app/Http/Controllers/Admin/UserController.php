<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q    = $request->input('q');
        $role = $request->input('role', 'all');

        $users = User::withCount('businesses')
            ->when($q, fn ($qb) => $qb->where(fn ($w) =>
                $w->where('name', 'like', "%$q%")
                  ->orWhere('email', 'like', "%$q%")
                  ->orWhere('phone', 'like', "%$q%")
            ))
            ->when($role !== 'all', fn ($qb) => $qb->where('role', $role))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q', 'role'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['admin', 'owner', 'customer'])],
        ]);

        // Don't let the admin demote themselves into a non-admin role
        if ($user->id === $request->user()->id && $data['role'] !== 'admin') {
            return back()->with('flash_error', 'مايصحش تنزّل صلاحياتك بنفسك.');
        }

        $user->update(['role' => $data['role']]);
        return back()->with('flash', "تم تحديث صلاحية {$user->name} ✓");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('flash_error', 'مايصحش تحذف حسابك بنفسك.');
        }
        $name = $user->name;
        $user->delete();
        return redirect()->route('admin.users.index')->with('flash', "تم حذف $name ✓");
    }
}
