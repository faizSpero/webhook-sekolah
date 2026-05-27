<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Immutable record of every inbound webhook request.
 *
 * Statuses:
 *   pending    – received, waiting to be dispatched to the queue
 *   processing – a worker has picked it up
 *   processed  – handler completed successfully
 *   failed     – all retry attempts exhausted
 */
class WebhookEvent extends Model
{
    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PROCESSED  = 'processed';
    public const STATUS_FAILED     = 'failed';

    protected $fillable = [
        'event_id',
        'source',
        'event_type',
        'headers',
        'payload',
        'status',
        'attempts',
        'error_message',
        'sender_timestamp',
        'processed_at',
    ];

    protected $casts = [
        'headers'          => 'array',
        'payload'          => 'array',
        'sender_timestamp' => 'integer',
        'processed_at'     => 'datetime',
        'attempts'         => 'integer',
    ];

    // --------------------------------------------------------------------------
    // Scopes
    // --------------------------------------------------------------------------

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    // --------------------------------------------------------------------------
    // Helpers
    // --------------------------------------------------------------------------

    public function markProcessing(): bool
    {
        return $this->update(['status' => self::STATUS_PROCESSING]);
    }

    public function markProcessed(): bool
    {
        return $this->update([
            'status'       => self::STATUS_PROCESSED,
            'processed_at' => now(),
        ]);
    }

    public function markFailed(string $errorMessage): bool
    {
        return $this->update([
            'status'        => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    public function incrementAttempts(): bool
    {
        return $this->increment('attempts') > 0;
    }
}
