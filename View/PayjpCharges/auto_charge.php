<?php $this->BcBaser->css(array('Point.point'), array('inline' => false)); ?>
<?php echo $this->Session->flash(); ?>

<h1 class="h5 border-bottom py-3 mb-3 text-secondary"><?php echo $this->pageTitle ?></h1>
<div class="my-3 mx-sm-5">
	<p><?php echo $BreakPoint ?>ポイント以下になると自動でクレジットカード決済を行います。</p>
	<small>
	<ul>
		<li>自動で行われる決済金額を選択、カード情報入力、登録ボタンを押してください。</li>
		<li>毎回決済時にメールでお知らせします。</li>
		<li>オートチャージに失敗しポイントが無くなった場合、予約があってもサービスは実行されません。ご注意ください。</li>
	</ul>
	</small>
	<?php echo $this->BcForm->create('PointUser', array('url' => 'auto_charge')) ?>
	<div class="row mb-3 mt-sm-5 mt-4">
		<div class="col-md-4 text-md-right">
			金額選択
		</div>
		<div class="col-md-8">
			<?php echo $this->BcForm->input('charge', array('type'=>'select', 'options'=>$chargeList, 'class'=>'form-control', 'empty'=>'---')) ?>
		</div>
	</div>
	<div class="row mb-3">
		<div class="col-md-4 text-md-right">
			カード情報入力
		</div>
		<div class="col-md-8 text-md-left text-center">
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
		</div>
	</div>
	<?php if($isAutoCharge): ?>
		<div class="row py-3 mx-2 rounded border">
			<div class="col-md-4 text-md-right">
				登録済み
				<?php if($PointUser['PointUser']['auto_charge_status'] == 'success'): ?>
					<span class="badge badge-pill badge-success">有効</span>
				<?php elseif($PointUser['PointUser']['auto_charge_status'] == 'fail'): ?>
					<span class="badge badge-pill badge-danger">失敗</span>
				<?php endif; ?>
			</div>
			<div class="col-md-8">
				<?php echo $chargeList[$PointUser['PointUser']['charge_point']]; ?><br>
				<?php echo $PointUser['PointUser']['payjp_brand'].' **** **** **** '.$PointUser['PointUser']['payjp_last4']; ?>
			</div>
		</div>
	<?php endif; ?>
	<div class="text-center mt-sm-5 mt-4">
		<?php echo $this->BcBaser->link( '解除', '/point/point_users/cancell_auto_charge', ['class'=>'btn btn-outline-secondary mr-5 btn-e', 'role'=>'button']);?>
		<?php if($isAutoCharge): ?>
			<?php echo $this->BcForm->submit('　登録変更　', array('div' => false, 'class' => 'btn btn-info btn-e', 'role' => 'button')) ?>
		<?php else: ?>
			<?php echo $this->BcForm->submit('　登録　', array('div' => false, 'class' => 'btn btn-info btn-e', 'role' => 'button')) ?>
		<?php endif; ?>
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
			また決済はトークン決済を用いることにより、当サイトは完全なカード情報を一切保持いたしません。<br>
			どうぞ安心してご利用くださいませ。
			</small>
		</div>
		
	</div>
</div>