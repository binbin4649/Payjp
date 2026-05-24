<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $payjpUser
 */
?>

<div class="container-fluid">

    <div class="page-title-box">
        <h4 class="page-title">Payjp User詳細</h4>
    </div>
    <section class="row mx-1 my-1">
        <div class="col-6">
            <?= $this->Html->link('一覧に戻る', '/admin/payjpusers', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <div class="col-6">
            <?php if ($Identity->can('delete', $payjpUser)) : ?>
                <?= $this->Form->postLink(
                    '削除',
                    ['prefix' => 'Admin', 'controller' => 'PayjpUsers', 'action' => 'delete', $payjpUser->id],
                    [
                        'confirm' => '通常はステータスを「無効」で運用して下さい。それでも削除しますか？',
                        'class' => 'btn btn-sm btn-secondary me-3',
                        'method' => 'delete'
                    ]
                ) ?>
            <?php endif; ?>
            <?php if ($Identity->can('edit', $payjpUser)) : ?>
                <?= $this->Html->link('編集', ['action' => 'edit', $payjpUser->id], ['class' => 'btn btn-primary me-3']) ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="p-2">
        <div class="row mx-1 my-1">
            <div class="col-md-7">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item small text-muted">
                        <span class="me-2">id:<?= $payjpUser->id ?></span>
                        <span class="me-2">modified:<?= $payjpUser->modified ?></span>
                        <span class="me-2">created:<?= $payjpUser->created ?></span>
                    </li>
                    <li class="list-group-item"><span class="text-muted me-2">user_id:</span><?= h($payjpUser->user_id) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">ステータス:</span><?= $this->Mem->statusBadge($payjpUser->status) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">type:</span><?= h($payjpUser->type) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">auto_charge_amount:</span><?= h($payjpUser->auto_charge_amount) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">payjp_card_token:</span><?= h($payjpUser->payjp_card_token) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">payjp_customer_code:</span><?= h($payjpUser->payjp_customer_code) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">card_brand:</span><?= h($payjpUser->card_brand) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">card_last4:</span><?= h($payjpUser->card_last4) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">last_synced:</span><?= h($payjpUser->last_synced) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">log:</span><?= nl2br(h($payjpUser->log ?? '')) ?></li>
                            </ul>
            </div>
            <div class="col-md-5"></div>
        </div>
    </section>

    <?= $this->element('admin/change_log') ?>

</div>