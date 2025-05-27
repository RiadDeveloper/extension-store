let sidebar = document.querySelector(".sidebar");
let closeBtn = document.querySelector("#btn");
let closeMblBtn = document.querySelector("#mbl-btn");
function mobileSideBar(isMobile) {
  if (isMobile.matches) {
    sidebar.classList.toggle("open");
  }
}
let isMobile = window.matchMedia("(max-width: 768px)");
mobileSideBar(isMobile);
closeBtn.addEventListener("click", () => {
  sidebar.classList.toggle("open");
  menuBtnChange();
});
closeMblBtn.addEventListener("click", () => {
  sidebar.classList.toggle("open");
  menuBtnChange();
});
function menuBtnChange() {
  if (sidebar.classList.contains("open")) {
    closeBtn.classList.replace("bx-menu", "bx-menu-alt-right");//replacing the iocns class
    // closeMblBtn.classList.replace("bx-menu", "bx-x");//replacing the iocns class
  } else {
    closeBtn.classList.replace("bx-menu-alt-right", "bx-menu");//replacing the iocns class
    // closeMblBtn.classList.replace("bx-x", "bx-menu");//replacing the iocns class
  }
}
