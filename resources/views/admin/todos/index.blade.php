@extends('layouts.admin')

@section('title', 'To-Do')

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem">
        <h1 style="margin:0">To-Do</h1>
        <a href="{{ route('admin.todos.create') }}" class="btn btn-primary">+ Create To-Do</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Due Date</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($todos as $todo)
                <tr>
                    <td>
                        {{ $todo->title }}<br>
                        <small style="color:#6b7280">{{ \Illuminate\Support\Str::limit($todo->description, 100) }}</small>
                    </td>
                    <td>{{ $todo->due_date?->format('Y-m-d') ?? '—' }}</td>
                    <td>{{ $todo->is_completed ? 'Completed' : 'Open' }}</td>
                    <td>
                        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                            <a class="link" href="{{ route('admin.todos.edit', $todo) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.todos.destroy', $todo) }}" onsubmit="return confirm('Delete this to-do?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center;color:#6b7280;padding:1.5rem">No to-do data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $todos->links('pagination::simple-tailwind') }}</div>
</div>
@endsection
