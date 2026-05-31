@extends('layouts.admin')

@section('title', 'Agendas')

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem">
        <h1 style="margin:0">Agendas</h1>
        <a href="{{ route('admin.agendas.create') }}" class="btn btn-primary">+ Create Agenda</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($agendas as $agenda)
                <tr>
                    <td>{{ $agenda->title }}</td>
                    <td>{{ $agenda->starts_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $agenda->ends_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td>{{ $agenda->is_active ? 'Active' : 'Inactive' }}</td>
                    <td style="white-space:nowrap">
                        <a class="link" href="{{ route('admin.agendas.edit', $agenda) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.agendas.destroy', $agenda) }}" style="display:inline"
                              onsubmit="return confirm('Delete this agenda?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" type="submit" style="margin-left:.5rem">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:#6b7280;padding:1.5rem">No agendas found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $agendas->links('pagination::simple-tailwind') }}</div>
</div>
@endsection
