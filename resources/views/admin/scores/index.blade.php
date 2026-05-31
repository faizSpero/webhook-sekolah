@extends('layouts.admin')

@section('title', 'Student Scores')

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem">
        <h1 style="margin:0">Student Scores</h1>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <a href="{{ route('admin.scores.import.form') }}" class="btn btn-secondary">Import CSV</a>
            <a href="{{ route('admin.scores.create') }}" class="btn btn-primary">+ Create Score</a>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Subject</th>
                <th>Score</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($scores as $score)
                <tr>
                    <td>
                        {{ $score->student?->name }}<br>
                        <small style="color:#6b7280">NISN: {{ $score->student?->nisn ?? '-' }}</small>
                    </td>
                    <td>{{ $score->subject }}</td>
                    <td>{{ rtrim(rtrim(number_format((float) $score->score, 2, '.', ''), '0'), '.') }}</td>
                    <td>{{ $score->score_date?->format('Y-m-d') ?? '—' }}</td>
                    <td style="white-space:nowrap">
                        <a class="link" href="{{ route('admin.scores.edit', $score) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.scores.destroy', $score) }}" style="display:inline"
                              onsubmit="return confirm('Delete this score?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" type="submit" style="margin-left:.5rem">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:#6b7280;padding:1.5rem">No scores found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $scores->links('pagination::simple-tailwind') }}</div>
</div>
@endsection
