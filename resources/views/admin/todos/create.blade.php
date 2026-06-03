@extends('layouts.admin')

@section('title', 'Create To-Do')

@section('content')
<div class="card">
    <h1>Create To-Do</h1>
    <form method="POST" action="{{ route('admin.todos.store') }}">
        @csrf
        @include('admin.todos._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-secondary" href="{{ route('admin.todos.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
