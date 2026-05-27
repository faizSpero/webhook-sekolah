<?php

namespace Tests\Feature;

use App\Jobs\ProcessWebhookEvent;
use App\Models\WebhookEvent;
use App\Services\WebhookProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessWebhookEventJobTest extends TestCase
{
    use RefreshDatabase;

    private function makeEvent(string $eventType = 'class.created', array $payload = []): WebhookEvent
    {
        return WebhookEvent::create([
            'event_id'   => uniqid('job_', true),
            'source'     => 'dapodik',
            'event_type' => $eventType,
            'headers'    => [],
            'payload'    => $payload ?: ['data' => ['code' => 'A-1', 'name' => 'Class A']],
            'status'     => WebhookEvent::STATUS_PENDING,
        ]);
    }

    public function test_successful_job_marks_event_processed(): void
    {
        $event = $this->makeEvent();

        $job = new ProcessWebhookEvent($event);
        $job->handle(new WebhookProcessor());

        $this->assertEquals(WebhookEvent::STATUS_PROCESSED, $event->fresh()->status);
        $this->assertNotNull($event->fresh()->processed_at);
    }

    public function test_failed_job_marks_event_failed(): void
    {
        $event = $this->makeEvent();

        $processorMock = $this->createMock(WebhookProcessor::class);
        $processorMock->method('process')->willThrowException(new \RuntimeException('DB error'));

        $job = new ProcessWebhookEvent($event);

        try {
            $job->handle($processorMock);
        } catch (\RuntimeException) {
            // Expected.
        }

        // After all retries exhausted, failed() is called.
        $job->failed(new \RuntimeException('DB error'));

        $this->assertEquals(WebhookEvent::STATUS_FAILED, $event->fresh()->status);
        $this->assertEquals('DB error', $event->fresh()->error_message);
    }

    public function test_job_increments_attempts_on_each_run(): void
    {
        $event = $this->makeEvent();
        $this->assertEquals(0, $event->attempts);

        $job = new ProcessWebhookEvent($event);
        $job->handle(new WebhookProcessor());

        $this->assertEquals(1, $event->fresh()->attempts);
    }
}
