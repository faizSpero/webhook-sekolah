<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates inbound webhook requests by verifying:
 *
 *  1. The X-Webhook-Signature header matches an HMAC-SHA256 digest of the
 *     raw request body signed with the source-specific secret.
 *  2. The X-Webhook-Timestamp header is within the configured replay-
 *     protection window (WEBHOOK_MAX_AGE seconds, default 300).
 *  3. The X-Webhook-Event-Id has not been seen before (idempotency key
 *     stored in the cache for max_age * 2 seconds).
 *
 * Header contract expected from senders:
 *   X-Webhook-Signature: sha256=<hex-encoded-hmac>
 *   X-Webhook-Timestamp: <unix-timestamp>
 *   X-Webhook-Event-Id:  <unique-string-per-event>
 */
class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $source = $request->route('source');

        $secret = config("webhook.secrets.{$source}");

        if (! $secret) {
            return $this->reject('Unknown webhook source.', 401);
        }

        // --- Timestamp validation (replay protection, window check) ----------
        $maxAge   = (int) config('webhook.max_age', 300);
        $timestamp = (int) $request->header('X-Webhook-Timestamp', 0);

        if ($maxAge > 0) {
            if ($timestamp === 0) {
                return $this->reject('Missing X-Webhook-Timestamp header.', 400);
            }

            $age = time() - $timestamp;
            if ($age < 0 || $age > $maxAge) {
                return $this->reject(
                    "Webhook timestamp out of acceptable window ({$maxAge}s).",
                    400
                );
            }
        }

        // --- Signature verification ------------------------------------------
        $rawSignature = $request->header('X-Webhook-Signature', '');
        // Support both "sha256=<hex>" and plain "<hex>" formats.
        $receivedHex  = str_starts_with($rawSignature, 'sha256=')
            ? substr($rawSignature, 7)
            : $rawSignature;

        if (empty($receivedHex)) {
            return $this->reject('Missing X-Webhook-Signature header.', 400);
        }

        $expectedHex = hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($expectedHex, $receivedHex)) {
            return $this->reject('Invalid webhook signature.', 401);
        }

        // --- Idempotency / replay-protection via event ID --------------------
        $eventId = $request->header('X-Webhook-Event-Id', '');

        if (! empty($eventId)) {
            $cacheKey = 'webhook_event_id:' . $source . ':' . $eventId;
            $ttl      = max($maxAge * 2, 600); // keep for at least 10 minutes

            if (Cache::has($cacheKey)) {
                return $this->reject('Duplicate webhook event.', 409);
            }

            Cache::put($cacheKey, 1, $ttl);
        }

        return $next($request);
    }

    private function reject(string $message, int $status): Response
    {
        return response()->json(['error' => $message], $status);
    }
}
