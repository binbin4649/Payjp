<!-- form -->
<?php echo $this->BcForm->create('PointUser', ['url'=>'adjust/'.$this->request->data['PointUser']['id']]) ?>
<div class="section">
	<table cellpadding="0" cellspacing="0" class="form-table">
		<tr>
			<th class="col-head"><?php echo $this->BcForm->label('Mypage.id', '会員番号') ?></th>
			<td class="col-input">
				<?php echo $this->request->data['Mypage']['id']; ?>
				<?php echo $this->BcForm->input('PointUser.mypage_id', array('type' => 'hidden')) ?>
				<?php echo $this->BcForm->input('PointUser.point_user_id', array('type' => 'hidden')) ?>
			</td>
		</tr>
		<tr>
			<th class="col-head" width="150"><?php echo $this->BcForm->label('Mypage.name', '名前') ?></th>
			<td class="col-input">
				<?php echo $this->request->data['Mypage']['name']; ?>
			</td>
		</tr>
		<tr>
			<th class="col-head" width="150"><?php echo $this->BcForm->label('PointUser.point', '現ポイント') ?></th>
			<td class="col-input">
				<?php echo $this->request->data['PointUser']['point']; ?>
			</td>
		</tr>
		<tr>
			<th class="col-head" width="150"><?php echo $this->BcForm->label('PointUser.credit', '現クレジット') ?></th>
			<td class="col-input">
				<?php echo $this->request->data['PointUser']['credit']; ?>
			</td>
		</tr>
		<tr>
			<th class="col-head" width="150"><?php echo $this->BcForm->label('PointUser.point', '増減ポイント') ?></th>
			<td class="col-input">
			<?php echo $this->BcForm->input('PointUser.point', array('type'=>'text', 'value' => 0)) ?>
			<?php echo $this->BcForm->error('PointUser.point') ?></td>
		</tr>
		<tr>
			<th class="col-head" width="150"><?php echo $this->BcForm->label('PointUser.reason', '理由') ?></th>
			<td class="col-input">
			<?php echo $this->BcForm->input('PointUser.reason', array('type'=>'text', 'value'=>'other')) ?>
			<?php echo $this->BcForm->error('PointUser.reason') ?></td>
		</tr>
	</table>
</div>
<!-- button -->
<div class="submit">
<?php echo $this->BcForm->submit('調整', array('div' => false, 'class' => 'button')) ?>
</div>
<?php echo $this->BcForm->end() ?>

<div class="section">
<ul>
	<li>減する時は半角ハイフンを付ける。</li>
	<li></li>
	<li></li>
</ul>
</div>
<div class="section">
	<table cellpadding="0" cellspacing="0" class="form-table">
		<tr>
			<th>created</th>
			<th>point</th>
			<th>credit</th>
			<th>point_balance</th>
			<th>credit_balance</th>
			<th>charge</th>
			<th>reason</th>
			<th>reason_id</th>
		</tr>
		<?php foreach($this->request->data['PointBook'] as $book): ?>
		<tr>
			<td><?php echo $book['created']; ?></td>
			<td><?php echo $book['point']; ?></td>
			<td><?php echo $book['credit']; ?></td>
			<td><?php echo $book['point_balance']; ?></td>
			<td><?php echo $book['credit_balance']; ?></td>
			<td><?php echo $book['charge']; ?></td>
			<td><?php echo $book['reason']; ?></td>
			<td><?php echo $book['reason_id']; ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>

