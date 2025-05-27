<?php
include "db_conn_B.php";
include "login_check.php";

if (isUserLogedin()) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['email']) && isset($_POST['username']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate password
    if (strlen($password) < 6) {
        header("Location: register.php?e=Please enter a strong password!");
        exit();
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    }

    // Validate email
    if (!str_contains($email, "")) {
        header("Location: register.php?e=Please enter a valid email!");
        exit();
    }

    // Validate username
    if (strlen($username) < 3) {
        header("Location: register.php?e=Please enter a valid username!");
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT serial, code FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($serial, $code);
    
    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        $stmt->close();
        
        if ($code) {
            header("Location: register.php?e=This email is already registered but not verified! Please verify your email.");
        } else {
            header("Location: register.php?e=This email is already registered! Please login.");
        }
        exit();
    }
    $stmt->close();

    // Insert new user into database
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        $verification_code = md5(rand());

        $stmt = $conn->prepare("UPDATE users SET code = ? WHERE email = ?");
        $stmt->bind_param("ss", $verification_code, $email);
        $stmt->execute();

        // Send verification email using Google Apps Script
        $url = 'https://script.google.com/macros/s/AKfycbyiJ0vyC0MWf2gjMAYip_1jC1-XLV4ZZR8t9sD2lyaTiW9lqp05iCAT4IplAu5DXnRm/exec';
        $post_data = http_build_query([
            'to' => $email,
            'subject' => 'Email Verification',
            'body' => 'Please click on the link to verify your email: <a href="http://localhost//Riad-developer/verify.php?code=' . $verification_code . '">Verify Email</a>'
        ]);

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => $post_data,
            ],
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            header("Location: register.php?e=Email sending failed! Please try again.");
            exit();
        } else {
            header("Location: register.php?s=We've sent a verification link to your email address.");
            exit();
        }

    } else {
        header("Location: register.php?e=Registration failed! Please try again.");
        exit();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Register</title>
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

        .register-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 400px;
            width: 100%;
            position: relative;
        }

        .register-container h1 {
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            color: #333333;
            text-align: center;
        }

        .alert {
            font-size: 0.9rem;
            margin-bottom: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            background-color: transparent;
            border: none;
        }

        .alert-error {
            color: #ff4d4d;
        }

        .alert-success {
            color: #4CAF50;
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

        .input-group .toggle-password {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.25rem;
            color: #888888;
            cursor: pointer;
        }

        .register-container button {
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

        .register-container button:hover {
            background-color: #4f46e5;
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-overlay .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #6366f1;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>Sign Up</h1>
        <form method="POST" action="register.php" class="space-y-4" onsubmit="showLoading()">
            <!-- Error or Success Message -->
            <div id="registerAlert" class="alert" style="display: none;">
                <!-- Error or Success message will be inserted here by JavaScript -->
            </div>

            <div class="input-group">
                <i class="fas fa-user icon"></i>
                <input id="username" name="username" type="text" placeholder="Username" required oninput="clearAlert()">
            </div>
            <div class="input-group">
                <i class="fas fa-envelope icon"></i>
                <input id="email" name="email" type="email" placeholder="Email" required oninput="clearAlert()">
            </div>
            <div class="input-group">
                <i class="fas fa-lock icon"></i>
                <input id="password" name="password" type="password" placeholder="Password" required oninput="clearAlert()">
                <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility()"></i>
            </div>
            <button type="submit">SIGN UP</button>
        </form>
        <div class="flex flex-col mt-4 items-center justify-center text-sm">
            <h3>Already have an account? 
                <a class="group text-blue-400 transition-all duration-100 ease-in-out" href="login.php">
                    <span class="bg-left-bottom bg-gradient-to-r from-blue-400 to-blue-400 bg-[length:0%_2px] bg-no-repeat group-hover:bg-[length:100%_2px] transition-all duration-500 ease-out">
                        Login
                    </span>
                </a>
            </h3>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner"></div>
    </div>

    <script>
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

        function clearAlert() {
            const alertBox = document.getElementById('registerAlert');
            alertBox.style.display = "none";
            alertBox.textContent = "";
        }

        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        // Display the alert message if it is set
        document.addEventListener('DOMContentLoaded', function () {
            const alertBox = document.getElementById('registerAlert');
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('e');
            const success = urlParams.get('s');

            if (error) {
                alertBox.textContent = error;
                alertBox.className = "alert alert-error";
                alertBox.style.display = "block";
            } else if (success) {
                alertBox.textContent = success;
                alertBox.className = "alert alert-success";
                alertBox.style.display = "block";
            }
        });
    </script>
</body>
</html>
