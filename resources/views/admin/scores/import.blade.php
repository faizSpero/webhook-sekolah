@extends('layouts.admin')

@section('title', 'Import Student Scores')

@section('content')
<div class="card">
    <h1>Import Student Scores</h1>
    <p style="color:#6b7280">Upload a CSV file. Required columns: <code>subject</code>, <code>score</code>, and one of <code>student_id</code>/<code>nisn</code>/<code>nis</code>. Optional: <code>score_date</code>, <code>notes</code>. If you have an Excel file, export/save it as CSV first.</p>

    <form method="POST" action="{{ route('admin.scores.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
            <div>
                <label for="file">CSV File</label>
                <input id="file" type="file" name="file" accept=".csv,text/csv" required>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Import</button>
            <a class="btn btn-secondary" href="{{ route('admin.scores.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
