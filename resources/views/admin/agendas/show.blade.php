@extends('layouts.admin')

@section('title', 'Agenda Detail')

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem">
        <h1 style="margin:0">Agenda #{{ $agenda->id }}</h1>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <a class="btn btn-secondary" href="{{ route('admin.agendas.index') }}">← Back</a>
            <a class="btn btn-secondary" href="{{ route('admin.agendas.edit', $agenda) }}">Edit</a>
            <form method="POST" action="{{ route('admin.agendas.destroy', $agenda) }}" onsubmit="return confirm('Delete this agenda?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">Delete</button>
            </form>
        </div>
    </div>

    <table style="width:auto;margin-bottom:1rem">
        <tr><th style="width:160px">Title</th><td>{{ $agenda->title }}</td></tr>
        <tr><th>Description</th><td style="white-space:pre-wrap">{{ $agenda->description ?: '—' }}</td></tr>
        <tr><th>Agenda Date</th><td>{{ $agenda->agenda_date ?? $agenda->starts_at?->format('Y-m-d') ?? '—' }}</td></tr>
        <tr><th>Agenda Time</th><td>{{ $agenda->agenda_time ?? $agenda->starts_at?->format('H:i') ?? '—' }}</td></tr>
        <tr><th>Location</th><td>{{ $agenda->location ?? '—' }}</td></tr>
        <tr><th>Starts At</th><td>{{ $agenda->starts_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
        <tr><th>Ends At</th><td>{{ $agenda->ends_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
        <tr><th>Status</th><td>{{ $agenda->is_active ? 'Active' : 'Inactive' }}</td></tr>
    </table>
</div>
@endsection
