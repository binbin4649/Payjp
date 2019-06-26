<?php $this->BcBaser->css(array('Point.point'), array('inline' => false)); ?>
<?php echo $this->Session->flash(); ?>

<h1 class="h5 border-bottom py-3 mb-3 text-secondary"><?php echo $this->pageTitle ?></h1>
<div class="my-3 mx-sm-5">
	<p>以下の注意事項を読み、よろしければチェックを入れて解除実行ボタンを押してください。</p>
	<small>
	<ul>
		<li>オートチャージを解除すると、予約が一旦全て削除されます。余っているポイントは解除後、再予約をお願いします。</li>
	</ul>
	</small>
	<?php echo $this->BcForm->create('PointUser', array('url' => 'cancell_auto_charge')) ?>

	<div class="row mb-3">
		<div class="col-3">
		</div>
		<div class="col-6 form-check form-check-inline">
			<?php echo $this->BcForm->input('cancell', array('type'=>'checkbox', 'class' => 'form-check-input mr-3')) ?>
			<label class="form-check-label">解除します。</label>
		</div>
		<div class="col-3"></div>
	</div>
	
	<div class="text-center mt-sm-5 mt-4">
		<?php echo $this->BcForm->submit('　解除実行　', array('div' => false, 'class' => 'btn btn-secondary btn-e', 'role' => 'button')) ?>
	</div>
	<?php echo $this->BcForm->end() ?>
	
</div>