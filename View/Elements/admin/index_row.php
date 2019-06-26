<?php
//状態
if ($data['Mypage']['status'] == 0) {
  $status = 'class=""';
} else {
  $status = 'class="disablerow"';
}
?>
<tr <?php echo $status ?>>
	<td class="row-tools">
		<?php $this->BcBaser->link($this->BcBaser->getImg('admin/icn_tool_edit.png', array('width' => 24, 'height' => 24, 'alt' => 'ポイント調整', 'class' => 'btn')), array('action' => 'adjust', $data['PointUser']['id']), array('title' => 'ポイント調整')) ?>
		<?php $this->BcBaser->link($this->BcBaser->getImg('admin/icn_tool_manage.png', ['alt' => 'ユーザ編集', 'class' => 'btn']), array('action' => 'edit', $data['PointUser']['id']), ['title' => 'ユーザ編集']) ?>
	</td>
	<td><?php echo $data['Mypage']['id'] ?></td>
	<td><?php $this->BcBaser->link($data['Mypage']['name'], array('action' => 'edit', $data['PointUser']['id'])) ?></td>
	<td><?php echo $data['PointUser']['point'] ?></td>
	<td><?php echo $data['PointUser']['credit'] ?></td>
	<td><?php echo $this->BcTime->format('Y-m-d', $data['Mypage']['created']) ?><br />
		<?php echo $this->BcTime->format('Y-m-d', $data['Mypage']['modified']) ?></td>
</tr>