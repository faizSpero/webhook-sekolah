<?php

namespace App\Jobs;

use App\Models\WebhookEvent;
use App\Services\WebhookProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Processes a single WebhookEvent asynchronously.
 *
 * Retry policy: up to 3 automatic retries with exponential back-off.
 * After all retries are exhausted the event is marked as 'failed'.
 */
class ProcessWebhookEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Maximum number of queue-level attempts before the job is permanently failed. */
    public int $tries = 3;

    /** Back-off in seconds between attempts (10s, 60s, 300s). */
    public array $backoff = [10, 60, 300];

    public function __construct(public readonly WebhookEvent $event) {}

    public function handle(WebhookProcessor $processor): void
    {
        $this->event->markProcessing();
        $this->event->incrementAttempts();

        try {
            $processor->process($this->event);
            $this->event->markProcessed();

            Log::channel('webhook')->info('Webhook event processed', [
                'id'         => $this->event->id,
                'event_type' => $this->event->event_type,
            ]);
        } catch (Throwable $e) {
            Log::channel('webhook')->error('Webhook event processing failed', [
                'id'         => $this->event->id,
                'event_type' => $this->event->event_type,
                'error'      => $e->getMessage(),
                'attempt'    => $this->attempts(),
            ]);

            // Mark as pending so the job can be retried by the queue.
            $this->event->update(['status' => WebhookEvent::STATUS_PENDING]);

            throw $e;
        }
    }

    /**
     * Handle final failure (all retries exhausted).
     */
    public function failed(Throwable $exception): void
    {
        $this->event->markFailed($exception->getMessage());

        Log::channel('webhook')->error('Webhook event permanently failed', [
            'id'         => $this->event->id,
            'event_type' => $this->event->event_type,
            'error'      => $exception->getMessage(),
        ]);
    }
}
