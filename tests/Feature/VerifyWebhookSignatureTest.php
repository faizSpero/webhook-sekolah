<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class VerifyWebhookSignatureTest extends TestCase
{
    private string $source = 'simak';
    private string $secret = 'middleware-test-secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['webhook.secrets' => [$this->source => $this->secret]]);
        config(['webhook.max_age' => 300]);
    }

    private function signedRequest(
        string $body,
        string $source,
        ?string $sig = null,
        ?int $timestamp = null,
        ?string $eventId = null
    ): Request {
        $ts  = $timestamp ?? time();
        $hex = hash_hmac('sha256', $body, $this->secret);

        $request = Request::create("/api/webhook/{$source}", 'POST', [], [], [], [], $body);
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('X-Webhook-Signature', $sig ?? "sha256={$hex}");
        $request->headers->set('X-Webhook-Timestamp', (string) $ts);
        if ($eventId !== null) {
            $request->headers->set('X-Webhook-Event-Id', $eventId);
        }

        // Bind route parameter so middleware can read source.
        $request->setRouteResolver(fn () => tap(
            new \Illuminate\Routing\Route('POST', "/api/webhook/{$source}", []),
            fn ($r) => $r->bind($request)
        ));

        return $request;
    }

    private function handle(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $middleware = new VerifyWebhookSignature();
        return $middleware->handle($request, fn ($r) => response('OK'));
    }

    public function test_valid_request_passes_through(): void
    {
        $body    = json_encode(['event_type' => 'class.created']);
        $request = $this->signedRequest($body, $this->source);

        $response = $this->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_wrong_secret_rejected(): void
    {
        $body    = json_encode(['event_type' => 'class.created']);
        $badSig  = 'sha256=' . hash_hmac('sha256', $body, 'wrong-secret');
        $request = $this->signedRequest($body, $this->source, $badSig);

        $response = $this->handle($request);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_stale_timestamp_rejected(): void
    {
        $body    = json_encode(['event_type' => 'test']);
        $old     = time() - 600; // 10 minutes ago, beyond the 5 min window
        $request = $this->signedRequest($body, $this->source, null, $old);

        $response = $this->handle($request);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function test_future_timestamp_rejected(): void
    {
        $body    = json_encode(['event_type' => 'test']);
        $future  = time() + 600;
        $request = $this->signedRequest($body, $this->source, null, $future);

        $response = $this->handle($request);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function test_duplicate_event_id_rejected(): void
    {
        Cache::flush();

        $body    = json_encode(['event_type' => 'test']);
        $eventId = 'mw-dedup-01';

        // First call places the key in cache.
        $request1 = $this->signedRequest($body, $this->source, null, null, $eventId);
        $this->assertEquals(200, $this->handle($request1)->getStatusCode());

        // Second call with same ID should be rejected.
        $request2 = $this->signedRequest($body, $this->source, null, null, $eventId);
        $this->assertEquals(409, $this->handle($request2)->getStatusCode());
    }

    public function test_unknown_source_rejected(): void
    {
        $body    = json_encode(['event_type' => 'test']);
        $request = $this->signedRequest($body, 'unknown-src');

        $response = $this->handle($request);
        $this->assertEquals(401, $response->getStatusCode());
    }
}
