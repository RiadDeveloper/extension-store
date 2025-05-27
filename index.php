<!------------------- Header is here -------------------->
  <?php include 'page/header.php'; ?>
  <!------------------------ Content Here -------------------------->
  <div>
    <!---------------------Drawer Sidebar Here---------------------->
    <?php include 'page/sidebar.php'; ?>

    <!------------------------ Main contents are here --------------------------->
    <section class="home-section flex flex-col items-center justify-center">
      <main class="full-height-top">
        <!-- Full container of Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 bg-cards_section_bg gap-3 px-3 py-7 pt-[90px]">
          <?php
          $query_q = "SELECT * FROM extension ORDER BY id DESC";
          $files_q = mysqli_query($conn, $query_q);
          if (isset($_GET['search'])) {
            $search_q = strtolower($_GET['search']);
            if ($search_q == "free") {
              $query_q = "SELECT * FROM extension WHERE price='FREE' ORDER BY id DESC";
            } elseif ($search_q == "paid") {
              $query_q = "SELECT * FROM extension WHERE price='PAID' ORDER BY id DESC";
            } else {
              $query_q = "SELECT * FROM extension WHERE extension_name LIKE '%$search_q%' ORDER BY id DESC";
            }
            $files_q = mysqli_query($conn, $query_q);
          }
          while ($file = mysqli_fetch_assoc($files_q)) {
            ?>
            <div class="join join-vertical m-1 p-0 mb-0 flex flex-col max-w-full h-full place-items-center rounded-3xl md:rounded-2xl 
        <?php if ($file['price'] == "PAID") { ?>
            <?php echo 'box after:bg-paid_card_bg text-paid_card_text ' ?>
            <?php } else { ?>
                <?php echo 'bg-free_card_bg text-free_card_text shadow-[0_0_5px_1px_#dbdbdb66] border-2 border-primary' ?>
                <?php } ?> flex-grow">

              <div class="flex-grow py-3 flex flex-col justify-between w-full pl-2">
                <div>
                  <h4 class="text-2xl font-bold text-center pb-3"><?php echo $file['extension_name']; ?></h4>
                  <p><img class="inline font-semibold w-6 py-1" src="https://i.ibb.co/vZ84Sz0/image.png"><span
                      class="font-semibold"> Released On:
                    </span><?php echo $file['Released_On']; ?></p>
                  <p><img class="inline font-semibold w-6 py-1" src="https://i.ibb.co/41KDs1C/image.png"><span
                      class="font-semibold"> Last Update :
                    </span><?php echo $file['last_update']; ?></p>
                  <p><img class="inline font-semibold w-6 py-1" src="https://i.ibb.co/gTZWmvs/image.png"><span
                      class="font-semibold"> Current Version :
                    </span><?php echo $file['Latest_Version']; ?></p>
                  <p><img class="inline font-semibold w-6 py-1" src="https://i.ibb.co/QvTCR9z/image.png"><span
                      class="font-semibold"> Price :
                    </span><?php echo $file['price']; ?></p>
                  <?php if ($file['price'] == "PAID") { ?>
                    <p><img class="inline font-semibold w-6 py-1" src="https://i.ibb.co/pdDVyy0/image.png"><span
                        class="font-semibold"> Extension Price:
                      </span><?php echo $file['extension_price']; ?></p>
                  <?php } ?>
                </div>
                <div class="w-full px-2">
                  <div class="w-full">
                    <h6 class="m-0">Platform</h6>
                    <small class="text-muted"><?php echo $file['Platform']; ?></small>
                  </div>
                </div>
              </div>

              <a href="view_extension.php?id=<?php
              echo $file['id'];
              ?>" class="btn border-0 uppercase <?php if ($file['price'] == "PAID") {
                echo "bg-paid_card_button_color hover:bg-paid_card_button_color_hover text-paid_card_button_text";
              } else {
                echo "bg-free_card_button_color hover:bg-free_card_button_color_hover text-free_card_button_text hover:text-white dark:hover:text-black";
              } ?> w-full h-4 join-item font-bold">
                <?php if ($file['price'] == "PAID") {
                  echo "Buy Now";
                } else {
                  echo "Download";
                } ?></a>
              <?php
              if ($file['price'] == "PAID") {
                ?>
                <img loading="lazy" src="https://i.postimg.cc/fTWkHK9C/premium-quality.png"
                  class="absolute size-[32px] top-[13px] right-[11px]">
              <?php } ?>
            </div>
            <?php
          }
          if (mysqli_num_rows($files_q) < 1) {
            ?>
            <div class="col-start-2 flex flex-col bg-gray-700 rounded-2xl text-center">
              <h2 class="m-3">No Search results</h2>
              <img class="noFound" src="assets\images\no-search-results.gif" />
            </div>
            <?php
          } ?>
        </div>
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