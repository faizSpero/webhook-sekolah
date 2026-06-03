@extends('layouts.admin')

@section('title', 'Create Suggestion')

@section('content')
<div class="card">
    <h1>Create Suggestion</h1>
    <form method="POST" action="{{ route('admin.suggestions.store') }}">
        @csrf
        @include('admin.suggestions._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-secondary" href="{{ route('admin.suggestions.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
