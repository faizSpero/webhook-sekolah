<div class="form-grid">
    <div>
        <label for="title">Title</label>
        <input id="title" type="text" name="title" value="{{ old('title', $todo->title ?? '') }}" required>
    </div>
    <div>
        <label for="due_date">Due Date</label>
        <input id="due_date" type="date" name="due_date"
               value="{{ old('due_date', isset($todo) && $todo->due_date ? $todo->due_date->format('Y-m-d') : '') }}">
    </div>
    <div>
        <label for="is_completed">Completed</label>
        <select id="is_completed" name="is_completed">
            <option value="0" @selected(old('is_completed', isset($todo) ? (int) $todo->is_completed : 0) == 0)>No</option>
            <option value="1" @selected(old('is_completed', isset($todo) ? (int) $todo->is_completed : 0) == 1)>Yes</option>
        </select>
    </div>
    <div style="grid-column:1 / -1">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4">{{ old('description', $todo->description ?? '') }}</textarea>
    </div>
</div>
