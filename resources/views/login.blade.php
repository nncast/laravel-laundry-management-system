<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laundry Box Login</title>

<!-- Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root {
    --blue: #007bff;
    --light-gray: rgba(255,255,255,0.85);
    --text-dark: #2c3e50;
}

/* Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: url('{{ asset('laundry.jpeg') }}') no-repeat center center/cover;
}

/* Optional overlay for better text readability */
body::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.45);
    z-index: 0;
}

/* Container */
.login-container {
    position: relative;
    z-index: 1;
    display: flex;
    width: 900px;
    height: 520px;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.3);
}

/* Left Section */
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
    font-size: 38px;
    font-weight: 700;
    text-align: center;
    padding: 20px;
}

/* Right Login Section */
.login-section {
    flex: 1;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
}

.login-box {
    width: 80%;
    max-width: 380px;
}

.login-box h2 {
    text-align: center;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 30px;
}

.input-group {
    position: relative;
    margin-bottom: 20px;
}

.input-group i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
}

.input-group input {
    width: 100%;
    padding: 12px 12px 12px 40px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    transition: 0.2s;
}

.input-group input:focus {
    border-color: var(--blue);
    outline: none;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.1);
}

.login-btn {
    width: 100%;
    background: var(--blue);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    transition: 0.2s ease;
}

.login-btn:hover {
    background: #0056b3;
}

.options {
    text-align: center;
    margin-top: 15px;
    font-size: 13px;
}

.options a {
    color: var(--blue);
    text-decoration: none;
    font-weight: 500;
}

.options a:hover {
    text-decoration: underline;
}

@media (max-width: 850px) {
    .image-section {
        display: none;
    }
    .login-section {
        flex: 1;
    }
}
</style>
</head>

<body>

<div class="login-container">
    <!-- Left section -->
    <div class="image-section">
        <h1>Fresh Clothes, Fresh Start<br>💧 Laundry Box</h1>
    </div>

    <!-- Right login form -->
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
                <button type="submit" class="login-btn">Login</button>
            </form>

        </div>
    </div>
</div>

</body>
</html>
