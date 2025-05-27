<?php
    // Simulate user data (Replace this with actual session or database data)
    $currentPage = basename($_SERVER['PHP_SELF']); // Get the current page

    // Example data for user
    $isLoggedIn = isset($name) && $name !== 'Guest';
    ?>

<div class="sidebar open bg-nav_and_side_menu_bg">
    <div class="logo-details">
        <i class='bx bx-registered icon' style='color:var(--nav-text)'></i>
        <div class="logo_name text-nav_text">Admin Panel.</div>
        <i class='bx bx-menu hidden md:block' style="color:var(--nav-text)" id="btn"></i>
    </div>
    <ul class="nav-list">
        <li class="<?php echo ($currentPage == 'user_list.php') ? 'active' : ''; ?>">
            <a href="user_list.php">
                <i class='bx bx-user'></i>
                <span class="links_name">User List</span>
            </a>
            <span class="tooltip">User List</span>
        </li>
        <li class="<?php echo ($currentPage == 'extension_list.php') ? 'active' : ''; ?>">
            <a href="extension_list.php">
                <i class='bx bx-user'></i>
                <span class="links_name">Extension List</span>
            </a>
            <span class="tooltip">Extension List</span>
        </li>
        <!-- Other menu items -->
    </ul>


    <!-- Logout Section -->
    <div class="profile-section">
        <!-- <div class='divider'></div> -->

        <?php if ($isLoggedIn): ?>
        <div class='sidebar-logout'>
            <div class='logout-bg'>
                <p>Log Out</p>
                <svg xmlns="http://www.w3.org/2000/svg"" viewBox=" 0 0 24 24">
                    <path d="m13 16 5-4-5-4v3H4v2h9z"></path>
                    <path
                        d="M20 3h-9c-1.103 0-2 .897-2 2v4h2V5h9v14h-9v-4H9v4c0 1.103.897 2 2 2h9c1.103 0 2-.897 2-2V5c0-1.103-.897-2-2-2z">
                    </path>
                </svg>
            </div>
        </div>
        <?php endif; ?>

        <div class='sidebar-theme-toggle'>
            <div class='toggle-bg'>
                <p>Theme Controller</p>
                <!-- Theme-controller -->
                <label class="grid cursor-pointer place-items-center">
                    <input type="checkbox" value="dark"
                        class="toggle theme-controller col-span-2 col-start-1 row-start-1" />
                    <svg class="stroke-black dark:stroke-white fill-white col-start-1 row-start-1"
                        xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5" />
                        <path
                            d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4" />
                    </svg>
                    <svg class="stroke-base-100 fill-white col-start-2 row-start-1" xmlns="http://www.w3.org/2000/svg"
                        width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </label>
            </div>
        </div>

        <?php if ($isLoggedIn): ?>
        <div class='sidebar-profile'>
            <!-- If user is logged in, show profile picture and details -->
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="profile-pic">
            <div class="profile-info">
                <!-- Restrict length of name and email to 21 characters in PHP and use ellipsis in CSS -->
                <span
                    class="name"><?php echo htmlspecialchars(strlen($name) > 21 ? substr($name, 0, 21) . '...' : $name); ?></span>
                <span
                    class="email"><?php echo htmlspecialchars(strlen($email) > 21 ? substr($email, 0, 21) . '...' : $email); ?></span>
            </div>
        </div>

        <?php else: ?>
        <!-- If user is not logged in, show login button -->
        <a href="login.php" class="login-button flex items-center">
            <button class="btn-login">Login</button>
        </a>
        <?php endif; ?>

    </div>
</div>