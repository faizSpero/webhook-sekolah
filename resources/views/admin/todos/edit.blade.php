@extends('layouts.admin')

@section('title', 'Edit To-Do')

@section('content')
<div class="card">
    <h1>Edit To-Do</h1>
    <form method="POST" action="{{ route('admin.todos.update', $todo) }}">
        @csrf
        @method('PUT')
        @include('admin.todos._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Update</button>
            <a class="btn btn-secondary" href="{{ route('admin.todos.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
