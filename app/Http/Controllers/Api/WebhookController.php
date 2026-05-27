<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhookEvent;
use App\Models\WebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Receive an inbound webhook from an external school system.
     *
     * The signature/timestamp/replay checks have already been performed by
     * VerifyWebhookSignature middleware before this method is reached.
     *
     * Steps:
     *   1. Persist the raw event to the database (status = pending).
     *   2. Dispatch a queued job to process it asynchronously.
     *   3. Return 202 Accepted immediately.
     */
    public function receive(Request $request, string $source): JsonResponse
    {
        $payload = $request->json()->all();

        if (empty($payload)) {
            return response()->json(['error' => 'Empty or non-JSON payload.'], 400);
        }

        $eventId   = $request->header('X-Webhook-Event-Id', (string) \Illuminate\Support\Str::uuid());
        $eventType = data_get($payload, 'event_type', 'unknown');
        $timestamp = (int) $request->header('X-Webhook-Timestamp', 0) ?: null;

        // Capture only the relevant headers for debugging (exclude auth headers).
        $headers = collect($request->headers->all())
            ->only([
                'x-webhook-event-id',
                'x-webhook-timestamp',
                'x-webhook-source',
                'content-type',
                'user-agent',
            ])
            ->toArray();

        $event = WebhookEvent::create([
            'event_id'         => $eventId,
            'source'           => $source,
            'event_type'       => $eventType,
            'headers'          => $headers,
            'payload'          => $payload,
            'status'           => WebhookEvent::STATUS_PENDING,
            'sender_timestamp' => $timestamp,
        ]);

        Log::channel('webhook')->info('Webhook received', [
            'id'         => $event->id,
            'event_id'   => $eventId,
            'source'     => $source,
            'event_type' => $eventType,
        ]);

        ProcessWebhookEvent::dispatch($event)
            ->onQueue(config('webhook.queue', 'webhooks'));

        return response()->json([
            'message'  => 'Accepted',
            'event_id' => $eventId,
        ], 202);
    }
}
