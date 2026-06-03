@extends('layouts.admin')

@section('title', 'Suggestion Detail')

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem">
        <h1 style="margin:0">Suggestion #{{ $suggestion->id }}</h1>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <a class="btn btn-secondary" href="{{ route('admin.suggestions.index') }}">← Back</a>
            <a class="btn btn-secondary" href="{{ route('admin.suggestions.edit', $suggestion) }}">Edit</a>
            <form method="POST" action="{{ route('admin.suggestions.destroy', $suggestion) }}" onsubmit="return confirm('Delete this suggestion?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">Delete</button>
            </form>
        </div>
    </div>

    <table style="width:auto;margin-bottom:1rem">
        <tr><th style="width:140px">Sender</th><td>{{ $suggestion->sender_name ?: 'Unknown' }}</td></tr>
        <tr><th>Number</th><td>{{ $suggestion->sender ?: '—' }}</td></tr>
        <tr><th>Source</th><td>{{ $suggestion->source }}</td></tr>
        <tr><th>Created</th><td>{{ $suggestion->created_at->format('Y-m-d H:i:s') }}</td></tr>
    </table>

    <h2>Message</h2>
    <pre style="white-space:pre-wrap">{{ $suggestion->message }}</pre>
</div>
@endsection
