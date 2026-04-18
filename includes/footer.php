<?php // File: includes/footer.php ?>
  <footer class="mt-5">
    <div class="container text-center">
      <p class="mb-1">🩸 Blood Donor Finder &mdash; Connecting patients with donors</p>
      <p class="mb-0"><small>Built with PHP &amp; MySQL · Bootstrap 5</small></p>
    </div>
  </footer>
  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Project JS -->
  <script src="../assets/js/main.js"></script>
  <!-- Auto-dismiss flash messages after 4 seconds -->
  <script>
    setTimeout(function() {
      document.querySelectorAll('.alert-dismissible').forEach(function(el) {
        bootstrap.Alert.getOrCreateInstance(el).close();
      });
    }, 4000);
  </script>
</body>
</html>