<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Site Maintenance · Softia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --cloud: #F8FAFC; --blue: #8DB3E2; --navy: #1E3A8A; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Montserrat', sans-serif; background: var(--cloud); color: var(--navy); }
        main { max-width: 720px; margin: 0 auto; padding: 48px 16px; }
        h1 { font-size: 1.6rem; margin: 0 0 4px; }
        h2 { font-size: 1.1rem; margin: 0 0 16px; }
        p.lead { margin: 0 0 32px; opacity: .75; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(30, 58, 138, .08); padding: 24px; margin-bottom: 24px; }
        .task { display: flex; gap: 16px; align-items: center; justify-content: space-between; padding: 14px 0; border-top: 1px solid #E2E8F0; }
        .task:first-of-type { border-top: 0; }
        .task strong { display: block; }
        .task small { opacity: .7; }
        button { font: inherit; font-weight: 600; border: 0; border-radius: 999px; padding: 10px 20px; background: var(--navy); color: #fff; cursor: pointer; white-space: nowrap; transition: opacity .2s; }
        button:hover { opacity: .85; }
        label { display: block; font-weight: 600; font-size: .9rem; margin: 12px 0 4px; }
        input { width: 100%; font: inherit; padding: 10px 12px; border: 1px solid var(--blue); border-radius: 10px; }
        .result { border-radius: 12px; padding: 16px; margin-bottom: 24px; }
        .result.ok { background: #ECFDF5; color: #065F46; }
        .result.fail { background: #FEF2F2; color: #991B1B; }
        pre { white-space: pre-wrap; word-break: break-word; margin: 8px 0 0; font-size: .85rem; }
        .errors { color: #991B1B; font-size: .9rem; }
    </style>
</head>
<body>
<main>
    <h1>Site Maintenance</h1>
    <p class="lead">Run server tasks without a terminal. Keep this link private.</p>

    @if (session('result'))
        @php($result = session('result'))
        <div class="result {{ $result['ok'] ? 'ok' : 'fail' }}">
            <strong>{{ $result['ok'] ? 'Completed' : 'Failed' }}: {{ $tasks[$result['task']]['label'] ?? 'Create admin' }}</strong>
            <pre>{{ $result['output'] }}</pre>
        </div>
    @endif

    <section class="card">
        <h2>Tasks</h2>
        @foreach ($tasks as $key => $task)
            <form class="task" method="POST" action="{{ route('deploy.run', $token) }}"
                  onsubmit="return confirm('Run “{{ $task['label'] }}” now?')">
                @csrf
                <input type="hidden" name="task" value="{{ $key }}">
                <div>
                    <strong>{{ $task['label'] }}</strong>
                    <small>{{ $task['description'] }}</small>
                </div>
                <button type="submit">Run</button>
            </form>
        @endforeach
    </section>

    @if ($needsAdmin)
        <section class="card">
            <h2>Create the first admin account</h2>
            @if ($errors->any())
                <ul class="errors">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
            <form method="POST" action="{{ route('deploy.admin', $token) }}">
                @csrf
                <label for="name">Name</label>
                <input id="name" name="name" value="{{ old('name') }}" required>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                <label for="password">Password (12+ characters)</label>
                <input id="password" name="password" type="password" required>
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required>
                <p><button type="submit">Create admin</button></p>
            </form>
        </section>
    @endif
</main>
</body>
</html>
