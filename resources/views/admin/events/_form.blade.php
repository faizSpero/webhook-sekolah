<div class="form-grid">
    <div>
        <label for="event_id">Event ID</label>
        <input id="event_id" type="text" name="event_id" value="{{ old('event_id', $event->event_id ?? '') }}" required>
    </div>
    <div>
        <label for="source">Source</label>
        <input id="source" type="text" name="source" value="{{ old('source', $event->source ?? '') }}" required>
    </div>
    <div>
        <label for="event_type">Event Type</label>
        <input id="event_type" type="text" name="event_type" value="{{ old('event_type', $event->event_type ?? '') }}" required>
    </div>
    <div>
        <label for="status">Status</label>
        <select id="status" name="status" required>
            @foreach ([\App\Models\WebhookEvent::STATUS_PENDING, \App\Models\WebhookEvent::STATUS_PROCESSING, \App\Models\WebhookEvent::STATUS_PROCESSED, \App\Models\WebhookEvent::STATUS_FAILED] as $status)
                <option value="{{ $status }}" @selected(old('status', $event->status ?? \App\Models\WebhookEvent::STATUS_PENDING) === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="attempts">Attempts</label>
        <input id="attempts" type="number" min="0" name="attempts" value="{{ old('attempts', $event->attempts ?? 0) }}">
    </div>
    <div>
        <label for="sender_timestamp">Sender Timestamp (Unix)</label>
        <input id="sender_timestamp" type="number" min="0" name="sender_timestamp" value="{{ old('sender_timestamp', $event->sender_timestamp ?? '') }}">
    </div>
    <div>
        <label for="processed_at">Processed At</label>
        <input id="processed_at" type="datetime-local" name="processed_at"
               value="{{ old('processed_at', isset($event) && $event->processed_at ? $event->processed_at->format('Y-m-d\\TH:i') : '') }}">
    </div>
    <div>
        <label for="error_message">Error Message</label>
        <input id="error_message" type="text" name="error_message" value="{{ old('error_message', $event->error_message ?? '') }}">
    </div>
    <div style="grid-column:1 / -1">
        <label for="payload_json">Payload (JSON)</label>
        <textarea id="payload_json" name="payload_json" rows="6" required>{{ old('payload_json', isset($event) ? json_encode($event->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '{}') }}</textarea>
    </div>
    <div style="grid-column:1 / -1">
        <label for="headers_json">Headers (JSON)</label>
        <textarea id="headers_json" name="headers_json" rows="6" required>{{ old('headers_json', isset($event) ? json_encode($event->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '{}') }}</textarea>
    </div>
</div>
