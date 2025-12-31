<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>

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
    height: 100vh;
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
    width:900px;
    height:520px;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 5px 25px rgba(0,0,0,0.3);
    background:rgba(255,255,255,0.1);
}

/* Left */
.image-section {
    flex:1;
    background: rgba(255,255,255,0.15);
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    text-shadow:0 3px 10px rgba(0,0,0,.5);
    backdrop-filter: blur(4px);
}
.image-section h1 {
    font-size:38px;
    font-weight:700;
    text-align:center;
}

/* Right */
.login-section {
    flex:1;
    background:rgba(255,255,255,0.92);
    display:flex;
    align-items:center;
    justify-content:center;
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

@media(max-width:850px){
    .image-section { display:none; }
}
</style>
</head>

<body>

<div class="login-container">
    <div class="image-section">
        <h1>Fresh Clothes, Fresh Start<br>💧 Laundry Box</h1>
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

            <form action="/login" method="post">
                @csrf

                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text"
                           name="username"
                           placeholder="Username"
                           class="{{ session('error') ? 'error' : '' }}"
                           required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password"
                           name="password"
                           placeholder="Password"
                           class="{{ session('error') ? 'error' : '' }}"
                           required>
                </div>

                <button class="login-btn">Login</button>
            </form>
        </div>
    </div>
</div>

<script>
const img = new Image();
img.src = "{{ asset('images/laundry.jpeg') }}";
img.onload = () => document.body.classList.add("loaded");

// Auto-hide error
setTimeout(() => {
    const err = document.querySelector('.login-error');
    if (err) err.style.display = 'none';
}, 4000);
</script>

</body>
</html>
