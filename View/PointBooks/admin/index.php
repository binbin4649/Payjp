<?php echo $this->BcForm->create('PointBook') ?>
会員番号:<?php echo $this->BcForm->input('PointBook.mypage_id', array('type'=>'text', 'size'=>5)) ?>　
reason:<?php echo $this->BcForm->input('PointBook.reason', array('type'=>'text', 'size'=>10)) ?>　
<?php echo $this->BcForm->submit('　検索　', array('div' => false, 'class' => 'button', 'style'=>'padding:4px;')) ?>
<?php echo $this->BcForm->end() ?>

<div id="DataList">
<?php $this->BcBaser->element('pagination') ?>
<table cellpadding="0" cellspacing="0" class="list-table" id="ListTable">
<thead>
	<tr>
		<th class="list-tool">調整・編集</th>
		<th><?php echo $this->Paginator->sort('PointBook.mypage_id', array('asc' => $this->BcBaser->getImg('admin/blt_list_down.png', array('alt' => '昇順', 'title' => '昇順')) . ' 会員番号', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', array('alt' => '降順', 'title' => '降順')) . ' 会員番号'), array('escape' => false, 'class' => 'btn-direction')) ?></th>
		<th><?php echo $this->Paginator->sort('PointBook.point', array('asc' => $this->BcBaser->getImg('admin/blt_list_down.png', array('alt' => '昇順', 'title' => '昇順')) . ' Point', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', array('alt' => '降順', 'title' => '降順')) . ' Point'), array('escape' => false, 'class' => 'btn-direction')) ?></th>
		<th><?php echo $this->Paginator->sort('PointBook.credit', array('asc' => $this->BcBaser->getImg('admin/blt_list_down.png', array('alt' => '昇順', 'title' => '昇順')) . ' Credit', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', array('alt' => '降順', 'title' => '降順')) . ' Credit'), array('escape' => false, 'class' => 'btn-direction')) ?></th>
		<th><?php echo $this->Paginator->sort('PointBook.point_balance', array('asc' => $this->BcBaser->getImg('admin/blt_list_down.png', array('alt' => '昇順', 'title' => '昇順')) . ' point_balance', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', array('alt' => '降順', 'title' => '降順')) . ' point_balance'), array('escape' => false, 'class' => 'btn-direction')) ?></th>
		<th><?php echo $this->Paginator->sort('PointBook.credit_balance', array('asc' => $this->BcBaser->getImg('admin/blt_list_down.png', array('alt' => '昇順', 'title' => '昇順')) . ' credit_balance', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', array('alt' => '降順', 'title' => '降順')) . ' credit_balance'), array('escape' => false, 'class' => 'btn-direction')) ?></th>
		<th><?php echo $this->Paginator->sort('PointBook.reason', array('asc' => $this->BcBaser->getImg('admin/blt_list_down.png', array('alt' => '昇順', 'title' => '昇順')) . ' Reason', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', array('alt' => '降順', 'title' => '降順')) . ' Reason'), array('escape' => false, 'class' => 'btn-direction')) ?></th>
		<th><?php echo $this->Paginator->sort('PointBook.close_date', array('asc' => $this->BcBaser->getImg('admin/blt_list_down.png', array('alt' => '昇順', 'title' => '昇順')) . ' close_date', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', array('alt' => '降順', 'title' => '降順')) . ' close_date'), array('escape' => false, 'class' => 'btn-direction')) ?></th>
		<th><?php echo $this->Paginator->sort('PointBook.created', array('asc' => $this->BcBaser->getImg('admin/blt_list_down.png', array('alt' => '昇順', 'title' => '昇順')) . ' 登録日', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', array('alt' => '降順', 'title' => '降順')) . ' 登録日'), array('escape' => false, 'class' => 'btn-direction')) ?><br />
			<?php echo $this->Paginator->sort('PointBook.modified', array('asc' => $this->BcBaser->getImg('admin/blt_list_down.png', array('alt' => '昇順', 'title' => '昇順')) . ' 更新日', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', array('alt' => '降順', 'title' => '降順')) . ' 更新日'), array('escape' => false, 'class' => 'btn-direction')) ?></th>
	</tr>
</thead>
<tbody>
	<?php if (!empty($PointBooks)): ?>
		<?php foreach ($PointBooks as $data): ?>
			<tr>
				<td class="row-tools">
					<?php $this->BcBaser->link($this->BcBaser->getImg('admin/icn_tool_edit.png', array('width' => 24, 'height' => 24, 'alt' => '編集', 'class' => 'btn')), array('action' => 'edit', $data['PointBook']['id']), array('title' => '編集')) ?>
				</td>
				<td><?php echo $data['PointBook']['mypage_id'] ?></td>
				<td><?php echo $data['PointBook']['point'] ?></td>
				<td><?php echo $data['PointBook']['credit'] ?></td>
				<td><?php echo $data['PointBook']['point_balance'] ?></td>
				<td><?php echo $data['PointBook']['credit_balance'] ?></td>
				<td><?php echo $data['PointBook']['reason'] ?></td>
				<td><?php echo $data['PointBook']['close_date'] ?></td>
				<td><?php echo $this->BcTime->format('Y-m-d', $data['PointBook']['created']) ?><br />
					<?php echo $this->BcTime->format('Y-m-d', $data['PointBook']['modified']) ?></td>
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