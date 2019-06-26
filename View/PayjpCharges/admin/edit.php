<!-- form -->
<?php echo $this->BcForm->create('PointUser', ['url'=>'edit/'.$this->request->data['PointUser']['id']]) ?>
<div class="section">
	<table cellpadding="0" cellspacing="0" class="form-table">
		<tr>
			<th class="col-head"><?php echo $this->BcForm->label('Mypage.id', '会員番号') ?></th>
			<td class="col-input">
				<?php echo $this->request->data['Mypage']['id']; ?>
				<?php echo $this->BcForm->input('PointUser.mypage_id', array('type' => 'hidden')) ?>
				<?php echo $this->BcForm->input('PointUser.id', array('type' => 'hidden')) ?>
			</td>
		</tr>
		<tr>
			<th class="col-head" width="150">名前</th>
			<td class="col-input">
				<?php echo $this->request->data['Mypage']['name']; ?>
			</td>
		</tr>
		<tr>
			<th class="col-head" width="150">現ポイント</th>
			<td class="col-input">
				point:<?php echo $this->request->data['PointUser']['point']; ?><br>
				credit:<?php echo $this->request->data['PointUser']['credit']; ?>
			</td>
		</tr>
		<tr>
			<th class="col-head" width="150">PAY.jp</th>
			<td class="col-input">
				auto_charge_status:<?php echo $this->request->data['PointUser']['auto_charge_status']; ?><br>
				charge_point:<?php echo $this->request->data['PointUser']['charge_point']; ?><br>
				card_token:<?php echo $this->request->data['PointUser']['payjp_card_token']; ?><br>
				customer_token:<?php echo $this->request->data['PointUser']['payjp_customer_token']; ?><br>
				brand:<?php echo $this->request->data['PointUser']['payjp_brand']; ?><br>
				last4:<?php echo $this->request->data['PointUser']['payjp_last4']; ?>
			</td>
		</tr>
		<tr>
			<th class="col-head" width="150"><?php echo $this->BcForm->label('PointUser.pay_plan', '支払プラン') ?></th>
			<td class="col-input">
				<?php echo $this->BcForm->input('PointUser.pay_plan', array('type'=>'select', 'options'=>$PayPlan, 'empty'=>'---')) ?>
				<?php echo $this->BcForm->error('PointUser.pay_plan') ?></td>
			</td>
		</tr>
		<tr>
			<th class="col-head" width="150"><?php echo $this->BcForm->label('PointUser.pay_plan', '請求プラン') ?></th>
			<td class="col-input">
				<?php echo $this->BcForm->input('PointUser.invoice_plan', array('type'=>'select', 'options'=>$InvoicePlan, 'empty'=>'---')) ?>
				<?php echo $this->BcForm->error('PointUser.invoice_plan') ?></td>
			</td>
		</tr>
	</table>
</div>
<!-- button -->
<div class="submit">
<?php echo $this->BcForm->submit('編集', array('div' => false, 'class' => 'button')) ?>
</div>
<?php echo $this->BcForm->end() ?>

<div class="section">
<ul>
	<li>[2018-05-25] [basic -> pay_off] 既存のcall予約(before)をすべて削除する。</li>
	<li>[basic <-> auto]へ任意に変えてどうなるかはテストしてないし、やるべきでもない。本来はカード登録が必要。</li>
	<li></li>
	<li></li>
</ul>
</div>