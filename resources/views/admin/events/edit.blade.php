@extends('layouts.admin')

@section('title', 'Edit Event')

@section('content')
<div class="card">
    <h1>Edit Event</h1>
    <form method="POST" action="{{ route('admin.events.update', $event) }}">
        @csrf
        @method('PUT')
        @include('admin.events._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Update</button>
            <a class="btn btn-secondary" href="{{ route('admin.events.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
