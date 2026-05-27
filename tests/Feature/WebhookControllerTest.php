<?php

namespace Tests\Feature;

use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $source = 'dapodik';
    private string $secret = 'test-secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['webhook.secrets' => [$this->source => $this->secret]]);
        config(['webhook.max_age' => 0]); // Disable timestamp check in tests.
    }

    private function makeHeaders(string $body, ?string $eventId = null): array
    {
        $sig = 'sha256=' . hash_hmac('sha256', $body, $this->secret);
        $headers = [
            'X-Webhook-Signature' => $sig,
            'X-Webhook-Timestamp' => (string) time(),
            'Content-Type'        => 'application/json',
        ];
        if ($eventId !== null) {
            $headers['X-Webhook-Event-Id'] = $eventId;
        }
        return $headers;
    }

    public function test_valid_webhook_returns_202_and_persists_event(): void
    {
        Queue::fake();

        $payload = ['event_type' => 'student.enrolled', 'data' => ['id' => '42', 'name' => 'Budi']];
        $body    = json_encode($payload);

        $response = $this->postJson(
            "/api/webhook/{$this->source}",
            $payload,
            $this->makeHeaders($body, 'evt-001')
        );

        $response->assertStatus(202)
                 ->assertJsonFragment(['message' => 'Accepted']);

        $this->assertDatabaseHas('webhook_events', [
            'event_id'   => 'evt-001',
            'source'     => $this->source,
            'event_type' => 'student.enrolled',
            'status'     => 'pending',
        ]);

        Queue::assertPushed(\App\Jobs\ProcessWebhookEvent::class);
    }

    public function test_missing_signature_returns_400(): void
    {
        $payload = ['event_type' => 'student.enrolled'];

        $response = $this->postJson(
            "/api/webhook/{$this->source}",
            $payload,
            ['X-Webhook-Timestamp' => (string) time(), 'Content-Type' => 'application/json']
        );

        $response->assertStatus(400);
    }

    public function test_invalid_signature_returns_401(): void
    {
        $payload = ['event_type' => 'student.enrolled'];
        $body    = json_encode($payload);

        $response = $this->postJson(
            "/api/webhook/{$this->source}",
            $payload,
            [
                'X-Webhook-Signature' => 'sha256=invalidsignature',
                'X-Webhook-Timestamp' => (string) time(),
                'Content-Type'        => 'application/json',
            ]
        );

        $response->assertStatus(401);
    }

    public function test_unknown_source_returns_401(): void
    {
        $payload = ['event_type' => 'student.enrolled'];
        $body    = json_encode($payload);

        $response = $this->postJson(
            '/api/webhook/unknown-source',
            $payload,
            $this->makeHeaders($body)
        );

        $response->assertStatus(401);
    }

    public function test_duplicate_event_id_returns_409(): void
    {
        Queue::fake();

        $payload   = ['event_type' => 'student.enrolled', 'data' => ['id' => '1']];
        $body      = json_encode($payload);
        $eventId   = 'dedup-001';
        $headers   = $this->makeHeaders($body, $eventId);

        // First request should succeed.
        $this->postJson("/api/webhook/{$this->source}", $payload, $headers)->assertStatus(202);

        // Second request with same event ID should be rejected.
        $this->postJson("/api/webhook/{$this->source}", $payload, $headers)->assertStatus(409);
    }

    public function test_empty_payload_returns_400(): void
    {
        $body    = '';
        $headers = $this->makeHeaders($body);

        $response = $this->call(
            'POST',
            "/api/webhook/{$this->source}",
            [],
            [],
            [],
            $this->transformHeadersToServerVars($headers),
            $body
        );

        $response->assertStatus(400);
    }
}
