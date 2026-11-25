<?php use App\Helpers\View; ?>
<?php foreach ($lookups as $table => $rows): ?>
<section class="card lookup-card"><h2><?= View::e(str_replace('_',' ',ucfirst($table))) ?></h2><div class="table-wrap"><table><tbody>
<?php foreach ($rows as $row): ?><tr><?php foreach ($row as $value): ?><td><?= View::e((string)$value) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
</tbody></table></div></section>
<?php endforeach; ?>
