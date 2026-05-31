@extends('layouts.admin')

@section('title', 'Suggestions')

@section('content')
<div class="card">
    <h1>Suggestions</h1>

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
                    <td><a class="link" href="{{ route('admin.suggestions.show', $suggestion) }}">View</a></td>
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
