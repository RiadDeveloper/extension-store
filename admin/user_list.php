<?php
// Include necessary files with proper handling to avoid multiple inclusions
include_once "login_check.php";
include_once "db_conn_B.php";

// Fetch user profile
$userProfile = getUserProfile(); // Assuming getUserProfile is defined in login_check.php
$name = $userProfile['name'];
$email = $userProfile['email'];
$profile_pic = $userProfile['profile_pic'];
$isAdmin = $userProfile['trust_leve'] === 'admin';

// Check if the user is logged in and is an admin
if (!$isAdmin) {
    header("Location: login.php");
    exit();
}


// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $query = "DELETE FROM users WHERE serial = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "User deleted successfully!";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: user_list.php");
    exit();
}

// Handle user update
if (isset($_POST['update_user'])) {
    $serial = $_POST['serial'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $verified = isset($_POST['verified']) ? 1 : 0;
    $profile_pic = $_POST['profile_pic'];
    $trust_leve = $_POST['trust_leve'];
    
    $query = "UPDATE users SET name = ?, email = ?, verified = ?, profile_pic = ?, trust_leve = ? WHERE serial = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssissi", $name, $email, $verified, $profile_pic, $trust_leve, $serial);
    if ($stmt->execute()) {
        $_SESSION['message'] = "User updated successfully!";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: user_list.php");
    exit();
}

// Fetch users based on search criteria and category filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$sql = "SELECT serial, name, email, created_at, verified, profile_pic, trust_leve FROM users 
        WHERE (name LIKE ? OR email LIKE ?) AND (trust_leve = ? OR ? = '')";
$search_param = "%" . $search . "%";
$category_param = $category;
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $search_param, $search_param, $category_param, $category_param);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
$stmt->close();
?>

<?php include 'page/header.php'; ?>

  <div>
    <!---------------------Drawer Sidebar Here---------------------->
    <?php include 'page/sidebar.php'; ?>

    <!------------------------ Main contents are here --------------------------->
    <section class="home-section flex flex-col items-center justify-center">
      <main class="full-height-top">
      <style>
 

 /* Container */
 .container {
   margin: 65px auto;
   padding: 20px;
   box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
   background: var(--cards-section-bg);
 }
 
 /* Header */
 .header {
   display: flex;
   justify-content: space-between;
   align-items: center;
   margin-bottom: 30px;
   padding-bottom: 20px;
   border-bottom: 2px solid #ddd;
 }
 
 .header h2 {
   font-size: 32px;
   color: var(--nav-text);
   margin: 0;
 }
 
 .header .total-users {
   font-size: 18px;
   color: var(--paid-card-text);
 }
 
 /* Search Bar */
 .search-bar {
   display: flex;
   align-items: center;
   margin-bottom: 20px;
   gap: 10px;
 }
 
 .search-bar input[type="text"] {
   flex: 1;
   padding: 10px;
   border: 1px solid #ccc;
   border-radius: 8px;
   font-size: 16px;
   outline: none;
   background-color: var(--search-bar-color);
   color: var(--free-card-text);
 }
 
 .search-bar button {
   background-color: var(--search-button-color);
   border: none;
   color: var(--paid-card-button-text);
   padding: 10px;
   border-radius: 8px;
   cursor: pointer;
   font-size: 16px;
   transition: background-color 0.3s;
 }
 
 .search-bar button:hover {
   background-color: #0056b3;
 }
 
 /* Category Filter */
 .category-filter {
   margin-bottom: 20px;
   display: flex;
   align-items: center;
   gap: 10px;
 }
 
 .category-filter label {
   font-size: 16px;
   color: var(--free-card-text);
 }
 
 .category-filter select {
   padding: 10px;
   border: 1px solid #ccc;
   border-radius: 8px;
   font-size: 16px;
   outline: none;
   background-color: var(--free-card-bg);
   color: var(--free-card-text);
 }
 
 /* User Grid */
 .user-grid {
   display: grid;
   grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
   gap: 20px;
 }
 
 .user-card {
   background-color: var(--paid-card-bg);
   border-radius: 12px;
   box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
   padding: 20px;
   position: relative;
   transition: transform 0.3s;
   border: 2px solid red; /* Red border for the cards */
 }
 
 .user-card:hover {
   transform: translateY(-5px);
 }
 
 .user-card img {
   width: 70px;
   height: 70px;
   border-radius: 50%;
   object-fit: cover;
   margin-bottom: 15px;
 }
 
 .user-card h3 {
   font-size: 24px;
   margin: 0;
   color: var(--nav-text);
 }
 
 .user-card p {
   margin: 8px 0;
   color: var(--free-card-text);
 }
 
 .user-card .actions {
   position: absolute;
   top: 15px;
   right: 15px;
   display: flex;
   gap: 8px;
 }
 
 .user-card .actions button {
   background-color: var(--free-card-button-color);
   border: none;
   color: var(--free-card-button-text);
   padding: 8px 14px;
   border-radius: 8px;
   cursor: pointer;
   font-size: 14px;
   transition: background-color 0.3s;
 }
 
 /* Fix for the edit button in white mode */
 .user-card .actions .edit {
   background-color: var(--edit-button-bg); /* Define this variable in your CSS or set a specific color */
   color: var(--edit-button-text); /* Define this variable in your CSS or set a specific color */
 }
 
 .user-card .actions button:hover {
   background-color: var(--free-card-button-color-hover);
 }
 
 .user-card .actions .delete {
   background-color: #dc3545;
 }
 
 .user-card .actions .delete:hover {
   background-color: #c82333;
 }
 
 /* Popup */
 .popup {
   display: none;
   position: fixed;
   top: 0;
   left: 0;
   width: 100%;
   height: 100%;
   background: rgba(0, 0, 0, 0.6);
   justify-content: center;
   align-items: center;
   z-index: 1000;
 }
 
 .popup-content {
   background: var(--free-card-bg);
   border-radius: 8px;
   width: 90%;
   max-width: 500px;
   padding: 30px;
   position: relative;
   box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
 }
 
 .popup-content h3 {
   margin: 0 0 20px;
   font-size: 24px;
   color: var(--free-card-text);
 }
 
 .popup-close {
   position: absolute;
   top: 15px;
   right: 15px;
   font-size: 24px;
   cursor: pointer;
   color: #999;
 }
 
 .popup-close:hover {
   color: var(--free-card-text);
 }
 
 .popup-form label {
   display: block;
   margin-bottom: 8px;
   font-weight: 500;
   color: var(--free-card-text);
 }
 
 .popup-form input,
 .popup-form select {
   width: calc(100% - 20px);
   padding: 12px;
   margin-bottom: 15px;
   border: 1px solid #ccc;
   border-radius: 4px;
   box-sizing: border-box;
   font-size: 16px;
   background-color: var(--free-card-bg);
   color: var(--free-card-text);
 }
 
 .popup-form button {
   background-color: var(--search-button-color);
   border: none;
   color: var(--paid-card-button-text);
   padding: 12px;
   border-radius: 8px;
   cursor: pointer;
   font-size: 16px;
   transition: background-color 0.3s;
   margin-right: 10px;
 }
 
 .popup-form button:hover {
   background-color: #0056b3;
 }
 
 .popup-form .cancel-btn {
   background-color: #6c757d;
 }
 
 .popup-form .cancel-btn:hover {
   background-color: #5a6268;
 }
 
 /* Responsive Styles */
 @media (max-width: 768px) {
   .header h2 {
     font-size: 28px;
   }
 
   .search-bar input,
   .category-filter select {
     width: 100%;
   }
 
   .popup-content {
     width: 95%;
   }
 }
 
 @media (max-width: 576px) {
   .header {
     flex-direction: column;
     align-items: flex-start;
   }
 
   .header h2 {
     font-size: 24px;
   }
 }
 
 </style>
 </head>
 
 <body>
 
     <div class="container">
         <div class="header">
             <h2>User List</h2>
             <div class="total-users">Total Users: <?php echo count($users); ?></div>
         </div>
 
 
         <div class="category-filter">
             <label for="category">Filter by Trust Level:</label>
             <select name="category" id="category" onchange="window.location.href='user_list.php?search=<?php echo htmlspecialchars($search); ?>&category=' + this.value;">
                 <option value="">All</option>
                 <option value="basic user" <?php echo $category === 'basic user' ? 'selected' : ''; ?>>Basic User</option>
                 <option value="admin" <?php echo $category === 'admin' ? 'selected' : ''; ?>>Admin</option>
             </select>
         </div>
 
         <div class="user-grid">
             <?php foreach ($users as $user) : ?>
                 <div class="user-card">
                     <div class="actions">
                         <?php if (isAdmin()) : ?>
                             <button onclick="openEditPopup(<?php echo htmlspecialchars(json_encode($user)); ?>)">Edit</button>
                             <a href="?delete_id=<?php echo $user['serial']; ?>" onclick="return confirm('Are you sure you want to delete this user?');"><button class="delete">Delete</button></a>
                         <?php endif; ?>
                     </div>
                     <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture">
                     <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                     <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                     <p>Joined: <?php echo date("j M, Y", strtotime($user['created_at'])); ?></p>
                     <p>Verified: <?php echo $user['verified'] ? 'Yes' : 'No'; ?></p>
                     <p>Trust Level: <?php echo htmlspecialchars($user['trust_leve']); ?></p>
                 </div>
             <?php endforeach; ?>
         </div>
     </div>
 
     <!-- Edit User Popup -->
     <div id="edit-popup" class="popup">
         <div class="popup-content">
             <span class="popup-close" onclick="closeEditPopup()">&times;</span>
             <h3>Edit User</h3>
             <form class="popup-form" method="POST">
                 <input type="hidden" name="serial" id="serial">
                 <label for="name">Name</label>
                 <input type="text" name="name" id="name" required>
                 <label for="email">Email</label>
                 <input type="email" name="email" id="email" required>
                 <label for="profile_pic">Profile Picture URL</label>
                 <input type="text" name="profile_pic" id="profile_pic">
                 <label for="trust_leve">Trust Level</label>
                 <select name="trust_leve" id="trust_leve" required>
                     <option value="basic user">Basic User</option>
                     <option value="admin">Admin</option>
                 </select>
                 <label for="verified">Verified</label>
                 <input type="checkbox" name="verified" id="verified">
                 <button type="submit" name="update_user">Update User</button>
                 <button type="button" class="cancel-btn" onclick="closeEditPopup()">Cancel</button>
             </form>
         </div>
     </div>
 
     <script>
         function openEditPopup(user) {
             document.getElementById('serial').value = user.serial;
             document.getElementById('name').value = user.name;
             document.getElementById('email').value = user.email;
             document.getElementById('profile_pic').value = user.profile_pic;
             document.getElementById('trust_leve').value = user.trust_leve;
             document.getElementById('verified').checked = user.verified;
             document.getElementById('edit-popup').style.display = 'flex';
         }
 
         function closeEditPopup() {
             document.getElementById('edit-popup').style.display = 'none';
         }
     </script>
 
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
