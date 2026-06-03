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
                <th>Agenda Date</th>
                <th>Agenda Time</th>
                <th>Location</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($agendas as $agenda)
                <tr>
                    <td>{{ $agenda->title }}</td>
                    <td>{{ $agenda->agenda_date ?? $agenda->starts_at?->format('Y-m-d') ?? '—' }}</td>
                    <td>{{ $agenda->agenda_time ?? $agenda->starts_at?->format('H:i') ?? '—' }}</td>
                    <td>{{ $agenda->location ?? '—' }}</td>
                    <td>{{ $agenda->is_active ? 'Active' : 'Inactive' }}</td>
                    <td style="white-space:nowrap">
                        @if(\Illuminate\Support\Facades\Route::has('admin.agendas.show'))
                            <a class="link" href="{{ route('admin.agendas.show', $agenda) }}">View</a>
                        @endif
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
                    <td colspan="6" style="text-align:center;color:#6b7280;padding:1.5rem">No agendas found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $agendas->links('pagination::simple-tailwind') }}</div>
</div>
@endsection
