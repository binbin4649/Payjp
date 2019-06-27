
<?php echo $this->BcForm->create('PayjpCustomer') ?>
会員id:<?php echo $this->BcForm->input('PayjpCustomer.mypage_id', array('type'=>'text', 'size'=>2)) ?>　
名前:<?php echo $this->BcForm->input('Mypage.name', array('type'=>'text', 'size'=>7)) ?>　　
<?php echo $this->BcForm->submit('　検索　', array('div' => false, 'class' => 'button', 'style'=>'padding:4px;')) ?>
<?php echo $this->BcForm->end() ?>

<div id="DataList">
<?php $this->BcBaser->element('pagination') ?>
<table cellpadding="0" cellspacing="0" class="list-table" id="ListTable">
<thead>
	<tr>
		<th>会員</th>
		<th>status</th>
		<th>card</th>
		<th>modified</th>
	</tr>
</thead>
<tbody>
	<?php if (!empty($PayjpCustomer)): ?>
		<?php foreach ($PayjpCustomer as $data): ?>
			<tr>
				<td><?php echo $data['PayjpCustomer']['mypage_id'] ?>:<?php echo $data['Mypage']['name'] ?></td>
				<td><?php echo $data['PayjpCustomer']['status'] ?></td>
				<td><?php echo $data['PayjpCustomer']['brand'] ?>:<?php echo $data['PayjpCustomer']['last4'] ?></td>
				<td><?php echo $data['PayjpCustomer']['modified'] ?></td>
			</tr>
		<?php endforeach; ?>
	<?php else: ?>
		<tr>
			<td colspan="8"><p class="no-data">データが見つかりませんでした。</p></td>
		</tr>
	<?php endif; ?>
</tbody>
</table>
</div>
<div class="section">
<p>ページングと検索は同時に使えません。（仕様です）<br>
	名前は部分一致。<br>
</p>
</div>