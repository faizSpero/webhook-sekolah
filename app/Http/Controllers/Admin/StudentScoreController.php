<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentScore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentScoreController extends Controller
{
    public function index(): View
    {
        $scores = StudentScore::query()
            ->with('student:id,name,nisn,nis')
            ->latest()
            ->paginate(20);

        return view('admin.scores.index', compact('scores'));
    }

    public function create(): View
    {
        $students = Student::query()->orderBy('name')->limit(200)->get(['id', 'name', 'nisn', 'nis']);

        return view('admin.scores.create', compact('students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'subject' => ['required', 'string', 'max:255'],
            'score' => ['required', 'numeric', 'between:0,100'],
            'score_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        StudentScore::create($data);

        return redirect()->route('admin.scores.index')->with('success', 'Student score created successfully.');
    }

    public function edit(StudentScore $score): View
    {
        $students = Student::query()->orderBy('name')->limit(200)->get(['id', 'name', 'nisn', 'nis']);

        return view('admin.scores.edit', compact('score', 'students'));
    }

    public function update(Request $request, StudentScore $score): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'subject' => ['required', 'string', 'max:255'],
            'score' => ['required', 'numeric', 'between:0,100'],
            'score_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $score->update($data);

        return redirect()->route('admin.scores.index')->with('success', 'Student score updated successfully.');
    }

    public function destroy(StudentScore $score): RedirectResponse
    {
        $score->delete();

        return redirect()->route('admin.scores.index')->with('success', 'Student score deleted successfully.');
    }

    public function importForm(): View
    {
        return view('admin.scores.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');

        if (! $handle) {
            throw ValidationException::withMessages(['file' => 'Unable to read uploaded file.']);
        }

        $headers = fgetcsv($handle);
        if (! is_array($headers)) {
            fclose($handle);
            throw ValidationException::withMessages(['file' => 'CSV header row is missing.']);
        }

        $normalizedHeaders = array_map(fn ($value) => strtolower(trim((string) $value)), $headers);

        $imported = 0;
        $errors = [];

        DB::transaction(function () use ($handle, $normalizedHeaders, &$imported, &$errors): void {
            $rowNumber = 1;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if ($row === [null] || $row === []) {
                    continue;
                }

                $data = [];
                foreach ($normalizedHeaders as $index => $header) {
                    $data[$header] = isset($row[$index]) ? trim((string) $row[$index]) : null;
                }

                $student = $this->resolveStudent($data);
                if (! $student) {
                    $errors[] = "Row {$rowNumber}: student not found.";
                    continue;
                }

                $subject = (string) ($data['subject'] ?? '');
                $scoreValue = $data['score'] ?? null;

                if ($subject === '' || ! is_numeric($scoreValue)) {
                    $errors[] = "Row {$rowNumber}: subject and numeric score are required.";
                    continue;
                }

                $scoreNumber = (float) $scoreValue;
                if ($scoreNumber < 0 || $scoreNumber > 100) {
                    $errors[] = "Row {$rowNumber}: score must be between 0 and 100.";
                    continue;
                }

                StudentScore::create([
                    'student_id' => $student->id,
                    'subject' => $subject,
                    'score' => $scoreNumber,
                    'score_date' => $data['score_date'] ?: null,
                    'notes' => $data['notes'] ?: null,
                ]);

                $imported++;
            }
        });

        fclose($handle);

        if ($imported === 0) {
            throw ValidationException::withMessages(['file' => 'No rows were imported. '.implode(' ', $errors)]);
        }

        return redirect()
            ->route('admin.scores.index')
            ->with('success', "Imported {$imported} score(s).".($errors ? ' Some rows were skipped: '.implode(' ', array_slice($errors, 0, 5)) : ''));
    }

    private function resolveStudent(array $data): ?Student
    {
        if (! empty($data['student_id']) && ctype_digit((string) $data['student_id'])) {
            return Student::find((int) $data['student_id']);
        }

        if (! empty($data['nisn'])) {
            $student = Student::where('nisn', $data['nisn'])->first();
            if ($student) {
                return $student;
            }
        }

        if (! empty($data['nis'])) {
            return Student::where('nis', $data['nis'])->first();
        }

        return null;
    }
}
