<?php $this->BcBaser->css(array('Point.point'), array('inline' => false)); ?>
<?php echo $this->Session->flash(); ?>

<h1 class="h5 border-bottom py-3 mb-3 text-secondary"><?php echo $this->pageTitle ?></h1>
<div class="my-3 mx-sm-5">
	<p>金額を確認し、よろしければカード情報入力、決済ボタンを押してください。</p>
	<small>
	<ul>
		<li>支払いはクレジットカード、1回払いのみとなります。</li>
	</ul>
	</small>
	<?php echo $this->BcForm->create('PayjpCharge', array('url' => 'payment/'.$amount)) ?>
	<?php echo $this->BcForm->input('amount', array('type'=>'hidden', 'value'=>$amount)) ?>
	<table class="table text-nowrap">
		<thead>
		<tr>
			<th class="align-middle text-right">決済金額</th>
			<th></th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td class="align-middle text-right"><?php echo number_format($amount); ?>円</td>
			<td>
<script
  type="text/javascript"
  src="https://checkout.pay.jp/"
  class="payjp-button"
  data-key="<?php echo $payjp_public ?>"
  data-on-created="onCreated"
  data-text="カード情報入力"
  data-submit-text="入力"
  data-partial="true">
</script>
			</td>
		</tr>
		</tbody>
	</table>
	<div class="text-center">
		<?php //echo $this->BcBaser->link( '戻る', '/point/point_users/payselect', ['class'=>'btn btn-outline-secondary mr-5 btn-e', 'role'=>'button']);?>
		<?php echo $this->BcForm->submit('　決済　', array('div' => false, 'class' => 'btn btn-info btn-e', 'role' => 'button')) ?>
	</div>
	<?php echo $this->BcForm->end() ?>
	<div class="row mt-5 mx-1 p-2 bg-light rounded border">
		<div class="col-2 text-center">
			<i class="fab fa-expeditedssl fa-3x logo-gold"></i>
			<div class="text-muted d-none d-sm-block"><small>RSA:SHA-256</small></div>
		</div>
		<div class="col-10">
			<small>
			決済ページはSSL暗号通信により、通信の秘密が守られております。<br>
			また決済はトークン決済を用いることにより、当サイトはカード情報を一切保持いたしません。<br>
			どうぞ安心してご利用くださいませ。
			</small>
		</div>
		
	</div>
</div>