<?php use App\Helpers\View; $r=$record;?>
<section class="card narrow"><form method="post" action="/crops" class="form-grid"><input type="hidden" name="csrf_token" value="<?=View::e($token)?>"><input type="hidden" name="id" value="<?=$r?(int)$r['crop_id']:''?>">
<label>Crop name<input name="crop_name" maxlength="80" value="<?=View::e($r['crop_name']??'')?>" required></label><label>Variety<input name="variety" maxlength="80" value="<?=View::e($r['variety']??'')?>" required></label>
<label>Category<select name="category_id" required><option value="">Choose category</option><?php foreach($categories as$x):?><option value="<?=(int)$x['category_id']?>" <?=($r['category_id']??0)==$x['category_id']?'selected':''?>><?=View::e($x['label'])?></option><?php endforeach;?></select></label>
<label>Growing period (days)<input name="growing_period_days" type="number" min="1" value="<?=View::e($r['growing_period_days']??'')?>" required></label>
<label>Season<select name="season_id" required><option value="">Choose season</option><?php foreach($seasons as$x):?><option value="<?=(int)$x['season_id']?>" <?=($r['season_id']??0)==$x['season_id']?'selected':''?>><?=View::e($x['label'])?></option><?php endforeach;?></select></label>
<div class="form-actions"><button class="button primary">Save</button><a class="button secondary" href="/crops">Cancel</a></div></form></section>
