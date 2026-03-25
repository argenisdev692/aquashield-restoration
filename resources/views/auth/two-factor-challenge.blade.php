<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Challenge</title>
    @vite(['resources/css/app.css'])
</head>
<body style="margin:0; min-height:100vh; display:grid; place-items:center; padding:24px; background:var(--bg-app); color:var(--text-primary); font-family:var(--font-sans);">
    <main style="width:min(100%, 480px); border:1px solid var(--border-default); border-radius:var(--radius-lg); background:var(--bg-surface); padding:32px; box-shadow:0 24px 60px rgba(0,0,0,0.24);">
        <h1 style="margin:0 0 8px; font-size:1.5rem; font-weight:700;">Two-Factor Authentication</h1>
        <p style="margin:0 0 24px; color:var(--text-secondary); line-height:1.6;">
            Enter the authentication code from your app or use a recovery code.
        </p>

        @if ($errors->any())
            <div style="margin-bottom:16px; border:1px solid color-mix(in srgb, var(--accent-error) 25%, transparent); border-radius:var(--radius-md); background:color-mix(in srgb, var(--accent-error) 10%, transparent); padding:12px 14px; color:var(--accent-error);">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ url('/two-factor-challenge') }}" method="post" style="display:grid; gap:18px;">
            @csrf

            <label style="display:grid; gap:8px;">
                <span style="font-size:0.875rem; font-weight:600; color:var(--text-secondary);">Authentication code</span>
                <input
                    type="text"
                    name="code"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    autofocus
                    style="height:40px; border-radius:var(--radius-md); border:1px solid var(--input-border); background:var(--input-bg); color:var(--input-text); padding:0 12px; outline:none;"
                >
            </label>

            <div style="text-align:center; color:var(--text-muted); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.12em;">
                or
            </div>

            <label style="display:grid; gap:8px;">
                <span style="font-size:0.875rem; font-weight:600; color:var(--text-secondary);">Recovery code</span>
                <input
                    type="text"
                    name="recovery_code"
                    autocomplete="one-time-code"
                    style="height:40px; border-radius:var(--radius-md); border:1px solid var(--input-border); background:var(--input-bg); color:var(--input-text); padding:0 12px; outline:none;"
                >
            </label>

            <button
                type="submit"
                style="height:42px; border:0; border-radius:var(--radius-md); background:var(--accent-primary); color:var(--text-primary); font-weight:700; cursor:pointer;"
            >
                Verify
            </button>
        </form>
    </main>
</body>
</html>
