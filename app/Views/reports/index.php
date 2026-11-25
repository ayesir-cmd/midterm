<?php use App\Helpers\View; ?>
<form method="get" action="/reports" class="filter-bar">
  <label>Start <input type="date" name="start" value="<?= View::e($filters['start']) ?>"></label>
  <label>End <input type="date" name="end" value="<?= View::e($filters['end']) ?>"></label>
  <label>Farm <select name="farm"><option value="">All farms</option><?php foreach ($farms as $farm): ?><option value="<?= View::e($farm['farm_id']) ?>" <?= $filters['farm']===$farm['farm_id']?'selected':'' ?>><?= View::e($farm['label']) ?></option><?php endforeach; ?></select></label>
  <button class="button primary" type="submit">Run report</button>
  <a class="button secondary" href="/reports">Clear</a>
</form>
<section class="dashboard-metrics">
  <article class="card"><span>Total harvested quantity</span><strong><?= number_format($quantity,2) ?> kg</strong></article>
  <article class="card"><span>Estimated value</span><strong>PHP <?= number_format($value,2) ?></strong></article>
</section>
<section class="card">
  <div class="table-actions"><h2>Harvest records</h2><button class="button secondary" type="button" onclick="window.print()">Print</button></div>
  <div class="table-wrap"><table><thead><tr><th>Date</th><th>Farm</th><th>Crop</th><th>Qty kg</th><th>Price/kg</th><th>Grade</th><th>Remark</th></tr></thead><tbody>
  <?php foreach ($rows as $row): ?><tr><td><?= View::e($row['harvest_date']) ?></td><td><?= View::e($row['farm_name']) ?></td><td><?= View::e($row['crop_name']) ?></td><td><?= View::e($row['quantity_kg']) ?></td><td><?= $row['selling_price_per_kg']===null?'N/A':'PHP '.View::e($row['selling_price_per_kg']) ?></td><td><?= View::e($row['grade_name']) ?></td><td><?= View::e($row['remark_description']) ?></td></tr><?php endforeach; ?>
  </tbody></table></div>
</section>
