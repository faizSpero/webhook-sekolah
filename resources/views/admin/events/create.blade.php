@extends('layouts.admin')

@section('title', 'Create Event')

@section('content')
<div class="card">
    <h1>Create Event</h1>
    <form method="POST" action="{{ route('admin.events.store') }}">
        @csrf
        @include('admin.events._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-secondary" href="{{ route('admin.events.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
