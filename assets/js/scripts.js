
// scripts.js - UI behavior enhancements

document.addEventListener("DOMContentLoaded", function () {
  // Auto-dismiss alerts after 4 seconds
  document.querySelectorAll(".alert").forEach(alert => {
    setTimeout(() => {
      alert.classList.add("fade");
      setTimeout(() => alert.remove(), 500);
    }, 4000);
  });
});
