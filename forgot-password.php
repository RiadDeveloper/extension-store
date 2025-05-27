<?php
include "db_conn_B.php";

if (isset($_POST['email'])) {
    $email = $_POST['email'];

    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $code = md5(rand());
        $update_query = "UPDATE users SET code = ? WHERE email = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ss", $code, $email);
        $update_stmt->execute();
        $update_stmt->close();

        // Send reset password email via Google Script
        $url = 'https://script.google.com/macros/s/AKfycbyiJ0vyC0MWf2gjMAYip_1jC1-XLV4ZZR8t9sD2lyaTiW9lqp05iCAT4IplAu5DXnRm/exec';
        $data = array(
            'to' => $email,
            'subject' => 'Reset Password',
            'body' => 'Click on this link to reset your password: <a href="http://localhost/Riad-developer-web/reset-password.php?code=' . $code . '">Reset Password</a>'
        );

        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ),
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            header("Location: forgot-password.php?e=Email sending failed! Please try again.");
            exit();
        } else {
            header("Location: forgot-password.php?s=A reset password link has been sent to your email.");
            exit();
        }

    } else {
        header("Location: forgot-password.php?e=Email not found!");
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
    <title>Forgot Password</title>
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

        .forgot-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 400px;
            width: 100%;
            position: relative;
        }

        .forgot-container h1 {
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

        .forgot-container button {
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

        .forgot-container button:hover {
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

        .login-option {
            text-align: center;
            margin-top: 1.5rem;
        }

        .login-option a {
            color: #6366f1;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .login-option a:hover {
            color: #4f46e5;
        }
    </style>
</head>

<body>
    <div class="forgot-container">
        <h1>Forgot Password</h1>
        <form method="POST" action="forgot-password.php" onsubmit="showLoading()">
            <!-- Error or Success Message -->
            <div id="forgotAlert" class="alert" style="display: none;">
                <!-- Error or Success message will be inserted here by JavaScript -->
            </div>

            <div class="input-group">
                <i class="fas fa-envelope icon"></i>
                <input id="email" name="email" type="email" placeholder="Enter your email" required oninput="clearAlert()">
            </div>
            <button type="submit">RESET PASSWORD</button>
        </form>

        <div class="login-option">
            <p>Remembered your password? <a href="login.php">Log in</a></p>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner"></div>
    </div>

    <script>
        function clearAlert() {
            const alertBox = document.getElementById('forgotAlert');
            alertBox.style.display = "none";
            alertBox.textContent = "";
        }

        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        // Display the alert message if it is set
        document.addEventListener('DOMContentLoaded', function () {
            const alertBox = document.getElementById('forgotAlert');
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
