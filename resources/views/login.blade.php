<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign in - {{ $system->business_name ?? 'Soap Opera' }}</title>
@if(!empty($system->favicon))
<link rel="icon" href="{{ asset($system->favicon) }}?v={{ optional($system->updated_at)->timestamp }}" type="image/x-icon">
@else
<link rel="icon" href="{{ asset('images/brand/mark.svg') }}" type="image/svg+xml">
@endif

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/theme.css') }}?v={{ @filemtime(public_path('css/theme.css')) }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    min-height: 100vh;
    min-height: 100dvh;
    display: grid;
    place-items: center;
    padding: 24px;
    color: var(--text);
    background-color: var(--bg);
    background-image:
        radial-gradient(640px 460px at 0% 0%, rgba(90, 170, 255, 0.22), transparent 70%),
        radial-gradient(560px 460px at 100% 100%, rgba(120, 210, 255, 0.22), transparent 70%);
}

.login-shell {
    display: grid;
    grid-template-columns: 1.05fr 1fr;
    width: min(1040px, 100%);
    min-height: 600px;
    padding: 12px;
    border-radius: 32px;
    background: rgba(255, 255, 255, 0.85);
    -webkit-backdrop-filter: blur(16px);
    backdrop-filter: blur(16px);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-lg);
}

/* Left: brand hero */
.hero {
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 32px;
    border-radius: 24px;
    background: var(--grad-brand);
    color: #fff;
    overflow: hidden;
}
.hero::before, .hero::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    background: radial-gradient(circle at 35% 30%, rgba(255,255,255,0.32), rgba(255,255,255,0.05) 60%, transparent 70%);
}
.hero::before { width: 380px; height: 380px; right: -140px; top: -140px; }
.hero::after { width: 300px; height: 300px; left: -120px; bottom: -150px; }
.hero-brand { position: relative; z-index: 1; display: flex; align-items: center; gap: 10px; font-size: 19px; font-weight: 800; letter-spacing: -0.02em; }
.hero-brand img { width: 40px; height: 40px; padding: 4px; border-radius: 50%; background: rgba(255,255,255,0.92); box-shadow: 0 8px 18px -8px rgba(0,30,90,0.6); }
.hero-art { position: relative; z-index: 1; width: min(380px, 100%); margin: 8px auto; animation: bubble-float 6s ease-in-out infinite; }
.hero-copy { position: relative; z-index: 1; }
.hero-copy h1 { font-size: 34px; line-height: 1.12; font-weight: 800; letter-spacing: -0.03em; }
.hero-copy p { margin-top: 10px; font-size: 15px; opacity: 0.88; max-width: 360px; }
@keyframes bubble-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
@media (prefers-reduced-motion: reduce) { .hero-art { animation: none; } }

/* Right: form */
.form-side { display: flex; align-items: center; justify-content: center; padding: 40px 32px; }
.login-box { width: 100%; max-width: 360px; }
.login-box .mark { width: 52px; height: 52px; margin-bottom: 18px; filter: drop-shadow(0 8px 14px rgba(31,116,240,0.3)); }
.login-box h2 { font-size: 28px; font-weight: 800; letter-spacing: -0.02em; }
.login-box .lead { margin: 6px 0 26px; color: var(--text-muted); font-size: 14.5px; }

.login-error {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 18px; padding: 12px 14px;
    border-radius: 14px;
    background: var(--danger-soft);
    color: var(--danger-text);
    font-size: 14px; font-weight: 500;
}

.field { margin-bottom: 16px; }
.field label { display: block; margin-bottom: 7px; font-size: 13px; font-weight: 700; color: var(--text-secondary); }
.input-wrap { position: relative; }
.input-wrap > i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-faint); font-size: 15px; pointer-events: none; transition: color .15s; }
.input-wrap input {
    width: 100%;
    height: 50px;
    padding: 0 48px 0 44px;
    border: 1px solid var(--border-strong);
    border-radius: 16px;
    background: var(--surface-muted);
    color: var(--text);
    font: inherit;
    font-size: 15px;
    transition: border-color .15s, box-shadow .15s, background-color .15s;
}
.input-wrap input::placeholder { color: var(--text-faint); }
.input-wrap input:focus { outline: none; border-color: var(--accent); background: var(--surface); box-shadow: 0 0 0 4px var(--accent-ring); }
.input-wrap:focus-within > i { color: var(--accent); }
.input-wrap input.error { border-color: var(--danger); }
.toggle-pass {
    position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
    width: 36px; height: 36px; border: 0; border-radius: 50%;
    background: none; color: var(--text-faint); cursor: pointer; font-size: 15px;
}
.toggle-pass:hover { background: var(--surface-sunken); color: var(--text-secondary); }

.login-btn {
    width: 100%;
    height: 52px;
    margin-top: 8px;
    border: 0;
    border-radius: var(--radius-pill);
    background: var(--grad-button);
    color: #fff;
    font: inherit;
    font-size: 15.5px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: var(--gloss), var(--glow);
    transition: transform .15s, background .15s;
}
.login-btn:hover { background: var(--grad-button-hover); transform: translateY(-1px); }
.login-btn:disabled { opacity: 0.7; cursor: progress; transform: none; }
.login-foot { margin-top: 26px; text-align: center; font-size: 12.5px; color: var(--text-faint); }

@media (max-width: 860px) {
    body { padding: 16px; place-items: start center; }
    .login-shell { grid-template-columns: 1fr; min-height: 0; max-width: 460px; }
    .hero { padding: 24px; min-height: 0; }
    .hero-art { width: 190px; position: absolute; right: -18px; top: 6px; margin: 0; opacity: 0.95; }
    .hero-copy { margin-top: 64px; max-width: 64%; }
    .hero-copy h1 { font-size: 26px; }
    .hero-copy p { font-size: 13.5px; }
    .form-side { padding: 28px 16px 20px; }
    .login-box .mark { display: none; }
}
@media (max-width: 420px) {
    .hero-art { width: 150px; }
    .hero-copy { max-width: 70%; }
    .input-wrap input { font-size: 16px; } /* stop iOS zoom */
}
</style>
</head>

<body>

<main class="login-shell">
    <section class="hero" aria-hidden="true">
        <div class="hero-brand"><img src="{{ asset('images/brand/mark.svg') }}" alt=""> Soap Opera</div>
        <img class="hero-art" src="{{ asset('images/brand/bubbles.svg') }}" alt="" width="380" height="326">
        <div class="hero-copy">
            <h1>Fresh loads.<br>Zero drama.</h1>
            <p>Orders, customers, inventory and sales for your laundry shop, all in one tidy place.</p>
        </div>
    </section>

    <section class="form-side">
        <div class="login-box">
            <img class="mark" src="{{ asset('images/brand/mark.svg') }}" alt="">
            <h2>Welcome back</h2>
            <p class="lead">Sign in to {{ $system->business_name ?? 'Soap Opera' }}</p>

            @if (session('error'))
                <div class="login-error" role="alert">
                    <i class="fas fa-circle-exclamation"></i>
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="post">
                @csrf

                <div class="field">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <input type="text" id="username" name="username" value="{{ old('username') }}"
                               autocomplete="username" placeholder="Enter your username"
                               class="{{ session('error') ? 'error' : '' }}" required autofocus>
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password"
                               autocomplete="current-password" placeholder="Enter your password"
                               class="{{ session('error') ? 'error' : '' }}" required>
                        <i class="fas fa-lock"></i>
                        <button type="button" class="toggle-pass" aria-label="Show password" aria-pressed="false"><i class="fas fa-eye"></i></button>
                    </div>
                </div>

                <button type="submit" class="login-btn">Sign in</button>
            </form>

            <p class="login-foot">&copy; {{ date('Y') }} Soap Opera &middot; Laundry management</p>
        </div>
    </section>
</main>

<script>
// Show / hide password
document.querySelector('.toggle-pass').addEventListener('click', function () {
    const input = document.getElementById('password');
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    this.setAttribute('aria-pressed', show ? 'true' : 'false');
    this.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    this.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
});

// Prevent double submits
document.querySelector('form').addEventListener('submit', function () {
    const btn = this.querySelector('.login-btn');
    btn.disabled = true;
    btn.textContent = 'Signing in...';
});
</script>

</body>
</html>
