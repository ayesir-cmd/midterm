<?php use App\Helpers\View; $r=$record;?>
<section class="card narrow"><form method="post" action="/farmers" class="form-grid">
<input type="hidden" name="csrf_token" value="<?= View::e($token) ?>"><input type="hidden" name="id" value="<?= $r?(int)$r['farmer_id']:'' ?>">
<label>First name<input name="first_name" maxlength="50" value="<?= View::e($r['first_name']??'') ?>" required></label>
<label>Last name<input name="last_name" maxlength="50" value="<?= View::e($r['last_name']??'') ?>" required></label>
<label>Contact number<input name="contact_number" maxlength="20" value="<?= View::e($r['contact_number']??'') ?>"></label>
<label>Address<input name="address" maxlength="150" value="<?= View::e($r['address']??'') ?>" required></label>
<label>Registration date<input type="date" name="date_registered" value="<?= View::e($r['date_registered']??date('Y-m-d')) ?>" required></label>
<div class="form-actions"><button class="button primary" type="submit">Save</button><a class="button secondary" href="/farmers">Cancel</a></div></form></section>
