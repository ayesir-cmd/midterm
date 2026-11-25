<?php use App\Helpers\View; ?>
<section class="card narrow">
  <h2>Change your password</h2>
  <p class="muted">This is required before administrative or operational access continues.</p>
  <form method="post" action="/change-password" class="form-grid">
    <input type="hidden" name="csrf_token" value="<?= View::e($token) ?>">
    <label for="password">New password</label>
    <input id="password" name="password" type="password" minlength="12" required>
    <label for="password_confirmation">Confirm password</label>
    <input id="password_confirmation" name="password_confirmation" type="password" minlength="12" required>
    <button class="button primary" type="submit">Update password</button>
  </form>
</section>
