<?php
include "login_check.php";

if (isUserLogedin()) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $query = "SELECT password, verified FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($u = $result->fetch_assoc()) {
        if (password_verify($pass, $u['password'])) {
            if ($u['verified'] == 1) {
                setcookie("user", $email, time() + 3600, "/");
                setcookie("password", $pass, time() + 3600, "/");
                $_SESSION['log'] = true;
                header("Location: index.php");
                exit();
            } else {
                header("Location: login.php?error=Account not verified!");
                exit();
            }
        }
    }

    $stmt->close();
    header("Location: login.php?error=Login failed!");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/images/favicon.ico" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 400px;
            width: 100%;
        }

        .login-container h1 {
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            color: #333333;
            text-align: center;
        }

        .alert {
            display: none;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            background-color: transparent;
            border: none;
        }

        .alert-error {
            color: #ff4d4d;
            border: 1px solid #ff4d4d;
            background-color: #fff0f0;
        }

        .alert-success {
            color: #4caf50;
            border: 1px solid #4caf50;
            background-color: #f0fff0;
        }

        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-group input {
            width: 100%;
            padding: 0.75rem 2.5rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            background-color: #f9f9f9;
        }

        .input-group .icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            color: #888888;
        }

        .input-group input::placeholder {
            color: #b0b0b0;
            font-size: 0.95rem;
        }

        .input-group .toggle-password {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.25rem;
            color: #888888;
            cursor: pointer;
        }

        .remember-me {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .remember-me input {
            margin-right: 0.5rem;
        }

        .remember-me label {
            color: #333333;
            font-size: 0.9rem;
        }

        .forgot-password {
            color: #6366f1;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .forgot-password:hover {
            color: #4f46e5;
        }

        .signup-option {
            text-align: center;
            margin-top: 1.5rem;
        }

        .signup-option a {
            color: #6366f1;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .signup-option a:hover {
            color: #4f46e5;
        }

        .login-container button {
            width: 100%;
            padding: 0.75rem;
            background-color: #6366f1;
            border: none;
            border-radius: 8px;
            color: #ffffff;
            font-size: 1.25rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
            margin-top: 1rem;
        }

        .login-container button:hover {
            background-color: #4f46e5;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h1>Login</h1>
        <form action="login.php" method="POST">
            <div id="loginAlert" class="alert">
                <?php
                if (isset($_GET['error'])) {
                    echo htmlspecialchars($_GET['error']);
                } elseif (isset($_GET['success'])) {
                    echo htmlspecialchars($_GET['success']);
                }
                ?>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope icon"></i>
                <input id="email" name="email" type="email" placeholder="Enter your email" required oninput="handleInput()">
            </div>
            <div class="input-group">
                <i class="fas fa-lock icon"></i>
                <input id="password" name="password" type="password" placeholder="Enter your password" required oninput="handleInput()">
                <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility()"></i>
            </div>
            <div class="remember-me">
                <label>
                    <input type="checkbox" id="rememberMe" name="rememberMe">
                    Remember me
                </label>
                <a href="forgot-password.php" class="forgot-password">Forgot your password?</a>
            </div>
            <button type="submit">LOG IN</button>
        </form>

        <div class="signup-option">
            <p>Don't have an account? <a href="register.php">Sign Up</a></p>
        </div>
    </div>

    <script>
        window.onload = function () {
            const alertBox = document.getElementById('loginAlert');
            if (alertBox.textContent.trim() !== "") {
                alertBox.style.display = "block";
                if (location.search.includes("error")) {
                    alertBox.classList.add('alert-error');
                } else if (location.search.includes("success")) {
                    alertBox.classList.add('alert-success');
                }
            }
        };

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        function handleInput() {
            const alertBox = document.getElementById('loginAlert');
            alertBox.style.display = "none";
            alertBox.textContent = "";
            alertBox.classList.remove('alert-error', 'alert-success');
        }
    </script>
</body>

</html>
