<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;  
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{

    use AuthorizesRequests;

    public function index()
{
    $user = auth()->user();
    if (!$user) {
        return redirect()->route('login');
    }

    \Log::info('User ID: ' . $user->id); // Debugging statement
    \Log::info('Tasks: ' . $user->tasks); // Debugging statement

    $tasks = $user->tasks()
        ->where('completed', false)
        ->orderBy('priority', 'desc')
        ->orderBy('due_date')
        ->get();

    return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        return view('tasks.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        auth()->user()->tasks()->create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'priority' => $request->input('priority'),
            'due_date' => $request->input('due_date'),
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully');
    }

    public function edit(Task $task)
    {
        // Ensure the task belongs to the authenticated user
        $this->authorize('update', $task);

        return view('tasks.edit', compact('task'));
    }
    
    public function update(Request $request, Task $task)
    {
        // Ensure the task belongs to the authenticated user
        $this->authorize('update', $task);

        $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        $task->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'priority' => $request->input('priority'),
            'due_date' => $request->input('due_date'),
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully');
    }

    public function destroy(Task $task)
    {
        // Ensure the task belongs to the authenticated user
        $this->authorize('delete', $task);

        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully');
    }

    public function complete(Task $task)
    {
        // Ensure the task belongs to the authenticated user
        $this->authorize('update', $task);

        $task->update([
            'completed' => true,
            'completed_at' => now(),
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task completed successfully');
    }

    public function showCompleted()
    {
        $completedTasks = auth()->user()->tasks()
            ->where('completed', true)
            ->orderBy('completed_at', 'desc')
            ->get();

        return view('taskShow', compact('completedTasks'));
    }    
}
