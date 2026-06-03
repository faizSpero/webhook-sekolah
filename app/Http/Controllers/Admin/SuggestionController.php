<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Suggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuggestionController extends Controller
{
    public function index(): View
    {
        $suggestions = Suggestion::query()->latest()->paginate(20);

        return view('admin.suggestions.index', compact('suggestions'));
    }

    public function show(Request $request, Suggestion $suggestion): View
    {
        $suggestion = $this->resolveSuggestion($request, $suggestion);

        return view('admin.suggestions.show', compact('suggestion'));
    }

    public function create(): View
    {
        return view('admin.suggestions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Suggestion::create($this->validatedData($request));

        return redirect()->route('admin.suggestions.index')->with('success', 'Suggestion created successfully.');
    }

    public function edit(Request $request, Suggestion $suggestion): View
    {
        $suggestion = $this->resolveSuggestion($request, $suggestion);

        return view('admin.suggestions.edit', compact('suggestion'));
    }

    public function update(Request $request, Suggestion $suggestion): RedirectResponse
    {
        $suggestion = $this->resolveSuggestion($request, $suggestion);

        $suggestion->update($this->validatedData($request));

        return redirect()->route('admin.suggestions.index')->with('success', 'Suggestion updated successfully.');
    }

    public function destroy(Request $request, Suggestion $suggestion): RedirectResponse
    {
        $suggestion = $this->resolveSuggestion($request, $suggestion);

        $suggestion->delete();

        return redirect()->route('admin.suggestions.index')->with('success', 'Suggestion deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'sender' => ['nullable', 'string', 'max:64'],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'source' => ['required', 'string', 'max:32'],
            'message' => ['required', 'string'],
        ]);
    }

    private function resolveSuggestion(Request $request, Suggestion $suggestion): Suggestion
    {
        if ($suggestion->exists) {
            return $suggestion;
        }

        $id = $request->route('suggestion');

        return Suggestion::query()->findOrFail($id);
    }
}
