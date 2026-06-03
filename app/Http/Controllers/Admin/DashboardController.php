<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\StudentScore;
use App\Models\Suggestion;
use App\Models\Todo;
use App\Models\WebhookEvent;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $counts = [
            'events' => WebhookEvent::count(),
            'agendas' => Agenda::count(),
            'scores' => StudentScore::count(),
            'suggestions' => Suggestion::count(),
            'todos' => Todo::count(),
        ];

        return view('admin.dashboard', compact('counts'));
    }
}
