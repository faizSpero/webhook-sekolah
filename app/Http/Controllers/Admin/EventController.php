<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhookEvent;
use App\Models\WebhookEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * List webhook events with optional filtering.
     */
    public function index(Request $request): View
    {
        $query = WebhookEvent::query()->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($source = $request->query('source')) {
            $query->where('source', $source);
        }

        if ($eventType = $request->query('event_type')) {
            $query->where('event_type', 'like', "%{$eventType}%");
        }

        $events  = $query->paginate(50)->withQueryString();
        $sources = WebhookEvent::distinct()->orderBy('source')->pluck('source');

        return view('admin.events.index', compact('events', 'sources'));
    }

    /**
     * Show a single webhook event with its full payload.
     */
    public function show(WebhookEvent $event): View
    {
        return view('admin.events.show', compact('event'));
    }

    /**
     * Re-dispatch a failed (or any) event back to the queue.
     */
    public function replay(WebhookEvent $event): RedirectResponse
    {
        $event->update([
            'status'        => WebhookEvent::STATUS_PENDING,
            'error_message' => null,
        ]);

        ProcessWebhookEvent::dispatch($event)
            ->onQueue(config('webhook.queue', 'webhooks'));

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', "Event #{$event->id} has been queued for reprocessing.");
    }
}
