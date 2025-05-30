<?php

include "db_conn_B.php";
require 'vendor/autoload.php';

if (isset($_GET['id'])) {
  $id = $_GET['id'];
} else {
  header("Location: index.php");
}

$file_q = mysqli_query($conn, "SELECT * FROM extension WHERE id='$id'");
if (mysqli_num_rows($file_q) < 1) {
  header("Location: index.php");
}
;
while ($f = mysqli_fetch_assoc($file_q)) {
  $file = $f;
}

// Initialize Parsedown
$parsedown = new Parsedown();
$parsedown->setSafeMode(false); // Allow HTML
$parsedown->setBreaksEnabled(true); // Enable line breaks

?>
    <!------------------- Header is here -------------------->
    <?php include 'page/header.php'; ?>
    <link rel="stylesheet" href="assets/css/markdown.css">
    
    <div>
        <!---------------------Drawer Sidebar Here---------------------->
        <?php include 'page/sidebar.php'; ?>

        <!------------------------ Main contents are here --------------------------->
        <section class="home-section flex flex-col items-center justify-center">
            <div class="main-panel text-free_card_text content-wrapper grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 bg-cards_section_bg gap-3 px-3 py-7 pt-[90px]">
                <div class="card m-2 p-3 w-50%">
                    <div class="extension-info bg-nav_and_side_menu_bg text-nav_text w-full">
                        <h1 class="extension-title">
                            [ <?php echo htmlspecialchars($file['price'], ENT_QUOTES, 'UTF-8'); ?> ]
                            <?php echo htmlspecialchars($file['extension_name'], ENT_QUOTES, 'UTF-8'); ?> Extension
                        </h1>
                        <ul class="extension-details">
                            <li><b>🌎 Last Update: </b><?php echo htmlspecialchars($file['last_update'], ENT_QUOTES, 'UTF-8'); ?></li>
                            <li><b>🌎 Released On: </b><?php echo htmlspecialchars($file['Released_On'], ENT_QUOTES, 'UTF-8'); ?></li>
                            <li><b>⚙️ Latest Version: </b><?php echo htmlspecialchars($file['Latest_Version'], ENT_QUOTES, 'UTF-8'); ?></li>
                            <li><b>🔥 Price: </b> <?php echo htmlspecialchars($file['price'], ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php if ($file['price'] == "PAID") { ?>
                            <li><b>🔥 Extension Price: </b><?php echo htmlspecialchars($file['extension_price'], ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php } ?>
                        </ul>
                    </div>

                    <!-- Documentation Section -->
                    <div class="documentation-section rounded-md bg-nav_and_side_menu_bg text-nav_text w-full p-4">
                        <h2 class="text-lg font-semibold mb-3">Documentation</h2>
                        <div class="documentation-content prose prose-invert max-w-none">
                            <?php
                            function processContent($content) {
                                global $parsedown;
                                
                                // Remove any existing <div class="table-responsive"> wrappers
                                $content = preg_replace('/<div class="table-responsive">(.*?)<\/div>/s', '$1', $content);
                                
                                // Convert HTML entities back to characters for processing
                                $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                
                                // Process the content with Parsedown
                                $html = $parsedown->text($content);
                                
                                // Wrap tables in responsive div
                                $html = preg_replace('/(<table>.*?<\/table>)/s', '<div class="table-responsive">$1</div>', $html);
                                
                                // Fix image paths and add loading attribute
                                $html = preg_replace('/<img([^>]*)>/', '<img$1 loading="lazy">', $html);
                                
                                return $html;
                            }
                            
                            // Process and output the content
                            echo processContent($file['html_content']);
                            ?>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="action-button mt-4">
                        <?php if ($file['price'] == "FREE") { ?>
                        <center>
                            <a href="<?php echo htmlspecialchars($file['Download_link'], ENT_QUOTES, 'UTF-8'); ?>"
                                class="btn btn-gradient-primary btn-fw bg-free_card_bg shadow-[0_0_5px_1px_#dbdbdb66] border-2 border-primary"
                                download>Download</a>
                        </center>
                        <?php } else { ?>
                        <center>
                            <a href="https://t.me/R2_Store1/" target="_blank"
                                class="btn btn-gradient-primary btn-fw bg-free_card_bg text-free_card_text shadow-[0_0_5px_1px_#dbdbdb66] border-2 border-primary">Buy Now</a>
                        </center>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- JavaScript -->
        <script src="js/theme-toggle.js" defer></script>
        <script src="js/sidebar-toggle.js" defer></script>
        <script src="js/script.js" defer></script>
</body>
</html>