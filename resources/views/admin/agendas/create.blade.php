@extends('layouts.admin')

@section('title', 'Create Agenda')

@section('content')
<div class="card">
    <h1>Create Agenda</h1>
    <form method="POST" action="{{ route('admin.agendas.store') }}">
        @csrf
        @include('admin.agendas._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-secondary" href="{{ route('admin.agendas.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
