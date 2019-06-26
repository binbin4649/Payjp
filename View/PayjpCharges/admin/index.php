<?php echo $this->BcForm->create('PointUser') ?>
会員番号:<?php echo $this->BcForm->input('PointUser.mypage_id', array('type'=>'text', 'size'=>5)) ?>　
名前:<?php echo $this->BcForm->input('Mypage.name', array('type'=>'text', 'size'=>10)) ?>　
<?php echo $this->BcForm->submit('　検索　', array('div' => false, 'class' => 'button', 'style'=>'padding:4px;')) ?>
<?php echo $this->BcForm->end() ?>

<div id="DataList">
<?php $this->BcBaser->element('pagination') ?>
<table cellpadding="0" cellspacing="0" class="list-table" id="ListTable">
<thead>
	<tr>
		<th class="list-tool">調整・編集</th>
		<th><?php echo $this->Paginator->sort('Mypage.id', array('asc' => $this->BcBaser->getImg('admin/blt_list_down.png', array('alt' => '昇順', 'title' => '昇順')) . ' 会員番号', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', array('alt' => '降順', 'title' => '降順')) . ' 会員番号'), array('escape' => false, 'class' => 'btn-direction')) ?></th>
		<th><?php echo $this->Paginator->sort('Mypage.name', array('asc' => $this->BcBaser->getImg('admin/blt_list_down.png', array('alt' => '昇順', 'title' => '昇順')) . ' 名前', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', array('alt' => '降順', 'title' => '降順')) . ' 名前'), array('escape' => false, 'class' => 'btn-direction')) ?></th>
		<th><?php echo $this->Paginator->sort('PointUser.point', array('asc' => $this->BcBaser->getImg('admin/blt_list_down.png', array('alt' => '昇順', 'title' => '昇順')) . ' Point', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', array('alt' => '降順', 'title' => '降順')) . ' Mail'), array('escape' => false, 'class' => 'btn-direction')) ?></th>
		<th><?php echo $this->Paginator->sort('PointUser.credit', array('asc' => $this->BcBaser->getImg('admin/blt_list_down.png', array('alt' => '昇順', 'title' => '昇順')) . ' Credit', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', array('alt' => '降順', 'title' => '降順')) . ' Mail'), array('escape' => false, 'class' => 'btn-direction')) ?></th>
		<th><?php echo $this->Paginator->sort('Mypage.created', array('asc' => $this->BcBaser->getImg('admin/blt_list_down.png', array('alt' => '昇順', 'title' => '昇順')) . ' 登録日', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', array('alt' => '降順', 'title' => '降順')) . ' 登録日'), array('escape' => false, 'class' => 'btn-direction')) ?><br />
			<?php echo $this->Paginator->sort('Mypage.modified', array('asc' => $this->BcBaser->getImg('admin/blt_list_down.png', array('alt' => '昇順', 'title' => '昇順')) . ' 更新日', 'desc' => $this->BcBaser->getImg('admin/blt_list_up.png', array('alt' => '降順', 'title' => '降順')) . ' 更新日'), array('escape' => false, 'class' => 'btn-direction')) ?></th>
	</tr>
</thead>
<tbody>
	<?php if (!empty($pointUser)): ?>
		<?php foreach ($pointUser as $data): ?>
			<?php $this->BcBaser->element('admin/index_row', array('data' => $data)) ?>
		<?php endforeach; ?>
	<?php else: ?>
		<tr>
			<td colspan="8"><p class="no-data">データが見つかりませんでした。</p></td>
		</tr>
	<?php endif; ?>
</tbody>
</table>
</div>