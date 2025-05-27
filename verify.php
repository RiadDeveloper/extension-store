<?php
include "db_conn_B.php";
$verificationStatus = '';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE users SET code = '', verified = 1 WHERE code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $verificationStatus = 'success';
    } else {
        $verificationStatus = 'invalid';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Email Verification</title>
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

        .verification-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .verification-container h1 {
            margin-bottom: 1rem;
            font-size: 1.5rem;
            color: #333333;
        }

        .verification-container p {
            font-size: 1rem;
            color: #666666;
            margin-bottom: 1.5rem;
        }

        .verification-container .success {
            color: #4CAF50;
        }

        .verification-container .error {
            color: #F44336;
        }

        .verification-container button {
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

        .verification-container button:hover {
            background-color: #4f46e5;
        }
    </style>
</head>

<body>
    <div class="verification-container">
        <?php if ($verificationStatus === 'success') { ?>
            <h1>Email Verified!</h1>
            <p class="success">Your email has been successfully verified. You can now log in.</p>
            <button onclick="window.location.href='login.php'">Log In</button>
        <?php } elseif ($verificationStatus === 'invalid') { ?>
            <h1>Invalid Link</h1>
            <p class="error">The verification link is invalid or has expired.</p>
            <button onclick="window.location.href='login.php'">Log In</button>
        <?php } else { ?>
            <h1>No Verification Code Provided</h1>
            <p>Please use a valid verification link.</p>
            <button onclick="window.location.href='login.php'">Log In</button>
        <?php } ?>
    </div>
</body>

</html>
