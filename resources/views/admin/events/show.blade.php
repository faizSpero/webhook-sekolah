@extends('layouts.admin')

@section('title', "Event #{{ $event->id }}")

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem">
        <h1 style="margin:0">Event #{{ $event->id }}</h1>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
            <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">← Back</a>
            <form method="POST" action="{{ route('admin.events.replay', $event) }}"
                  onsubmit="return confirm('Re-queue this event for processing?')">
                @csrf
                <button type="submit" class="btn btn-primary">↺ Replay</button>
            </form>
        </div>
    </div>

    <table style="width:auto;margin-bottom:1.5rem">
        <tr><th style="width:160px">ID</th><td>{{ $event->id }}</td></tr>
        <tr><th>Event ID</th><td style="word-break:break-all">{{ $event->event_id }}</td></tr>
        <tr><th>Source</th><td>{{ $event->source }}</td></tr>
        <tr><th>Event type</th><td>{{ $event->event_type }}</td></tr>
        <tr><th>Status</th><td><span class="badge badge-{{ $event->status }}">{{ $event->status }}</span></td></tr>
        <tr><th>Attempts</th><td>{{ $event->attempts }}</td></tr>
        <tr>
            <th>Sender timestamp</th>
            <td>
                @if($event->sender_timestamp)
                    {{ \Carbon\Carbon::createFromTimestamp($event->sender_timestamp)->toDateTimeString() }}
                    ({{ $event->sender_timestamp }})
                @else
                    —
                @endif
            </td>
        </tr>
        <tr><th>Received at</th><td>{{ $event->created_at }}</td></tr>
        <tr><th>Processed at</th><td>{{ $event->processed_at ?? '—' }}</td></tr>
        @if($event->error_message)
        <tr><th>Error</th><td style="color:#dc2626">{{ $event->error_message }}</td></tr>
        @endif
    </table>

    <h2>Payload</h2>
    <pre>{{ json_encode($event->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>

    <h2>Headers</h2>
    <pre>{{ json_encode($event->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</div>
@endsection
