# webhook-sekolah

Simple to-do list web app with local storage persistence.

## Run

Open `index.html` in a browser, or serve the Laravel app and open `/todo`.

## Features

- Add tasks
- Mark tasks as completed/uncompleted
- Delete tasks
- Persist tasks in browser `localStorage`

## Laravel To-Do Integration

To-do list has been integrated using Laravel MVC structure and browser `localStorage` persistence (no database table required).

### Added structure

- Route: `/todo` in `routes/web.php`
- Controller: `App\Http\Controllers\TodoController`
- Blade view: `resources/views/todo/index.blade.php`
- Assets:
  - `public/js/todo.js`
  - `public/css/todo.css`

Open `/todo` in your Laravel app to use the to-do interface.
