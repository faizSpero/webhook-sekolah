@extends('layouts.admin')

@section('title', 'Webhook Events')

@section('content')
<div class="card">
    <h1>Webhook Events</h1>

    <form class="filter-form" method="GET">
        <div>
            <label>Status</label>
            <select name="status">
                <option value="">All</option>
                @foreach (['pending','processing','processed','failed'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label>Source</label>
            <select name="source">
                <option value="">All</option>
                @foreach ($sources as $src)
                    <option value="{{ $src }}" @selected(request('source') === $src)>{{ $src }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label>Event type</label>
            <input type="text" name="event_type" value="{{ request('event_type') }}" placeholder="e.g. student.enrolled">
        </div>

        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">Reset</a>
    </form>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Event ID</th>
                <th>Source</th>
                <th>Event type</th>
                <th>Status</th>
                <th>Attempts</th>
                <th>Received</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($events as $event)
            <tr>
                <td>{{ $event->id }}</td>
                <td style="font-size:.78rem;word-break:break-all">{{ $event->event_id }}</td>
                <td>{{ $event->source }}</td>
                <td>{{ $event->event_type }}</td>
                <td><span class="badge badge-{{ $event->status }}">{{ $event->status }}</span></td>
                <td>{{ $event->attempts }}</td>
                <td>{{ $event->created_at->format('Y-m-d H:i:s') }}</td>
                <td><a class="link" href="{{ route('admin.events.show', $event) }}">View</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center;color:#6b7280;padding:1.5rem">No events found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $events->links('pagination::simple-tailwind') }}
    </div>
</div>
@endsection
