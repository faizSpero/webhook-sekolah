<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') – Webhook Sekolah</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            color: #1f2937;
        }

        nav {
            background: #1e3a5f;
            color: #fff;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            height: 52px;
        }

        nav a {
            color: #c7d8f0;
            text-decoration: none;
            font-size: 0.9rem;
        }

        nav a:hover { color: #fff; }

        nav .brand {
            font-weight: bold;
            font-size: 1rem;
            color: #fff;
            margin-right: auto;
        }

        .container {
            max-width: 1200px;
            margin: 1.5rem auto;
            padding: 0 1rem;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        h1, h2 { margin-top: 0; }

        .badge {
            display: inline-block;
            padding: .25em .55em;
            border-radius: 4px;
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-pending    { background: #fef3c7; color: #92400e; }
        .badge-processing { background: #dbeafe; color: #1e40af; }
        .badge-processed  { background: #d1fae5; color: #065f46; }
        .badge-failed     { background: #fee2e2; color: #991b1b; }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            border-radius: 6px;
            padding: .75rem 1rem;
            margin-bottom: 1rem;
        }

        table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        th { text-align: left; border-bottom: 2px solid #e5e7eb; padding: .5rem .75rem; color: #6b7280; }
        td { border-bottom: 1px solid #f3f4f6; padding: .6rem .75rem; vertical-align: top; }
        tr:hover td { background: #f9fafb; }

        a.link { color: #2563eb; text-decoration: none; }
        a.link:hover { text-decoration: underline; }

        .btn {
            display: inline-block;
            padding: .4rem .85rem;
            border-radius: 5px;
            font-size: .85rem;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }

        .btn-primary { background: #2563eb; color: #fff; }
        .btn-danger  { background: #dc2626; color: #fff; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn:hover { opacity: .88; }

        .filter-form { display: flex; gap: .75rem; flex-wrap: wrap; margin-bottom: 1.25rem; align-items: flex-end; }
        .filter-form label { display: block; font-size: .8rem; color: #6b7280; margin-bottom: .2rem; }
        .filter-form select,
        .filter-form input[type=text] {
            padding: .4rem .6rem;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: .85rem;
        }

        pre { background: #f3f4f6; border-radius: 6px; padding: 1rem; overflow-x: auto; font-size: .8rem; }

        .pagination { display: flex; gap: .5rem; margin-top: 1rem; flex-wrap: wrap; }
        .pagination a,
        .pagination span {
            padding: .35rem .65rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: .85rem;
            text-decoration: none;
            color: #374151;
        }
        .pagination .active span { background: #2563eb; color: #fff; border-color: #2563eb; }
    </style>
</head>
<body>

<nav>
    <span class="brand">Webhook Sekolah</span>
    <a href="{{ route('admin.events.index') }}">Events</a>
    <a href="{{ route('todo.index') }}">To-Do</a>
</nav>

<div class="container">
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @yield('content')
</div>

</body>
</html>
