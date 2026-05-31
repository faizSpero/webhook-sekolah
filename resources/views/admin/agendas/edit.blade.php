@extends('layouts.admin')

@section('title', 'Edit Agenda')

@section('content')
<div class="card">
    <h1>Edit Agenda</h1>
    <form method="POST" action="{{ route('admin.agendas.update', $agenda) }}">
        @csrf
        @method('PUT')
        @include('admin.agendas._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Update</button>
            <a class="btn btn-secondary" href="{{ route('admin.agendas.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
