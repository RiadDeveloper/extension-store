<?php
include "login_check.php";
include "db_conn_B.php";

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and is an admin
if (!isUserLogedin() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

$user_id = $_GET['serial'] ?? '';

if (!$user_id) {
    header("Location: user_list.php");
    exit();
}

// Fetch user data
$sql = "SELECT * FROM users WHERE serial = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: user_list.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $verified = isset($_POST['verified']) ? 1 : 0;

    $query = "UPDATE users SET";
    $params = [];
    $types = "";

    if ($name !== "") {
        $query .= " name = ?,";
        $params[] = $name;
        $types .= "s";
    }
    if ($email !== "") {
        $query .= " email = ?,";
        $params[] = $email;
        $types .= "s";
    }
    if ($password !== "") {
        $query .= " password = ?,";
        $params[] = password_hash($password, PASSWORD_BCRYPT);
        $types .= "s";
    }
    $query .= " verified = ? WHERE serial = ?";
    $params[] = $verified;
    $params[] = $user_id;
    $types .= "ii";

    // Remove trailing comma
    $query = rtrim($query, ",");

    // Prepare statement
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    // Execute statement
    if ($stmt->execute()) {
        $_SESSION['message'] = "User updated successfully!";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: user_list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <style>
        /* Include your styles here */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 60%;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .form-group button {
            background-color: #007bff;
            color: #ffffff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-group button:hover {
            background-color: #0056b3;
        }

        .alert {
            color: #fff;
            background-color: #28a745;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit User</h2>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert"><?php echo htmlspecialchars($_SESSION['message']); ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['serial']); ?>" />
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" />
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" />
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Leave empty to keep current password" />
            </div>
            <div class="form-group">
                <label for="verified">Verified</label>
                <select id="verified" name="verified">
                    <option value="1" <?php if ($user['verified']) echo 'selected'; ?>>Yes</option>
                    <option value="0" <?php if (!$user['verified']) echo 'selected'; ?>>No</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" name="update_user">Update User</button>
            </div>
        </form>
    </div>
</body>
</html>
