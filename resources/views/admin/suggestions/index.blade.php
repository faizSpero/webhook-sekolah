@extends('layouts.admin')

@section('title', 'Suggestions')

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem">
        <h1 style="margin:0">Suggestions</h1>
        <a href="{{ route('admin.suggestions.create') }}" class="btn btn-primary">+ Create Suggestion</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Sender</th>
                <th>Message</th>
                <th>Created At</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($suggestions as $suggestion)
                <tr>
                    <td>
                        {{ $suggestion->sender_name ?: 'Unknown' }}<br>
                        <small style="color:#6b7280">{{ $suggestion->sender ?: '-' }}</small>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($suggestion->message, 80) }}</td>
                    <td>{{ $suggestion->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                            <a class="link" href="{{ route('admin.suggestions.show', $suggestion) }}">View</a>
                            <a class="link" href="{{ route('admin.suggestions.edit', $suggestion) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.suggestions.destroy', $suggestion) }}" onsubmit="return confirm('Delete this suggestion?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center;color:#6b7280;padding:1.5rem">No suggestions found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $suggestions->links('pagination::simple-tailwind') }}</div>
</div>
@endsection
