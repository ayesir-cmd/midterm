<?php use App\Helpers\View; ?>
<section class="card">
  <h2>Create account</h2>
  <form method="post" action="/users" class="form-grid">
    <input type="hidden" name="csrf_token" value="<?= View::e($token) ?>">
    <label>Full name<input name="full_name" maxlength="100" required></label>
    <label>Email<input name="email" type="email" maxlength="254" required></label>
    <label>Role<select name="role_id" required><option value="">Choose role</option><?php foreach ($roles as $role): ?><option value="<?= View::e($role['role_id']) ?>"><?= View::e($role['role_name']) ?></option><?php endforeach; ?></select></label>
    <label>Temporary password<input name="temporary_password" type="password" minlength="12" required></label>
    <button class="button primary" type="submit">Create account</button>
  </form>
</section>
<section class="card"><h2>Accounts</h2><div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Password change</th></tr></thead><tbody>
<?php foreach ($users as $row): ?><tr><td><?= View::e($row['full_name']) ?></td><td><?= View::e($row['email']) ?></td><td><?= View::e($row['role_name']) ?></td><td><?= $row['is_active']?'Yes':'No' ?></td><td><?= $row['must_change_password']?'Required':'Complete' ?></td></tr><?php endforeach; ?>
</tbody></table></div></section>
