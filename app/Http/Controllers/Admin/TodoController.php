<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TodoController extends Controller
{
    public function index(): View
    {
        $todos = Todo::query()->latest()->paginate(20);

        return view('admin.todos.index', compact('todos'));
    }

    public function create(): View
    {
        return view('admin.todos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Todo::create($this->validatedData($request));

        return redirect()->route('admin.todos.index')->with('success', 'To-do created successfully.');
    }

    public function edit(Request $request, Todo $todo): View
    {
        $todo = $this->resolveTodo($request, $todo);

        return view('admin.todos.edit', compact('todo'));
    }

    public function update(Request $request, Todo $todo): RedirectResponse
    {
        $todo = $this->resolveTodo($request, $todo);

        $todo->update($this->validatedData($request));

        return redirect()->route('admin.todos.index')->with('success', 'To-do updated successfully.');
    }

    public function destroy(Request $request, Todo $todo): RedirectResponse
    {
        $todo = $this->resolveTodo($request, $todo);

        $todo->delete();

        return redirect()->route('admin.todos.index')->with('success', 'To-do deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'is_completed' => ['nullable', 'boolean'],
        ]);

        $data['is_completed'] = $request->boolean('is_completed');

        return $data;
    }

    private function resolveTodo(Request $request, Todo $todo): Todo
    {
        if ($todo->exists) {
            return $todo;
        }

        $id = $request->route('todo');

        return Todo::query()->findOrFail($id);
    }
}
