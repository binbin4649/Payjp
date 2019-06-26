<!-- form -->
<?php echo $this->BcForm->create('PointBook', ['url'=>'edit/'.$this->request->data['PointBook']['id']]) ?>
<div class="section">
	<table cellpadding="0" cellspacing="0" class="form-table">
		<tr>
			<th class="col-head">id</th>
			<td class="col-input">
				<?php echo $this->request->data['PointBook']['id']; ?>
				<?php echo $this->BcForm->input('PointBook.id', array('type' => 'hidden')) ?>
			</td>
		</tr>
		<tr>
			<th class="col-head" width="150">reason</th>
			<td class="col-input">
				reason:<?php echo $this->request->data['PointBook']['reason']; ?><br>
				reason_id:<?php echo $this->request->data['PointBook']['reason_id']; ?><br>
			</td>
		</tr>
		<tr>
			<th class="col-head" width="150">point</th>
			<td class="col-input">
				point:<?php echo $this->request->data['PointBook']['point']; ?><br>
				credit:<?php echo $this->request->data['PointBook']['credit']; ?><br>
				point_balance:<?php echo $this->request->data['PointBook']['point_balance']; ?><br>
				credit_balance:<?php echo $this->request->data['PointBook']['credit_balance']; ?><br>
			</td>
		</tr>
		<tr>
			<th class="col-head" width="150">pay</th>
			<td class="col-input">
				pay_token:<?php echo $this->request->data['PointBook']['pay_token']; ?><br>
				charge:<?php echo $this->request->data['PointBook']['charge']; ?><br>
			</td>
		</tr>
		<tr>
			<th class="col-head" width="150">close</th>
			<td class="col-input">
				close_date:<?php echo $this->request->data['PointBook']['close_date']; ?><br>
				deadline_date:<?php echo $this->request->data['PointBook']['deadline_date']; ?><br>
				invoice_amount:<?php echo $this->request->data['PointBook']['invoice_amount']; ?><br>
			</td>
		</tr>
		<tr>
			<th class="col-head"><?php echo $this->BcForm->label('PointBook.invoice_date', '請求日') ?></th>
			<td class="col-input">
				<?php echo $this->BcForm->datepicker('PointBook.invoice_date') ?>
				<?php echo $this->BcForm->error('PointBook.invoice_date') ?>
			</td>
		</tr>
		<tr>
			<th class="col-head"><?php echo $this->BcForm->label('PointBook.payment_date', '入金日') ?></th>
			<td class="col-input">
				<?php echo $this->BcForm->datepicker('PointBook.payment_date') ?>
				<?php echo $this->BcForm->error('PointBook.payment_date') ?>
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
	<li></li>
	<li></li>
	<li></li>
</ul>
</div>