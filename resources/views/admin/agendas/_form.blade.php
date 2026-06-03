<div class="form-grid">
    <div>
        <label for="title">Title</label>
        <input id="title" type="text" name="title" value="{{ old('title', $agenda->title ?? '') }}" required>
    </div>
    <div>
        <label for="agenda_date">Agenda Date</label>
        <input id="agenda_date" type="date" name="agenda_date"
               value="{{ old('agenda_date', ($agenda ?? null)?->agenda_date ?? ($agenda ?? null)?->starts_at?->format('Y-m-d') ?? '') }}">
    </div>
    <div>
        <label for="agenda_time">Agenda Time</label>
        <input id="agenda_time" type="time" name="agenda_time"
               value="{{ old('agenda_time', ($agenda ?? null)?->agenda_time ?? ($agenda ?? null)?->starts_at?->format('H:i') ?? '') }}">
    </div>
    <div>
        <label for="location">Location</label>
        <input id="location" type="text" name="location" value="{{ old('location', $agenda->location ?? '') }}">
    </div>
    <div>
        <label for="starts_at">Starts At</label>
        <input id="starts_at" type="datetime-local" name="starts_at"
               value="{{ old('starts_at', isset($agenda) && $agenda->starts_at ? $agenda->starts_at->format('Y-m-d\\TH:i') : '') }}" required>
    </div>
    <div>
        <label for="ends_at">Ends At</label>
        <input id="ends_at" type="datetime-local" name="ends_at"
               value="{{ old('ends_at', isset($agenda) && $agenda->ends_at ? $agenda->ends_at->format('Y-m-d\\TH:i') : '') }}">
    </div>
    <div>
        <label for="is_active">Active</label>
        <select id="is_active" name="is_active">
            <option value="1" @selected(old('is_active', isset($agenda) ? (int) $agenda->is_active : 1) == 1)>Yes</option>
            <option value="0" @selected(old('is_active', isset($agenda) ? (int) $agenda->is_active : 1) == 0)>No</option>
        </select>
    </div>
    <div style="grid-column:1 / -1">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4">{{ old('description', $agenda->description ?? '') }}</textarea>
    </div>
</div>
