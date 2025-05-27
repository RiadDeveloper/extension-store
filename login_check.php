<?php
session_start();
include "db_conn_B.php";

function userlogout()
{
    session_destroy();
    setcookie('user', '', time() - 3600, '/');
    setcookie('password', '', time() - 3600, '/');
    setcookie('name', '', time() - 3600, '/');
    header("Location: index.php");
    exit();
}

function isUserLogedin()
{
    global $conn;
    
    if (isset($_COOKIE["user"]) && isset($_COOKIE['password'])) {
        $user = $_COOKIE['user'];
        $pass = $_COOKIE['password'];
        
        try {
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($u = $result->fetch_assoc()) {
                if (password_verify($pass, $u['password'])) {
                    // Refresh cookies and session data
                    setcookie("user", $user, time() + 3600, "/");
                    setcookie("password", $pass, time() + 3600, "/");
                    setcookie("name", $u['name'], time() + 3600, "/");

                    $_SESSION['log'] = true;
                    $_SESSION['trust_leve'] = $u['trust_leve'];
                    $_SESSION['user_id'] = $u['serial'];
                    return true;
                }
            }

            $stmt->close();
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
        }
    }

    return false;
}

function getUserProfile()
{
    global $conn;

    if (isUserLogedin()) {
        $email = $_COOKIE['user'];
        
        try {
            $query = "SELECT name, email, profile_pic, trust_leve FROM users WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();
            $stmt->close();

            if ($userData) {
                return [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'profile_pic' => $userData['profile_pic'] ?: 'https://avatar.iran.liara.run/public/boy',
                    'trust_leve' => $userData['trust_leve']
                ];
            }
        } catch (Exception $e) {
            error_log("Error fetching user profile: " . $e->getMessage());
        }
    }

    // Return default guest information if not logged in
    return [
        'name' => 'Guest',
        'email' => 'Guest',
        'profile_pic' => 'https://avatar.iran.liara.run/public/boy',
        'trust_leve' => 'guest'
    ];
}

function isAdmin()
{
    return isset($_SESSION['trust_leve']) && $_SESSION['trust_leve'] === 'admin';
}
?>
