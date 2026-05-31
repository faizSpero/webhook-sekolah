@extends('layouts.admin')

@section('title', 'Edit Student Score')

@section('content')
<div class="card">
    <h1>Edit Student Score</h1>
    <form method="POST" action="{{ route('admin.scores.update', $score) }}">
        @csrf
        @method('PUT')
        @include('admin.scores._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Update</button>
            <a class="btn btn-secondary" href="{{ route('admin.scores.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
