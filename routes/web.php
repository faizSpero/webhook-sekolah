<?php

use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\AgendaController;
use App\Http\Controllers\Admin\StudentScoreController;
use App\Http\Controllers\Admin\SuggestionController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Admin panel (protected by HTTP Basic auth via middleware).
Route::prefix('admin')
    ->middleware('auth.basic.once')
    ->name('admin.')
    ->group(function () {
        Route::get('/', fn () => redirect()->route('admin.events.index'));
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
        Route::post('/events/{event}/replay', [EventController::class, 'replay'])->name('events.replay');

        Route::resource('agendas', AgendaController::class)->except(['show']);

        Route::get('/scores/import', [StudentScoreController::class, 'importForm'])->name('scores.import.form');
        Route::post('/scores/import', [StudentScoreController::class, 'import'])->name('scores.import');
        Route::resource('scores', StudentScoreController::class)->except(['show']);

        Route::get('/suggestions', [SuggestionController::class, 'index'])->name('suggestions.index');
        Route::get('/suggestions/{suggestion}', [SuggestionController::class, 'show'])->name('suggestions.show');
    });

// Optional to-do UI (no authentication required, localStorage-only).
Route::get('/todo', [TodoController::class, 'index'])->name('todo.index');

// Root redirect to admin.
Route::get('/', fn () => redirect()->route('admin.events.index'));
