@extends('layouts.admin')

@section('title', 'Edit Suggestion')

@section('content')
<div class="card">
    <h1>Edit Suggestion</h1>
    <form method="POST" action="{{ route('admin.suggestions.update', $suggestion) }}">
        @csrf
        @method('PUT')
        @include('admin.suggestions._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Update</button>
            <a class="btn btn-secondary" href="{{ route('admin.suggestions.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
