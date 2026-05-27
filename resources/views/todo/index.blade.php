<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>To-Do List</title>
    <link rel="stylesheet" href="{{ asset('css/todo.css') }}">
</head>
<body>
<main class="todo-app">
    <h1>To-Do List</h1>

    <form id="todo-form" class="todo-form">
        <input id="todo-input" type="text" placeholder="Add a task..." autocomplete="off" required>
        <button type="submit">Add</button>
    </form>

    <ul id="todo-list" class="todo-list" aria-live="polite"></ul>
</main>

<script src="{{ asset('js/todo.js') }}"></script>
</body>
</html>
