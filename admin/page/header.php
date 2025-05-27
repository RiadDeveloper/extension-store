
<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/custom.css">
    <link rel="shortcut icon" href="assets/images/favicon.ico" />
    <link rel="stylesheet" href="./assets/css/Sidebar.css">
    <link rel="stylesheet" href="./assets/css/dropdown.css">
    <link rel="stylesheet" href="x.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <header>
        <!------ Navbar starts here ------>
        <nav class="navbar w-full rounded-none fixed z-50">
            <div class="navbar-start text-2xl">
                <!-- Button for mobile -->
                <i class='bx bx-menu px-2 pb-1 visible md:invisible' id="mbl-btn"></i>
                <a href="index.php" class="font-extrabold mobile-hidden pl-2 md:pl-9">Riad Dev.</a>
            </div>

            <div class="navbar-nav-right flex justify-right w-full" id="navbar-collapse">
                <!-- Search -->
                <form class='flex w-searchbar items-center' action="" method="GET">
                    <input class='w-full search-box' type="text" name="search" placeholder="Search..."
                        value="<?php if (isset($_GET['search'])) { echo $_GET['search']; } ?>">
                    <button class='inline mar-right-search' type="submit">
                    <i class="bx bx-search fs-4 lh-0" style="color: #8099f1;"></i>
                    </button>
                </form>

                <!-- Profile container -->
                <div class="profile-container">
                    <?php if ($name === 'Guest'): ?>
                    <!-- Show this button when user is not logged in -->
                    <a href="login.php" class="login-button flex items-center">
                        <button class="btn-login">Login</button>
                    </a>
                    <?php else: ?>
                    <!-- Show this when user is logged in -->
                    <div class="flex margin-profile-left items-center cursor-pointer" id="user-profile">
                        <img class="size-8 rounded-full" src="<?php echo $profile_pic; ?>"
                            alt="Profile Picture">
                        <!-- No name is shown here -->
                        <i class="bx mobile-hidden bx-chevron-down dropdown-arrow"></i>
                    </div>

                    

                    <!-- Dropdown menu -->
                    <div id="dropdown-menu" class="dropdown-menu">
                        <a href="profile.php">
                            <i class="bx bx-user"></i> My Profile
                        </a>
                        <a href="Riad-developer\index.php">
                            <i class="bx bx-home-smile"></i> Main panel
                        </a>     
                        <a href="logout.php">
                            <i class="bx bx-log-out"></i> Logout
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <script>
    // Toggle dropdown menu visibility when the user clicks on the profile section
    document.getElementById("user-profile").addEventListener("click", function(event) {
        var dropdown = document.getElementById("dropdown-menu");
        dropdown.classList.toggle("show");
        event.stopPropagation(); // Prevent the event from bubbling up to the document
    });

    // Hide dropdown menu when clicking outside
    document.addEventListener("click", function(event) {
        var dropdown = document.getElementById("dropdown-menu");
        var profile = document.getElementById("user-profile");
        if (!profile.contains(event.target)) {
            dropdown.classList.remove("show");
        }
    });
    </script>
</body>

</html>
