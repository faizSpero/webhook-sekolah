<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhookEvent;
use App\Models\WebhookEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

    public function create(): View
    {
        return view('admin.events.create');
    }

    public function store(Request $request): RedirectResponse
    {
        WebhookEvent::create($this->validatedData($request));

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully.');
    }

    /**
     * Show a single webhook event with its full payload.
     */
    public function show(Request $request, WebhookEvent $event): View
    {
        $event = $this->resolveEvent($request, $event);

        return view('admin.events.show', compact('event'));
    }

    public function edit(Request $request, WebhookEvent $event): View
    {
        $event = $this->resolveEvent($request, $event);

        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, WebhookEvent $event): RedirectResponse
    {
        $event = $this->resolveEvent($request, $event);

        $event->update($this->validatedData($request, $event));

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
    }

    /**
     * Re-dispatch a failed (or any) event back to the queue.
     */
    public function replay(Request $request, WebhookEvent $event): RedirectResponse
    {
        $event = $this->resolveEvent($request, $event);

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

    public function destroy(Request $request, WebhookEvent $event): RedirectResponse
    {
        $event = $this->resolveEvent($request, $event);

        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?WebhookEvent $event = null): array
    {
        $data = $request->validate([
            'event_id' => ['required', 'string', 'max:255', Rule::unique('webhook_events', 'event_id')->ignore($event?->id)],
            'source' => ['required', 'string', 'max:64'],
            'event_type' => ['required', 'string', 'max:128'],
            'status' => ['required', 'in:' . implode(',', [
                WebhookEvent::STATUS_PENDING,
                WebhookEvent::STATUS_PROCESSING,
                WebhookEvent::STATUS_PROCESSED,
                WebhookEvent::STATUS_FAILED,
            ])],
            'attempts' => ['nullable', 'integer', 'min:0'],
            'sender_timestamp' => ['nullable', 'integer', 'min:0'],
            'error_message' => ['nullable', 'string'],
            'headers_json' => ['required', 'json'],
            'payload_json' => ['required', 'json'],
            'processed_at' => ['nullable', 'date'],
        ]);

        return [
            'event_id' => $data['event_id'],
            'source' => $data['source'],
            'event_type' => $data['event_type'],
            'status' => $data['status'],
            'attempts' => $data['attempts'] ?? 0,
            'sender_timestamp' => $data['sender_timestamp'] ?? null,
            'error_message' => $data['error_message'] ?? null,
            'headers' => json_decode($data['headers_json'], true, 512, JSON_THROW_ON_ERROR),
            'payload' => json_decode($data['payload_json'], true, 512, JSON_THROW_ON_ERROR),
            'processed_at' => $data['processed_at'] ?? null,
        ];
    }

    private function resolveEvent(Request $request, WebhookEvent $event): WebhookEvent
    {
        if ($event->exists) {
            return $event;
        }

        $id = $request->route('event');

        return WebhookEvent::query()->findOrFail($id);
    }
}
