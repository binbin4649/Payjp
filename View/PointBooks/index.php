<?php $this->BcBaser->css(array('Point.point'), array('inline' => false)); ?>
<?php echo $this->Session->flash(); ?>
<h1 class="h5 border-bottom py-2 mb-3 text-secondary"><?php echo $this->pageTitle ?></h1>
<div class="my-3 mx-1">
	
	<?php if($PointBooks): ?>
	<div class="table-responsive">
	<small>
	<table class="table table-sm text-nowrap">
		<thead>
			<tr>
				<th scope="col ">Date</th>
				<th scope="col">Action</th>
				<th scope="col">ポイント</th>
				<th scope="col">予約</th>
				<th scope="col">ポイント残</th>
				<th scope="col">予約済</th>
				<th scope="col">支払</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($PointBooks as $book): ?>
			<tr>
			<td scope="row"><?php echo $book['PointBook']['created']; ?></td>
			<td><?php echo $book['PointBook']['reason']; ?></td>
			<td class="text-right"><?php echo number_format($book['PointBook']['point']); ?></td>
			<td class="text-right"><?php echo number_format($book['PointBook']['credit']); ?></td>
			<td class="text-right"><?php echo number_format($book['PointBook']['point_balance']); ?></td>
			<td class="text-right"><?php echo number_format($book['PointBook']['credit_balance']); ?></td>
			<td class="text-right"><?php echo number_format($book['PointBook']['charge']); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	</small>
	</div>
	<?php else: ?>
		<p>no data.</p>
	<?php endif; ?>
	<?php $this->BcBaser->pagination('simple'); ?>
</div>
<div class="my-3 mx-3">
	<p>
		<a class="btn btn-outline-secondary btn-sm btn-e" data-toggle="collapse" href="#descriptionOfTable" role="button" aria-expanded="false" aria-controls="collapseExample">
		表：各項目の説明
  		</a>
	</p>
	<div class="collapse" id="descriptionOfTable">
		<small>
		<ul>
			<li>Date：ポイント・予約が増減した時の日時</li>
			<li>Action：ポイント・予約が増減した内容</li>
			<li>ポイント：増減したポイント</li>
			<li>予約（消費予定のポイント）：増減した予約ポイント</li>
			<li>ポイント残：その時点でのポイントの残り</li>
			<li>予約済：その時点での消費予定ポイントの合計</li>
			<li>支払：クレジットカードなどで支払った金額</li>
		</ul>
		</small>
	</div>
</div>