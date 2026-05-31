<div class="form-grid">
    <div>
        <label for="student_id">Student</label>
        <select id="student_id" name="student_id" required>
            <option value="">Select student</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" @selected((int) old('student_id', $score->student_id ?? 0) === $student->id)>
                    {{ $student->name }} ({{ $student->nisn ?: ($student->nis ?: 'N/A') }})
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="subject">Subject</label>
        <input id="subject" type="text" name="subject" value="{{ old('subject', $score->subject ?? '') }}" required>
    </div>
    <div>
        <label for="score">Score</label>
        <input id="score" type="number" step="0.01" min="0" max="100" name="score" value="{{ old('score', $score->score ?? '') }}" required>
    </div>
    <div>
        <label for="score_date">Score Date</label>
        <input id="score_date" type="date" name="score_date" value="{{ old('score_date', isset($score) && $score->score_date ? $score->score_date->format('Y-m-d') : '') }}">
    </div>
    <div style="grid-column:1 / -1">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="3">{{ old('notes', $score->notes ?? '') }}</textarea>
    </div>
</div>
