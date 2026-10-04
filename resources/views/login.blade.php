<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - {{ $system->business_name ?? 'Laundry' }}</title>
@if(!empty($system->favicon))
<link rel="icon" href="{{ asset($system->favicon) }}?v={{ optional($system->updated_at)->timestamp }}" type="image/x-icon">
@endif

<!-- Preload Fonts -->
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- FontAwesome -->
<link rel="preload"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
      as="style"
      onload="this.rel='stylesheet'">

<style>
:root {
    --blue: #007bff;
    --text-dark: #2c3e50;
    --error: #b00020;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
    min-height: 100dvh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #1b1b1b, #3a3a3a);
    position: relative;
}

body.loaded {
    background: url("{{ asset('images/laundry.jpeg') }}") no-repeat center/cover;
}

body::before {
    content:"";
    position:absolute;
    inset:0;
    background:rgba(0,0,0,0.45);
    z-index:0;
}

.login-container {
    position:relative;
    z-index:1;
    display:flex;
    width:min(900px, calc(100% - 32px));
    min-height:520px;
    margin:16px;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 20px 50px rgba(0,0,0,0.35);
    border:1px solid rgba(255,255,255,0.18);
}

/* Left: frosted glass panel over the background photo */
.image-section {
    position:relative;
    flex:1;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:18px;
    padding:40px 30px;
    color:#fff;
    text-shadow:0 2px 12px rgba(0,0,0,.35);
    background:linear-gradient(135deg, rgba(255,255,255,0.22) 0%, rgba(255,255,255,0.06) 100%);
    -webkit-backdrop-filter:blur(16px) saturate(140%);
    backdrop-filter:blur(16px) saturate(140%);
    border-right:1px solid rgba(255,255,255,0.25);
    box-shadow:inset 0 1px 0 rgba(255,255,255,0.35);
}
/* Soft light sheen across the glass */
.image-section::before {
    content:"";
    position:absolute;
    inset:0;
    background:radial-gradient(120% 80% at 0% 0%, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0) 60%);
    pointer-events:none;
}
/* Browsers without backdrop-filter: use a darker tint so the text stays readable */
@supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
    .image-section { background:rgba(20,30,45,0.55); }
}
.image-section .brand-icon {
    position:relative;
    width:84px;
    height:84px;
    border-radius:22px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:40px;
    color:#fff;
    background:linear-gradient(135deg, rgba(0,123,255,0.9), rgba(0,86,179,0.9));
    box-shadow:0 10px 25px rgba(0,0,0,0.25), inset 0 1px 0 rgba(255,255,255,0.35);
    text-shadow:none;
}
.image-section h1 {
    position:relative;
    font-size:36px;
    line-height:1.25;
    font-weight:700;
    text-align:center;
}
.image-section h1 span {
    display:block;
    margin-top:6px;
    font-size:30px;
}

/* Right */
.login-section {
    flex:1;
    background:rgba(255,255,255,0.97);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:40px 0;
}

.login-box { width:80%; max-width:340px; }
.login-box h2 {
    text-align:center;
    margin-bottom:20px;
    color:var(--text-dark);
}

/* Error */
.login-error {
    background:#ffe6e6;
    color:var(--error);
    border:1px solid #f5c2c7;
    padding:10px 12px;
    border-radius:8px;
    font-size:14px;
    margin-bottom:16px;
    display:flex;
    align-items:center;
    gap:8px;
}

/* Inputs */
.input-group {
    position:relative;
    margin-bottom:16px;
}
.input-group i {
    position:absolute;
    left:12px;
    top:50%;
    transform:translateY(-50%);
    color:#777;
}
.input-group input {
    width:100%;
    padding:12px 12px 12px 40px;
    border:1px solid #bbb;
    border-radius:8px;
    transition:.2s;
}
.input-group input:focus {
    border-color:var(--blue);
    outline:none;
}
.input-group input.error {
    border-color:var(--error);
}

/* Button */
.login-btn {
    width:100%;
    background:var(--blue);
    color:white;
    border:none;
    padding:12px;
    border-radius:8px;
    font-size:15px;
    cursor:pointer;
}
.login-btn:hover { background:#0056b3; }

.login-box h2 i { color:var(--blue); }

@media(max-width:850px){
    .image-section { display:none; }
    .login-container { width:min(440px, calc(100% - 32px)); min-height:0; }
}
@media(max-width:480px){
    .login-box { width:86%; }
    .input-group input { font-size:16px; } /* stop iOS zoom */
}
</style>
</head>

<body>

<div class="login-container">
    <div class="image-section">
        <div class="brand-icon"><i class="fas fa-soap"></i></div>
        <h1>Fresh Clothes, Fresh Start<span>Laundry Box</span></h1>
    </div>

    <div class="login-section">
        <div class="login-box">

            <h2><i class="fas fa-soap"></i> Sign In</h2>

            {{-- Error Message --}}
            @if (session('error'))
                <div class="login-error">
                    <i class="fas fa-circle-exclamation"></i>
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="post">
                @csrf

                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text"
                           name="username"
                           value="{{ old('username') }}"
                           autocomplete="username"
                           placeholder="Username"
                           class="{{ session('error') ? 'error' : '' }}"
                           required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password"
                           name="password"
                           autocomplete="current-password"
                           placeholder="Password"
                           class="{{ session('error') ? 'error' : '' }}"
                           required>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>
        </div>
    </div>
</div>

<script>
const img = new Image();
img.src = "{{ asset('images/laundry.jpeg') }}";
img.onload = () => document.body.classList.add("loaded");

// Prevent double submits
document.querySelector('form').addEventListener('submit', function () {
    const btn = this.querySelector('.login-btn');
    btn.disabled = true;
    btn.textContent = 'Signing in...';
});
</script>

</body>
</html>
