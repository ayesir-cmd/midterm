<?php use App\Helpers\View; ?>
<div class="login-shell">
  <section class="login-visual">
    <div class="login-mark"><img src="/assets/icons/rootsy-mark.png" alt=""></div>
    <h2>Growing clearer decisions.</h2>
    <p>Secure farm, crop, planting and harvest management for Cebu-area operations.</p>
  </section>
  <section class="login-card">
    <h1>Welcome back</h1>
    <p class="muted">Sign in to your Rootsy account</p>
    <?php if (!empty($_SESSION['flash_error'])): ?>
      <div class="alert alert-error"><?= View::e($_SESSION['flash_error']) ?></div>
      <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>
    <form method="post" action="/login" class="stacked-form">
      <input type="hidden" name="csrf_token" value="<?= View::e($token) ?>">
      <label for="email">Email</label>
      <input id="email" name="email" type="email" autocomplete="username" required>
      <label for="password">Password</label>
      <div class="password-wrap">
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button type="button" id="togglePassword" aria-label="Show password">Show</button>
      </div>
      <label class="check-row"><input type="checkbox" name="remember"> <span>Remember me</span></label>
      <button class="button primary" type="submit">Sign in</button>
    </form>
    <a class="access-link" href="/account-access">Need account access?</a>
  </section>
</div>
