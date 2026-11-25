<?php use App\Helpers\View; ?>
<section class="stats-grid">
  <?php foreach ([
    'Farmer' => $totals['farmer_count'], 'Farm' => $totals['farm_count'], 'Crop' => $totals['crop_count'],
    'Planting' => $totals['planting_count'], 'Harvest' => $totals['harvest_count']
  ] as $label => $value): ?>
    <article class="stat-card"><span><?= View::e($label) ?>s</span><strong><?= View::e((string)$value) ?></strong></article>
  <?php endforeach; ?>
</section>
<section class="dashboard-metrics">
  <article class="card"><span>Total harvested</span><strong><?= number_format((float)$totals['harvest_kg'],2) ?> kg</strong></article>
  <article class="card"><span>Estimated harvest value</span><strong>PHP <?= number_format((float)$totals['harvest_value'],2) ?></strong><small>Where prices are recorded</small></article>
</section>
<section class="dashboard-columns">
  <article class="card">
    <h2>Recent plantings</h2>
    <div class="table-wrap"><table><thead><tr><th>Farm</th><th>Crop</th><th>Status</th><th>Date</th></tr></thead><tbody>
    <?php foreach ($plantings as $row): ?><tr><td><?= View::e($row['farm_name']) ?></td><td><?= View::e($row['crop_name']) ?></td><td><?= View::e($row['status_name']) ?></td><td><?= View::e($row['planting_date']) ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
  </article>
  <article class="card">
    <h2>Plantings by status</h2>
    <ul class="status-list"><?php foreach ($statuses as $row): ?><li><span><?= View::e($row['status_name']) ?></span><strong><?= View::e((string)$row['planting_count']) ?></strong></li><?php endforeach; ?></ul>
  </article>
  <article class="card">
    <h2>Recent harvests</h2>
    <div class="table-wrap"><table><thead><tr><th>Farm</th><th>Crop</th><th>Kg</th><th>Date</th></tr></thead><tbody>
    <?php foreach ($harvests as $row): ?><tr><td><?= View::e($row['farm_name']) ?></td><td><?= View::e($row['crop_name']) ?></td><td><?= View::e($row['quantity_kg']) ?></td><td><?= View::e($row['harvest_date']) ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
  </article>
</section>
