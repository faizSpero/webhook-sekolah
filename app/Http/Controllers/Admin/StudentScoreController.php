<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentScore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentScoreController extends Controller
{
    /** Subject columns in the Excel row-per-student template. */
    private const SUBJECT_COLUMNS = [
        'agama'        => 'Agama',
        'pkn'          => 'PKN',
        'bhs_indonesia'=> 'BHS_INDONESIA',
        'mtk'          => 'MTK',
        'ipa'          => 'IPA',
        'ips'          => 'IPS',
        'inggris'      => 'INGGRIS',
        'pjok'         => 'PJOK',
        'informatika'  => 'INFORMATIKA',
        'seni'         => 'SENI',
        'jawa'         => 'JAWA',
    ];

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
            'student_id'    => ['required', 'exists:students,id'],
            'subject'       => ['required', 'string', 'max:255'],
            'score'         => ['required', 'numeric', 'between:0,100'],
            'score_date'    => ['nullable', 'date'],
            'semester'      => ['nullable', 'string', 'max:50'],
            'tahun_akademik'=> ['nullable', 'string', 'max:50'],
            'notes'         => ['nullable', 'string'],
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
            'student_id'    => ['required', 'exists:students,id'],
            'subject'       => ['required', 'string', 'max:255'],
            'score'         => ['required', 'numeric', 'between:0,100'],
            'score_date'    => ['nullable', 'date'],
            'semester'      => ['nullable', 'string', 'max:50'],
            'tahun_akademik'=> ['nullable', 'string', 'max:50'],
            'notes'         => ['nullable', 'string'],
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

    /** Download a ready-to-fill Excel template (.xlsx). */
    public function downloadTemplate(): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['NIS', 'NISN', 'NAMA', 'KELAS', ...array_values(self::SUBJECT_COLUMNS), 'semester', 'Tahun Akademik'];
        foreach ($headers as $col => $label) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $label);
        }

        // Example row
        $example = ['12345', '9901234567', 'Nama Siswa', '7A', ...array_fill(0, count(self::SUBJECT_COLUMNS), 80), '1', '2024/2025'];
        foreach ($example as $col => $value) {
            $sheet->setCellValueByColumnAndRow($col + 1, 2, $value);
        }

        // Bold header
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_nilai_siswa.xlsx"',
        ]);
    }

    /** Handle both CSV (old format: subject/score per row) and Excel (.xlsx/.xls, row-per-student). */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls'],
        ]);

        $file      = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            return $this->importExcel($file);
        }

        return $this->importCsv($file);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function importExcel(\Illuminate\Http\UploadedFile $file): RedirectResponse
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getRealPath());
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getRealPath());
        $rows        = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        if (empty($rows)) {
            throw ValidationException::withMessages(['file' => 'Excel file is empty.']);
        }

        // Normalize header row
        $rawHeaders = array_shift($rows);
        $headers    = array_map(fn ($h) => strtolower(trim(str_replace([' ', '-'], '_', (string) $h))), $rawHeaders);

        // Build a lookup of normalised subject key → column index
        $subjectColMap = [];
        foreach (self::SUBJECT_COLUMNS as $key => $label) {
            $normalised = strtolower(str_replace([' ', '-'], '_', $label));
            $idx = array_search($normalised, $headers, true);
            if ($idx !== false) {
                $subjectColMap[$key] = $idx;
            }
        }

        $imported = 0;
        $errors   = [];

        DB::transaction(function () use ($rows, $headers, $subjectColMap, &$imported, &$errors): void {
            foreach ($rows as $rowNumber => $row) {
                $data = [];
                foreach ($headers as $idx => $header) {
                    $data[$header] = isset($row[$idx]) ? trim((string) $row[$idx]) : null;
                }

                // Skip completely empty rows
                if (array_filter($data) === []) {
                    continue;
                }

                $humanRow = $rowNumber + 2; // +1 for 0-index, +1 for header

                // Resolve student by NIS then NISN
                $student = null;
                if (! empty($data['nis'])) {
                    $student = Student::where('nis', $data['nis'])->first();
                }
                if (! $student && ! empty($data['nisn'])) {
                    $student = Student::where('nisn', $data['nisn'])->first();
                }

                if (! $student) {
                    $errors[] = "Row {$humanRow}: student not found (NIS={$data['nis']}, NISN={$data['nisn']}).";
                    continue;
                }

                $semester      = $data['semester'] ?? null;
                $tahunAkademik = $data['tahun_akademik'] ?? ($data['tahun akademik'] ?? null);

                foreach ($subjectColMap as $subjectKey => $colIdx) {
                    $rawScore = $row[$colIdx] ?? null;
                    if ($rawScore === null || $rawScore === '') {
                        continue;
                    }
                    if (! is_numeric($rawScore)) {
                        $errors[] = "Row {$humanRow}, subject {$subjectKey}: score '{$rawScore}' is not numeric – skipped.";
                        continue;
                    }
                    $scoreVal = (float) $rawScore;
                    if ($scoreVal < 0 || $scoreVal > 100) {
                        $errors[] = "Row {$humanRow}, subject {$subjectKey}: score must be 0–100 – skipped.";
                        continue;
                    }

                    StudentScore::updateOrCreate(
                        [
                            'student_id'    => $student->id,
                            'subject'       => self::SUBJECT_COLUMNS[$subjectKey],
                            'semester'      => $semester ?: null,
                            'tahun_akademik'=> $tahunAkademik ?: null,
                        ],
                        ['score' => $scoreVal]
                    );

                    $imported++;
                }
            }
        });

        if ($imported === 0) {
            throw ValidationException::withMessages(['file' => 'No scores were imported. '.implode(' ', array_slice($errors, 0, 5))]);
        }

        $msg = "Imported {$imported} score(s) from Excel.";
        if ($errors) {
            $msg .= ' Warnings: '.implode(' | ', array_slice($errors, 0, 5));
        }

        return redirect()->route('admin.scores.index')->with('success', $msg);
    }

    private function importCsv(\Illuminate\Http\UploadedFile $file): RedirectResponse
    {
        $handle = fopen($file->getRealPath(), 'r');

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
        $errors   = [];

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

                $subject    = (string) ($data['subject'] ?? '');
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
                    'student_id'    => $student->id,
                    'subject'       => $subject,
                    'score'         => $scoreNumber,
                    'score_date'    => $data['score_date'] ?: null,
                    'semester'      => $data['semester'] ?: null,
                    'tahun_akademik'=> $data['tahun_akademik'] ?: null,
                    'notes'         => $data['notes'] ?: null,
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
