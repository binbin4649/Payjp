<?php $this->BcBaser->css(array('Point.point'), array('inline' => false)); ?>
<?php echo $this->Session->flash(); ?>

<h1 class="h5 border-bottom py-3 mb-3 text-secondary"><?php echo $this->pageTitle ?></h1>
<div class="my-3 mx-sm-5">
	<p>下記のとおり決済が完了しました。ありがとうございます。</p>
<!-- 	<p>また決済控えとしてメールも送信しました。ご確認ください。</p> -->
	<table class="table text-nowrap md-4">
		<thead>
		<tr>
			<th>決済日時</th>
			<th>決済番号</th>
			<th>決済金額</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td><?php echo $charge['PayjpCharge']['created']; ?></td>
			<td class="text-right"><?php echo $charge['PayjpCharge']['id']; ?></td>
			<td class="text-right"><?php echo number_format($charge['PayjpCharge']['charge']); ?></td>
		</tr>
		</tbody>
	</table>
	
	<ul>
		<li><?php echo $this->BcBaser->link( '決済履歴', '/payjp/payjp_charges/charge_list');?></li>
	</ul>
	<small>決済履歴にてこれまでの履歴を確認できます。</small>
</div>