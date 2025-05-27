<?php
include "login_check.php";
include "db_conn_B.php";

if (isUserLogedin()) {
    if (isset($_COOKIE['user'])) {
        $email = $_COOKIE['user'];
        $name = isset($_COOKIE['name']) ? $_COOKIE['name'] : null;
        $profile_pic = isset($_COOKIE['profile_pic']) ? $_COOKIE['profile_pic'] : 'default_pic.png';
    }
} elseif (isset($_SESSION['log'])) {
    if (isset($_COOKIE['user'])) {
        $email = $_COOKIE['user'];
        $name = isset($_COOKIE['name']) ? $_COOKIE['name'] : null;
        $profile_pic = isset($_COOKIE['profile_pic']) ? $_COOKIE['profile_pic'] : 'default_pic.png';
    }
}

// Start session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isUserLogedin() || !isAdmin()) {
    header("Location: login.php");
    exit();
}


// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $query = "DELETE FROM extension WHERE extension_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $delete_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Extension deleted successfully!";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }
    $stmt->close();

    header("Location: extension_list.php");
    exit();
}

// Handle search and filtering
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$paid_free = isset($_GET['paid_free']) ? trim($_GET['paid_free']) : '';

$search_query = '';
$params = [];
$param_types = '';

if ($search || $paid_free) {
    $search_query = "WHERE 1=1";
    if ($search) {
        $search_query .= " AND extension_name LIKE ?";
        $params[] = "%$search%";
        $param_types .= "s";
    }
    if ($paid_free) {
        $search_query .= " AND price = ?";
        $params[] = $paid_free;
        $param_types .= "s";
    }
}

$query = "SELECT e.id, e.extension_name, e.Latest_Version, e.Platform, e.price, e.last_update, e.extension_id, e.Released_On, e.user_id, u.profile_pic, u.name, u.email 
          FROM extension e
          LEFT JOIN users u ON e.user_id = u.serial
          $search_query";

$stmt = $conn->prepare($query);

if ($param_types) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Get the message from session if it exists
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';

// Clear the message after displaying it
unset($_SESSION['message']);
?>

<?php include 'page/header.php'; ?>

  <div>
    <!---------------------Drawer Sidebar Here---------------------->
    <?php include 'page/sidebar.php'; ?>

    <!------------------------ Main contents are here --------------------------->
    <section class="home-section flex flex-col items-center justify-center">
      <main class="full-height-top">
          

 
 
 <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            margin: 60px auto;
            padding: 20px;

        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header-title {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
        }

        .add-extension-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .add-extension-btn:hover {
            background-color: #45a049;
        }

        .success-message, .error-message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
        }

        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
        }

        .error-message {
            background-color: #f2dede;
            color: #a94442;
        }

        .filter-section {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-section label {
            font-size: 16px;
        }

        .filter-section select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            outline: none;
            background: var(--free-card-bg);
            
        }

        .extension-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
        }

        .extension-card {
            background-color: var(--free-card-bg);
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            position: relative;
            transition: transform 0.3s;
        }

        .extension-card:hover {
            transform: translateY(-5px);
        }

        .extension-card h3 {
            font-size: 20px;
            margin: 0;
        }

        .extension-card p {
            margin: 8px 0;
        }

        .extension-card .actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 8px;
        }

        .extension-card .actions button {
            background-color: #007bff;
            border: none;
            color: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .extension-card .actions button:hover {
            background-color: #0056b3;
        }

        .extension-card .actions .delete {
            background-color: #dc3545;
        }

        .extension-card .actions .delete:hover {
            background-color: #c82333;
        }

        .user-profile-section {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .profile-name {
            font-weight: bold;
        }

        .profile-email {
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .header-title {
                font-size: 20px;
            }

            .add-extension-btn {
                padding: 8px 15px;
                font-size: 14px;
            }


            .filter-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-section select {
                width: 100%;
                margin-bottom: 10px;

            }
        }

        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="header-title">Extension List</div>
        <a href="add_extension.php" class="add-extension-btn">Add New Extension</a>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <label for="paid_free">Paid/Free:</label>
        <select name="paid_free" id="paid_free" onchange="window.location.href='extension_list.php?search=<?php echo htmlspecialchars($search); ?>&paid_free=' + this.value;">
            <option value="">All</option>
            <option value="Paid" <?php echo $paid_free === 'Paid' ? 'selected' : ''; ?>>Paid</option>
            <option value="Free" <?php echo $paid_free === 'Free' ? 'selected' : ''; ?>>Free</option>
        </select>
    </div>

    <!-- Message Section -->
    <?php if ($message): ?>
        <p class="success-message"><?php echo $message; ?></p>
    <?php endif; ?>

    <!-- Extension Cards -->
    <div class="extension-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="extension-card">
                    <div class="actions">
                        <a href="view_extension.php?id=<?php echo urlencode($row['id']); ?>"><button>View</button></a>
                        <a href="update_extension.php?id=<?php echo urlencode($row['extension_id']); ?>"><button>Edit</button></a>
                        <a href="?delete_id=<?php echo urlencode($row['extension_id']); ?>" onclick="return confirm('Are you sure you want to delete this extension?');"><button class="delete">Delete</button></a>
                    </div>
                    <?php if ($row['user_id']): ?>
                        <div class="user-profile-section">
                            <img src="<?php echo htmlspecialchars($row['profile_pic']); ?>" alt="User Profile Picture" class="profile-pic">
                            <div>
                                <span class="profile-name"><?php echo htmlspecialchars($row['name']); ?></span><br>
                                <span class="profile-email"><?php echo htmlspecialchars($row['email']); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($row['extension_name']); ?></h3>
                    <p><strong>Version:</strong> <?php echo htmlspecialchars($row['Latest_Version']); ?></p>
                    <p><strong>Platform:</strong> <?php echo htmlspecialchars($row['Platform']); ?></p>
                    <p><strong>Paid/Free:</strong> <?php echo htmlspecialchars($row['price']); ?></p>
                    <p><strong>Last Update:</strong> <?php echo htmlspecialchars($row['last_update']); ?></p>
                    <p><strong>Released On:</strong> <?php echo htmlspecialchars($row['Released_On']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div>No extensions found.</div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

      </main>

      <!-------------------------  Footer Section Here ------------------------->
      <?php include 'page/footer.php'; ?>
    </section>
  </div>



  <!-- JavaScript -->
  <script src="js/theme-toggle.js" defer></script>
  <script src="js/sidebar-toggle.js" defer></script>
  <script src="js/script.js" defer></script>
</body>
</html>