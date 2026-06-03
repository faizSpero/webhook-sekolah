<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function index(): View
    {
        $agendas = Agenda::query()->latest('starts_at')->paginate(20);

        return view('admin.agendas.index', compact('agendas'));
    }

    public function create(): View
    {
        return view('admin.agendas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($data['ends_at']) && strtotime((string) $data['ends_at']) < strtotime((string) $data['starts_at'])) {
            return back()
                ->withErrors(['ends_at' => 'The end time must be after or equal to the start time.'])
                ->withInput();
        }

        Agenda::create([
            ...$data,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.agendas.index')->with('success', 'Agenda created successfully.');
    }

    public function edit(Request $request, Agenda $agenda): View
    {
        $agenda = $this->resolveAgenda($request, $agenda);

        return view('admin.agendas.edit', compact('agenda'));
    }

    public function update(Request $request, Agenda $agenda): RedirectResponse
    {
        $agenda = $this->resolveAgenda($request, $agenda);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($data['ends_at']) && strtotime((string) $data['ends_at']) < strtotime((string) $data['starts_at'])) {
            return back()
                ->withErrors(['ends_at' => 'The end time must be after or equal to the start time.'])
                ->withInput();
        }

        $agenda->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.agendas.index')->with('success', 'Agenda updated successfully.');
    }

    public function destroy(Agenda $agenda): RedirectResponse
    {
        $agenda = $this->resolveAgenda(request(), $agenda);

        $agenda->delete();

        return redirect()->route('admin.agendas.index')->with('success', 'Agenda deleted successfully.');
    }

    private function resolveAgenda(Request $request, Agenda $agenda): Agenda
    {
        if ($agenda->exists) {
            return $agenda;
        }

        $id = $request->route('agenda');

        return Agenda::query()->findOrFail($id);
    }
}
