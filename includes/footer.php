<div class="mt-auto">
  <footer class="bg-dark text-center text-light py-3">
    <div class="container">
      <span>&copy; <?= date('Y') ?> Secure Lab Access System</span>
    </div>
  </footer>
</div>

<!-- Bootstrap 5 JavaScript Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (needed for some advanced features) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Custom JavaScript -->
<script>
  // Toggle sidebar on mobile
  document.addEventListener('DOMContentLoaded', function() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const sidebar = document.querySelector('.sidebar-nav');
    
    if (navbarToggler && sidebar) {
      navbarToggler.addEventListener('click', function() {
        sidebar.classList.toggle('show');
      });
    }
    
    // Add active class to current page in sidebar
    const currentLocation = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    
    sidebarLinks.forEach(link => {
      const linkPath = link.getAttribute('href');
      if (currentLocation.endsWith(linkPath)) {
        link.classList.add('active');
      }
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
      new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Notification dropdown behavior (just for demo)
    const notificationDropdown = document.getElementById('notificationsDropdown');
    if (notificationDropdown) {
      notificationDropdown.addEventListener('click', function(e) {
        e.preventDefault();
        alert('Notifications feature coming soon!');
      });
    }
    
    // Card hover effects
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
      card.addEventListener('mouseenter', function() {
        this.classList.add('shadow-lg');
      });
      card.addEventListener('mouseleave', function() {
        this.classList.remove('shadow-lg');
      });
    });
  });
</script>
</body>
</html>