<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $payjpCharge
 */
?>

<div class="container-fluid">

    <div class="page-title-box">
        <h4 class="page-title">Payjp Charge詳細</h4>
    </div>
    <section class="row mx-1 my-1">
        <div class="col-6">
            <?= $this->Html->link('一覧に戻る', '/admin/payjpcharges', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <div class="col-6">
            <?php if ($Identity->can('delete', $payjpCharge)) : ?>
                <?= $this->Form->postLink(
                    '削除',
                    ['prefix' => 'Admin', 'controller' => 'PayjpCharges', 'action' => 'delete', $payjpCharge->id],
                    [
                        'confirm' => '通常はステータスを「無効」で運用して下さい。それでも削除しますか？',
                        'class' => 'btn btn-sm btn-secondary me-3',
                        'method' => 'delete'
                    ]
                ) ?>
            <?php endif; ?>
            <?php if ($Identity->can('edit', $payjpCharge)) : ?>
                <?= $this->Html->link('編集', ['action' => 'edit', $payjpCharge->id], ['class' => 'btn btn-primary me-3']) ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="p-2">
        <div class="row mx-1 my-1">
            <div class="col-md-7">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item small text-muted">
                        <span class="me-2">id:<?= $payjpCharge->id ?></span>
                        <span class="me-2">modified:<?= $payjpCharge->modified ?></span>
                        <span class="me-2">created:<?= $payjpCharge->created ?></span>
                    </li>
                    <li class="list-group-item"><span class="text-muted me-2">user_id:</span><?= h($payjpCharge->user_id) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">point_book_id:</span><?= h($payjpCharge->point_book_id) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">ステータス:</span><?= $this->Mem->statusBadge($payjpCharge->status) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">type:</span><?= h($payjpCharge->type) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">payjp_status:</span><?= h($payjpCharge->payjp_status) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">payjp_customer_code:</span><?= h($payjpCharge->payjp_customer_code) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">payjp_charge_code:</span><?= h($payjpCharge->payjp_charge_code) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">amount:</span><?= h($payjpCharge->amount) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">payjp_card_token:</span><?= h($payjpCharge->payjp_card_token) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">card_brand:</span><?= h($payjpCharge->card_brand) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">card_last4:</span><?= h($payjpCharge->card_last4) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">idempotency_key:</span><?= h($payjpCharge->idempotency_key) ?></li>
                                <li class="list-group-item"><span class="text-muted me-2">log:</span><?= nl2br(h($payjpCharge->log ?? '')) ?></li>
                            </ul>
            </div>
            <div class="col-md-5"></div>
        </div>
    </section>

    <?= $this->element('admin/change_log') ?>

</div>