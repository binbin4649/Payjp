<?php
/**
 * @var \App\View\AppView $this
 * @var bool $completed
 */
?>
<div class="payjp-complete">
    <?php if (!empty($completed)) : ?>
        <p>お支払い手続きが完了しました。</p>
    <?php else : ?>
        <p>お支払いの確認中です。確定までしばらくお待ちください。</p>
    <?php endif; ?>
</div>
