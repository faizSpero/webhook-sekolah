@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="card">
    <h1>Admin Dashboard</h1>
    <div class="form-grid">
        <div class="card" style="margin:0">
            <strong>Events</strong>
            <div style="font-size:1.5rem;margin:.25rem 0">{{ $counts['events'] }}</div>
            <a class="link" href="{{ route('admin.events.index') }}">Manage events</a>
        </div>
        <div class="card" style="margin:0">
            <strong>Agendas</strong>
            <div style="font-size:1.5rem;margin:.25rem 0">{{ $counts['agendas'] }}</div>
            <a class="link" href="{{ route('admin.agendas.index') }}">Manage agendas</a>
        </div>
        <div class="card" style="margin:0">
            <strong>Scores</strong>
            <div style="font-size:1.5rem;margin:.25rem 0">{{ $counts['scores'] }}</div>
            <a class="link" href="{{ route('admin.scores.index') }}">Manage scores</a>
        </div>
        <div class="card" style="margin:0">
            <strong>Suggestions</strong>
            <div style="font-size:1.5rem;margin:.25rem 0">{{ $counts['suggestions'] }}</div>
            <a class="link" href="{{ route('admin.suggestions.index') }}">Manage suggestions</a>
        </div>
        <div class="card" style="margin:0">
            <strong>To-Do</strong>
            <div style="font-size:1.5rem;margin:.25rem 0">{{ $counts['todos'] }}</div>
            <a class="link" href="{{ route('admin.todos.index') }}">Manage to-do</a>
        </div>
    </div>
</div>
@endsection
