@extends('layouts.admin')

@section('title', 'Create Student Score')

@section('content')
<div class="card">
    <h1>Create Student Score</h1>
    <form method="POST" action="{{ route('admin.scores.store') }}">
        @csrf
        @include('admin.scores._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-secondary" href="{{ route('admin.scores.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
