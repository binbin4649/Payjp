<?php // Bitcash プラグインがあるか調べる。あれば支払い方法選択式を表示
	$bitcash = false;
	foreach($pluginList as $plugin){
		if($plugin == 'Bitcash'){
			$bitcash = true;
		}
	}
?>
<?php echo $this->Session->flash(); ?>

<h1 class="h5 py-3 mb-3 text-secondary"><?php echo $this->pageTitle ?></h1>
<div class="my-3 mx-sm-5">
	<?php if($bitcash): ?>
		<?php echo $this->BcForm->create('PointUser', array('url' => 'pay_method')) ?>
		<table class="table text-nowrap">
			<thead>
				<tr>
					<th class="align-middle text-right">金額選択</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($amountList as $amount => $point): ?>
			<tr>
				<td class="align-middle text-right narrow-size"><?php echo number_format($amount); ?>円（<?php echo number_format($point); ?>ポイント）</td>
				<td>
					<?php //echo $this->BcBaser->link( number_format($amount).'円', '/point/point_users/payment/'.$amount, ['class'=>'btn btn-outline-primary btn-e', 'role'=>'button']); ?>
					<?php echo $this->BcForm->input('charge', array('type'=>'radio', 'options'=>$amount, 'class'=>'form-control', 'legend'=>false)); ?>
				</td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		
		<div class="text-center mt-sm-5 mt-4">
			<?php echo $this->BcForm->submit('　次へ　', array('div' => false, 'class' => 'btn btn-info btn-e', 'role' => 'button')) ?>
		</div>
		<?php echo $this->BcForm->end() ?>
	<?php else: //bitcashがない場合 ?>
		<table class="table text-nowrap">
			<thead>
				<tr>
					<th class="align-middle text-right">金額選択</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($amountList as $amount => $point): ?>
			<tr>
				<td class="align-middle text-right narrow-size"><?php echo number_format($amount); ?>円（<?php echo number_format($point); ?>ポイント）</td>
				<td><?php echo $this->BcBaser->link( number_format($amount).'円', '/point/point_users/payment/'.$amount, ['class'=>'btn btn-outline-primary btn-e', 'role'=>'button']) ?></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>