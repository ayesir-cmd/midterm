<?php use App\Helpers\View; ?>
<section class="card"><div class="table-wrap"><table><thead><tr><th>Time</th><th>Actor</th><th>Action</th><th>Entity</th><th>Key</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><?= View::e($row['created_at']) ?></td><td><?= View::e($row['full_name'] ?? 'System') ?></td><td><?= View::e($row['action']) ?></td><td><?= View::e($row['entity_table']) ?></td><td><?= View::e($row['record_pk']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></section>
