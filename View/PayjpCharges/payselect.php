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
		<h3 class="h6 border-bottom mx-sm-5 px-sm-5 px-3 mx-3 py-2">金額選択</h3>
		<div class="row">
			<div class="col-md-3 mb-3"></div>
			<div class="col-md-6 mb-3">
			<?php
				$hidden = true;  
				foreach($amountList as $amount => $point){
					$show_data = '　'.number_format($amount).'円（'.number_format($point).'ポイント）';
					echo '<div class="form-check mx-3">' . $this->BcForm->radio('charge', [$amount => $show_data], ['hiddenField' => $hidden]) . '</div>';
					$hidden = false;
				}
			?>
			</div>
			<div class="col-md-3 mb-3"></div>
		</div>
		
		<h3 class="h6 border-bottom mx-sm-5 px-sm-5 px-3 mx-3 py-2">お支払い方法</h3>
		<div class="row">
			<div class="col-md-3 mb-3"></div>
			<div class="col-md-6 mb-3">
				<?php echo '<div class="form-check mx-3">' . $this->BcForm->radio('method', ['credit' => ' 　クレジットカード'], ['hiddenField' => true]) . '</div>'; ?>
				<?php echo '<div class="form-check mx-3">' . $this->BcForm->radio('method', ['bitcash' => '　ビットキャッシュ'], ['hiddenField' => false]); ?>
				<img src="https://bitcash.jp/static/logo/bc_w73px.gif" width="73" height="20" alt=" ビットキャッシュでお支払いいただけます" />
				</div>
			</div>
			<div class="col-md-3 mb-3"></div>
		</div>
		
		
		<div class="text-center my-sm-5 my-4">
			<?php echo $this->BcForm->submit('　次へ　', array('div' => false, 'class' => 'btn btn-info btn-e', 'role' => 'button')) ?>
		</div>
		
		<div class="mt-mb-5 mt-3 mx-mb-5 mx-3">
			<small>
			<ul>
				<li>
					「ビットキャッシュ」を選択すると、ビットキャッシュ決済画面に切り替わり、決済後当サイトへ戻り、ポイントが付与されます。
				</li>
				<li>
					<a href="https://bitcash.jp/" target="_blank"><img src="https://bitcash.jp/static/logo/bc_w73px.gif" width="73" height="20" alt=" ビットキャッシュでお支払いいただけます" /></a>
					ビットキャッシュは、ビットキャッシュ株式会社のサービスです。
				</li>
				<li>
					ビットキャッシュについて詳しくは、<a href="/feature/bitcash_payment_operation">ビットキャッシュでのお支払い方法</a> をご参照ください。
				</li>
			</ul>
			</small>
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