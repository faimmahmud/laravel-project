</main>

<footer class="site-footer mt-5">
  <div class="container py-5">
    <div class="row gy-4">
      <div class="col-lg-5">
        <h3 class="fw-bold text-white mb-3"><?= e($site['brand']) ?></h3>
        <p class="text-white-50 mb-0">A premium tourism experience with cinematic visuals, elegant motion, and a clean luxury feel.</p>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="text-uppercase text-white-50 letter-wide">Explore</h6>
        <ul class="list-unstyled footer-links">
          <li><a href="<?= e(app_path('destinations.php')) ?>">Destinations</a></li>
          <li><a href="<?= e(app_path('packages.php')) ?>">Packages</a></li>
          <li><a href="<?= e(app_path('world.php')) ?>">World Explorer</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="text-uppercase text-white-50 letter-wide">Account</h6>
        <ul class="list-unstyled footer-links">
          <li><a href="<?= e(app_path('login.php')) ?>">Login</a></li>
          <li><a href="<?= e(app_path('register.php')) ?>">Register</a></li>
          <li><a href="<?= e(app_path('booking.php')) ?>">Booking</a></li>
        </ul>
      </div>
      <div class="col-lg-3">
        <h6 class="text-uppercase text-white-50 letter-wide">Newsletter</h6>
        <p class="text-white-50">Premium travel updates and featured destinations.</p>
        <form class="newsletter-form">
          <div class="input-group">
            <input type="email" class="form-control" placeholder="Email address">
            <button class="btn btn-gold" type="button">Join</button>
          </div>
        </form>
      </div>
    </div>
    <hr class="border-white border-opacity-10 my-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 text-white-50 small">
      <span>© <?= date('Y') ?> <?= e($site['brand']) ?>. All rights reserved.</span>
      <span>Luxury arc-style tourism website.</span>
    </div>
  </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('assets/js/script.js')) ?>"></script>
</body>
</html>
