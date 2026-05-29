<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->input('category');
        $urgency  = $request->input('urgency');
        $q        = $request->input('q');

        // Server returns all tasks for the chosen filters — text search is
        // handled client-side in realtime so clearing the field restores results.
        $tasks = Task::whereIn('status', ['open', 'in_progress', 'completed', 'cancelled'])
            ->when($category, fn ($qb) => $qb->where('category', $category))
            ->when($urgency, fn ($qb) => $qb->where('urgency', $urgency))
            ->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'in_progress' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(40)
            ->withQueryString();

        return view('public.tasks.index', compact('tasks', 'category', 'urgency', 'q'));
    }

    public function create(): View
    {
        return view('public.tasks.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'            => 'required|string|min:6|max:160',
            'category'         => ['required', Rule::in(array_keys(Task::CATEGORIES))],
            'description'      => 'required|string|min:15|max:2000',
            'location'         => 'nullable|string|max:160',
            'budget'           => 'nullable|integer|min:0|max:1000000',
            'urgency'          => ['required', Rule::in(array_keys(Task::URGENCIES))],
            'contact_name'     => 'required|string|max:120',
            'contact_phone'    => 'required|string|min:8|max:30',
            'contact_whatsapp' => 'nullable|string|min:8|max:30',
        ]);

        $task = Task::create([
            ...$data,
            'user_id' => Auth::id(),
            'status'  => 'open',
        ]);

        return redirect()->route('tasks.show', $task)->with('flash', 'تم نشر المهمة ✓');
    }

    public function show(Task $task): View
    {
        $task->increment('views_count');
        $task->load('user');
        return view('public.tasks.show', compact('task'));
    }

    public function close(Task $task): RedirectResponse
    {
        abort_unless($task->user_id === Auth::id() || Auth::user()?->isAdmin(), 403);
        $task->update(['status' => 'completed', 'closed_at' => now()]);
        return back()->with('flash', 'تم إقفال المهمة');
    }
}
