<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();

            // Idempotency: unique event ID supplied by the sender.
            $table->string('event_id')->unique();

            // Source system name (e.g. 'dapodik', 'simak').
            $table->string('source', 64)->index();

            // Event type (e.g. 'student.enrolled', 'grade.updated').
            $table->string('event_type', 128)->index();

            // HTTP headers received (stored for debugging).
            $table->json('headers');

            // Raw JSON payload.
            $table->json('payload');

            // Processing state: pending, processing, processed, failed.
            $table->string('status', 32)->default('pending')->index();

            // Number of processing attempts.
            $table->unsignedSmallInteger('attempts')->default(0);

            // Error message from the last failed attempt.
            $table->text('error_message')->nullable();

            // Timestamp from the webhook sender (Unix timestamp).
            $table->unsignedBigInteger('sender_timestamp')->nullable();

            $table->timestamps();
            $table->timestamp('processed_at')->nullable();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};
