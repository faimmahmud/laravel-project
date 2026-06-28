<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf(app_path('login.php'));
    $redirectTo = trim($_POST['redirect'] ?? ($_GET['redirect'] ?? ''));
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $users = read_users();

    foreach ($users as $user) {
        if (strcasecmp($user['email'] ?? '', $email) === 0 && password_verify($password, $user['password'] ?? '')) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'name' => $user['name'] ?? '',
                'email' => $user['email'] ?? '',
                'role' => $user['role'] ?? 'user',
            ];
            flash_set('success', 'Welcome back!');
            redirect(safe_redirect_target($redirectTo, 'index.php'));
        }
    }

    flash_set('danger', 'Invalid email or password.');
    redirect(app_path('login.php'));
}

$pageTitle = 'Login | ' . $site['brand'];
require_once __DIR__ . '/includes/header.php';
?>
<div class="login-wrap">
  <div class="login-panel">
    <div class="login-visual" style="background-image:url('<?= e(travel_img('login-visual')) ?>')">
      <div class="position-relative z-2 h-100 d-flex align-items-end p-4 p-lg-5">
        <div class="text-white">
          <span class="hero-kicker">Secure access</span>
          <h2 class="display-5 fw-bold mt-3">Enter the concierge space</h2>
          <p class="mb-0 text-white-50">Luxury bookings, admin tools, and premium content management.</p>
        </div>
      </div>
    </div>
    <div class="login-body">
      <div class="section-kicker">Login</div>
      <h1 class="section-title mb-3">Welcome back</h1>
      <p class="section-lead">Use the demo admin account or your registered user account.</p>
      <form method="post" class="mt-4 row g-3">
        <?= csrf_field() ?>
        <input type="hidden" name="redirect" value="<?= e(trim($_GET['redirect'] ?? '')) ?>">
        <div class="col-12">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col-12">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="col-12 d-flex flex-wrap gap-2 align-items-center">
          <button class="btn btn-gold px-4" type="submit">Login</button>
          <a href="<?= e(app_path('register.php')) ?>" class="btn btn-outline-dark px-4">Create account</a>
        </div>
      </form>
      <div class="mt-4 small text-muted">
        Admin demo: <strong>admin@demo.com</strong> / <strong>admin123</strong>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
