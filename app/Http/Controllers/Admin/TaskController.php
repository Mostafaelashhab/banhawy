<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $status   = $request->input('status', 'open');
        $category = $request->input('category');
        $q        = $request->input('q');

        $tasks = Task::with('user')
            ->when($status !== 'all', fn ($qb) => $qb->where('status', $status))
            ->when($category, fn ($qb) => $qb->where('category', $category))
            ->when($q, fn ($qb) => $qb->where(fn ($w) =>
                $w->where('title', 'like', "%$q%")
                  ->orWhere('description', 'like', "%$q%")
                  ->orWhere('contact_phone', 'like', "%$q%")
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.tasks.index', compact('tasks', 'status', 'category', 'q'));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'completed', 'cancelled'])],
        ]);

        $task->update([
            'status'    => $data['status'],
            'closed_at' => in_array($data['status'], ['completed','cancelled']) ? now() : null,
        ]);

        return back()->with('flash', 'تم تحديث حالة المهمة ✓');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();
        return back()->with('flash', 'تم حذف المهمة ✓');
    }
}
