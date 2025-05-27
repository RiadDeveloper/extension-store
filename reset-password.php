<?php
include "db_conn_B.php";
include "login_check.php";

$isValidCode = false; // Assume the code is invalid by default
$msg = ''; // Initialize the message
$passwordChanged = false; // Flag to track if the password was changed successfully

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Check if the code exists in the database
    $query = "SELECT * FROM users WHERE code = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $isValidCode = true; // The code is valid, so enable the form

        if (isset($_POST['new_password']) && isset($_POST['confirm_new_password'])) {
            $new_password = $_POST['new_password'];
            $confirm_new_password = $_POST['confirm_new_password'];

            if ($new_password === $confirm_new_password) {
                if (strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $email = $result->fetch_assoc()['email'];

                    // Update password in the database
                    $update_query = "UPDATE users SET password = ?, code = NULL WHERE email = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("ss", $hashed_password, $email);
                    if ($update_stmt->execute()) {
                        $msg = "Password reset successfully!";
                        $msgType = "success";
                        $passwordChanged = true; // Mark that the password was changed successfully
                    } else {
                        $msg = "Password reset failed!";
                        $msgType = "error";
                    }
                    $update_stmt->close();
                } else {
                    $msg = "New password must be at least 6 characters!";
                    $msgType = "error";
                }
            } else {
                $msg = "New password and confirm password do not match!";
                $msgType = "error";
            }
        }
    } else {
        $msg = "Invalid reset code!";
        $msgType = "error";
    }

    $stmt->close();
} else {
    header("Location: forgot-password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Reset Password</title>
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

    .reset-container {
      background-color: #ffffff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      padding: 2rem;
      max-width: 400px;
      width: 100%;
    }

    .reset-container h1 {
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

    .alert-success {
      color: #4caf50;
    }

    .alert-error {
      color: #e53e3e;
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

    .input-group.success input {
      border-color: #4caf50;
      background-color: #e6ffe6;
    }

    .input-group.invalid input {
      border-color: #e53e3e;
      background-color: #ffe5e5;
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

    .reset-container button {
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

    .reset-container button:hover {
      background-color: #4f46e5;
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

    .login-option button {
      background-color: #6366f1;
      border: none;
      border-radius: 8px;
      color: #ffffff;
      font-size: 1.25rem;
      font-weight: bold;
      cursor: pointer;
      padding: 0.75rem 1.5rem;
      transition: background-color 0.3s ease-in-out;
      margin-top: 1rem;
    }

    .login-option button:hover {
      background-color: #4f46e5;
    }
  </style>
</head>

<body>
  <div class="reset-container">
    <h1><?php echo $isValidCode ? "Reset Password" : "Invalid Reset Code"; ?></h1>
    <form method="POST" action="reset-password.php?code=<?php echo htmlspecialchars($code); ?>" <?php echo $isValidCode ? '' : 'style="display:none;"'; ?>>
      <?php if (isset($msg)) { ?>
        <div id="resetAlert" class="alert <?php echo ($msgType === 'success') ? 'alert-success' : 'alert-error'; ?>">
          <?php echo htmlspecialchars($msg); ?>
        </div>
      <?php } ?>
      <div class="input-group <?php echo ($passwordChanged) ? 'success' : (!$isValidCode ? 'invalid' : ''); ?>">
        <i class="fas fa-lock icon"></i>
        <input id="new_password" name="new_password" type="password" placeholder="New Password" required <?php echo (!$isValidCode || $passwordChanged) ? 'disabled' : ''; ?>>
        <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('new_password')"></i>
      </div>
      <div class="input-group <?php echo ($passwordChanged) ? 'success' : (!$isValidCode ? 'invalid' : ''); ?>">
        <i class="fas fa-lock icon"></i>
        <input id="confirm_new_password" name="confirm_new_password" type="password" placeholder="Confirm New Password" required <?php echo (!$isValidCode || $passwordChanged) ? 'disabled' : ''; ?>>
        <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('confirm_new_password')"></i>
      </div>
      <?php if (!$passwordChanged) { ?>
        <button type="submit">RESET PASSWORD</button>
      <?php } ?>
    </form>

    <?php if ($passwordChanged) { ?>
      <div class="login-option">
        <button onclick="window.location.href='login.php'">Go to Login</button>
      </div>
    <?php } else if (!$isValidCode) { ?>
      <div class="login-option">
        <button onclick="window.location.href='forgot-password.php'">OK</button>
      </div>
    <?php } ?>
  </div>

  <script>
    window.onload = function () {
      const alertBox = document.getElementById('resetAlert');
      if (alertBox && alertBox.textContent.trim() !== "") {
        alertBox.style.display = "block";
      }
    };

    function togglePasswordVisibility(fieldId) {
      const passwordInput = document.getElementById(fieldId);
      const toggleIcon = passwordInput.nextElementSibling;
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
  </script>
</body>

</html>
