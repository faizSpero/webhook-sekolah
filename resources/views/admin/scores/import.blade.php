@extends('layouts.admin')

@section('title', 'Import Student Scores')

@section('content')
<div class="card">
    <h1>Import Nilai Siswa</h1>

    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:.5rem;padding:1rem;margin-bottom:1.5rem">
        <h2 style="margin:0 0 .5rem;font-size:1rem">Format Excel (satu baris per siswa)</h2>
        <p style="margin:.25rem 0;color:#374151">Upload file <strong>.xlsx</strong> dengan kolom berikut:</p>
        <code style="display:block;background:#e0f2fe;padding:.5rem;border-radius:.25rem;font-size:.85rem;word-break:break-all">
            NIS | NISN | NAMA | KELAS | Agama | PKN | BHS_INDONESIA | MTK | IPA | IPS | INGGRIS | PJOK | INFORMATIKA | SENI | JAWA | semester | Tahun Akademik
        </code>
        <p style="margin:.5rem 0 0;color:#6b7280;font-size:.9rem">
            Siswa dicocokkan berdasarkan <strong>NIS</strong> atau <strong>NISN</strong>. Satu baris Excel akan menghasilkan beberapa baris nilai (satu per mata pelajaran).
        </p>
        <a href="{{ route('admin.scores.template') }}" class="btn btn-secondary" style="margin-top:.75rem;display:inline-block">
            ⬇ Download Template Excel
        </a>
    </div>

    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:.5rem;padding:1rem;margin-bottom:1.5rem">
        <h2 style="margin:0 0 .5rem;font-size:1rem">Format CSV (satu baris per nilai)</h2>
        <p style="margin:.25rem 0;color:#374151">Upload file <strong>.csv</strong> dengan kolom:</p>
        <code style="display:block;background:#f3f4f6;padding:.5rem;border-radius:.25rem;font-size:.85rem">
            subject | score | student_id / nisn / nis | score_date (opsional) | semester (opsional) | tahun_akademik (opsional) | notes (opsional)
        </code>
    </div>

    @if ($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:.5rem;padding:1rem;margin-bottom:1rem;color:#b91c1c">
            @foreach ($errors->all() as $error)
                <p style="margin:.25rem 0">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.scores.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
            <div>
                <label for="file">File (Excel .xlsx atau CSV .csv)</label>
                <input id="file" type="file" name="file" accept=".csv,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Import</button>
            <a class="btn btn-secondary" href="{{ route('admin.scores.index') }}">Batal</a>
        </div>
    </form>
</div>
@endsection

