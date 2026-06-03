<?php

use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\AgendaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StudentScoreController;
use App\Http\Controllers\Admin\SuggestionController;
use App\Http\Controllers\Admin\TodoController as AdminTodoController;
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
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('events', EventController::class);
        Route::post('/events/{event}/replay', [EventController::class, 'replay'])->name('events.replay');

        Route::resource('agendas', AgendaController::class)->except(['show']);

        Route::get('/scores/import', [StudentScoreController::class, 'importForm'])->name('scores.import.form');
        Route::get('/scores/template', [StudentScoreController::class, 'downloadTemplate'])->name('scores.template');
        Route::post('/scores/import', [StudentScoreController::class, 'import'])->name('scores.import');
        Route::resource('scores', StudentScoreController::class)->except(['show']);

        Route::resource('suggestions', SuggestionController::class);
        Route::resource('todos', AdminTodoController::class)->except(['show']);
    });

// Optional to-do UI (no authentication required, localStorage-only).
Route::get('/todo', [TodoController::class, 'index'])->name('todo.index');

// Root redirect to admin.
Route::get('/', fn () => redirect()->route('admin.dashboard'));
