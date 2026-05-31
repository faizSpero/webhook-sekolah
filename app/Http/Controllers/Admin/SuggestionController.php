<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Suggestion;
use Illuminate\View\View;

class SuggestionController extends Controller
{
    public function index(): View
    {
        $suggestions = Suggestion::query()->latest()->paginate(20);

        return view('admin.suggestions.index', compact('suggestions'));
    }

    public function show(Suggestion $suggestion): View
    {
        return view('admin.suggestions.show', compact('suggestion'));
    }
}
