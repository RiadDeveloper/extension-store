<?php
include "db_conn_B.php";
include "login_check.php";

// Get the user ID from the cookie
$user_email = $_COOKIE['user'] ?? null;

if (!$user_email) {
    die("Error: User email is not set. Please log in again.");
}

// Fetch user data from the database using the email stored in the cookie
$stmt = $conn->prepare("SELECT serial, name, email, profile_pic FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("Error: User not found. Please log in again.");
}

$user_id = $user['serial'];
$update_message = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Update name with validation
    if (!empty($name) && strlen($name) <= 50) {
        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE serial = ?");
        if ($stmt) {
            $stmt->bind_param("si", $name, $user_id);
            if ($stmt->execute()) {
                $update_message = "Name updated successfully.";
            } else {
                $update_message = "Failed to update name.";
            }
            $stmt->close();
        }
    } elseif (strlen($name) > 50) {
        $update_message = "Name should not exceed 50 characters.";
    }

    // Update password with validation
    if (!empty($password) && strlen($password) >= 8) {
        if ($password === $confirm_password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE serial = ?");
            if ($stmt) {
                $stmt->bind_param("si", $hashed_password, $user_id);
                if ($stmt->execute()) {
                    $update_message = "Password updated successfully.";
                } else {
                    $update_message = "Failed to update password.";
                }
                $stmt->close();
            }
        } else {
            $update_message = "Passwords do not match.";
        }
    } elseif (!empty($password) && strlen($password) < 8) {
        $update_message = "Password should be at least 8 characters long.";
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $img_data = file_get_contents($_FILES['profile_pic']['tmp_name']);
        $img_base64 = base64_encode($img_data);
        $api_key = '30bd8c5d96c248a5b10850c46df5befb';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.imgbb.com/1/upload");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'key' => $api_key,
            'image' => $img_base64,
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        $response_data = json_decode($response, true);

        if (isset($response_data['data']['url'])) {
            $profile_pic_url = $response_data['data']['url'];
            $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE serial = ?");
            if ($stmt) {
                $stmt->bind_param("si", $profile_pic_url, $user_id);
                if ($stmt->execute()) {
                    $update_message = "Profile picture updated successfully.";
                } else {
                    $update_message = "Failed to update profile picture.";
                }
                $stmt->close();
            }
        } else {
            $update_message = "Failed to upload profile picture.";
        }
    }

    // Refresh user data after update
    $stmt = $conn->prepare("SELECT name, email, profile_pic FROM users WHERE serial = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/profile.css">
</head>
<body>
    <section class="home-section flex flex-col items-center justify-center">
        <form method="POST" action="profile.php" onsubmit="showLoading()" enctype="multipart/form-data">
            <div class="profile-picture">
                <div class="image-container">
                    <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture"
                        id="profilePreview">
                    <div class="overlay">
                    </div>
                </div>
                <input type="file" name="profile_pic" id="profile_pic" accept="image/*"
                    onchange="previewProfilePicture(event)">
                <label for="profile_pic" class="upload-button">Change Picture</label>
            </div>

            <div class="form-group">
                <label for="name">Username:</label>
                <div class="input-wrapper">
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>"
                        maxlength="50" required oninput="clearMessage()">
                    <i class="fas fa-user input-icon"></i>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <div class="input-wrapper email">
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                        readonly>
                    <i class="fas fa-envelope input-icon"></i>
                </div>
            </div>
            <div class="form-group">
                <label for="password">New Password:</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" oninput="clearMessage()">
                    <i class="fas fa-lock input-icon"></i>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <div class="input-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password"
                        oninput="checkPasswordMatch(); clearMessage();">
                    <i class="fas fa-lock input-icon"></i>
                    <small id="passwordHelp" class="password-help">Passwords do not match!</small>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="submit-button">Update Profile</button>
            </div>
        </form>
        <!-- Dynamic message area -->
        <?php if (!empty($update_message)): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($update_message); ?>
        </div>
        <?php endif; ?>
    </section>
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner"></div>
    </div>

    <script>
    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function previewProfilePicture(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('profilePreview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    function checkPasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const passwordHelp = document.getElementById('passwordHelp');

        if (password && confirmPassword && password !== confirmPassword) {
            passwordHelp.style.display = 'block';
        } else {
            passwordHelp.style.display = 'none';
        }
    }

    function clearMessage() {
        const message = document.querySelector('.message');
        if (message) {
            message.style.display = 'none';
        }
    }

    window.addEventListener('load', () => {
        const message = document.querySelector('.message');
        if (message) {
            setTimeout(() => {
                message.style.display = 'none';
            }, 5000); // Hide the message after 5 seconds
        }
    });
    </script>
    <script src="js/theme-toggle.js" defer></script>
    <script src="js/sidebar-toggle.js" defer></script>
    <script src="js/script.js" defer></script>
</body>

</html>