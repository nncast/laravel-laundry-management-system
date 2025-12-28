<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laundry Box Login</title>

<!-- Preload Fonts for Speed -->
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- FontAwesome (defer load for speed) -->
<link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" as="style" onload="this.rel='stylesheet'">

<style>
:root {
    --blue: #007bff;
    --text-dark: #2c3e50;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Poppins', sans-serif;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;

    /* TEMP background while main image loads */
    background: linear-gradient(135deg, #1b1b1b, #3a3a3a);
    position: relative;
}

/* Async Background Loader */
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

/* Left side */
.image-section {
    flex: 1; 
    background: rgba(255, 255, 255, 0.15); 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    color: white; 
    text-shadow: 0 3px 10px rgba(0,0,0,0.5); 
    backdrop-filter: blur(4px);
}

.image-section h1 {
    font-size: 38px; font-weight: 700; text-align: center; padding: 20px;
}

/* Right Side */
.login-section {
    flex:1;
    background:rgba(255,255,255,0.92);
    display:flex;
    align-items:center;
    justify-content:center;
}

.login-box { width:80%; max-width:340px; }
.login-box h2 { text-align:center; margin-bottom:25px; color:var(--text-dark); }

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

.login-btn {
    width:100%;
    background:var(--blue);
    color:white;
    border:none;
    padding:12px;
    border-radius:8px;
    font-size:15px;
    cursor:pointer;
    transition:.2s;
}
.login-btn:hover { background:#0056b3; }

@media(max-width:850px){
    .image-section {display:none;}
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

            <form action="/login" method="post">
                @csrf
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
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
</script>

</body>
</html>
