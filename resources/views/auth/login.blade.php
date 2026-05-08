<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — EXU Finance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Inter', system-ui, sans-serif;
        }
        .login-card {
            background: #fff; border-radius: 20px; padding: 2.5rem;
            width: 100%; max-width: 420px;
            box-shadow: 0 25px 60px rgba(0,0,0,.35);
        }
        .login-logo {
            width: 56px; height: 56px; border-radius: 16px;
            background: #3b82f6; display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; color: #fff; margin: 0 auto 1rem;
        }
        .form-control { border-radius: 10px; border-color: #e2e8f0; padding: .65rem 1rem; }
        .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
        .btn-login {
            background: #3b82f6; color: #fff; border: none;
            border-radius: 10px; padding: .75rem; font-weight: 600; width: 100%;
            transition: background .15s;
        }
        .btn-login:hover { background: #2563eb; }
        .footer-text { font-size: .75rem; color: #94a3b8; text-align: center; margin-top: 1.5rem; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-logo"><i class="bi bi-bar-chart-line-fill"></i></div>
    <h5 class="text-center fw-700 mb-1" style="font-weight:700;">EXU Finance</h5>
    <p class="text-center text-muted mb-4" style="font-size:.85rem;">Exponential University</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        @if ($errors->any())
        <div class="alert alert-danger" style="border-radius:10px; font-size:.85rem;">
            {{ $errors->first() }}
        </div>
        @endif

        <div class="mb-3">
            <label class="form-label fw-600" style="font-size:.82rem; font-weight:600;">Email Address</label>
            <input type="email" name="email" class="form-control"
                   value="{{ old('email') }}" required autofocus
                   placeholder="you@exponentialuniversity.ph">
        </div>

        <div class="mb-3">
            <label class="form-label fw-600" style="font-size:.82rem; font-weight:600;">Password</label>
            <input type="password" name="password" class="form-control"
                   required placeholder="••••••••">
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember" style="font-size:.82rem;">Remember me</label>
            </div>
        </div>

        <button type="submit" class="btn-login">Sign In</button>
    </form>

    <div class="footer-text">
        &copy; {{ date('Y') }} Exponential University. All rights reserved.
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
