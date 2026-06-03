<div class="form-grid">
    <div>
        <label for="sender_name">Sender Name</label>
        <input id="sender_name" type="text" name="sender_name" value="{{ old('sender_name', $suggestion->sender_name ?? '') }}">
    </div>
    <div>
        <label for="sender">Sender Number</label>
        <input id="sender" type="text" name="sender" value="{{ old('sender', $suggestion->sender ?? '') }}">
    </div>
    <div>
        <label for="source">Source</label>
        <input id="source" type="text" name="source" value="{{ old('source', $suggestion->source ?? 'whatsapp') }}" required>
    </div>
    <div style="grid-column:1 / -1">
        <label for="message">Message</label>
        <textarea id="message" name="message" rows="6" required>{{ old('message', $suggestion->message ?? '') }}</textarea>
    </div>
</div>
