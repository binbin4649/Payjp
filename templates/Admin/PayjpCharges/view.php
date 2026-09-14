<?php

/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $payjpCharge
 * @var \Authorization\IdentityInterface $Identity
 */
?>

<div class="container-fluid">

    <div class="page-title-box">
        <h4 class="page-title">Payjp Charge詳細</h4>
    </div>
    <section class="row mx-1 my-1">
        <div class="col-6">
            <?= $this->Html->link('一覧に戻る', '/payjp/admin/payjp-charges', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <div class="col-6">
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
                    <li class="list-group-item">
                        <span class="text-muted me-2">user_id:</span>
                        <?= $this->Mem->adminLink($payjpCharge->user->name, 'Users', $payjpCharge->user->id) ?>
                    </li>
                    <li class="list-group-item">
                        <span class="text-muted me-2">point_book_id:</span>
                        <?= $payjpCharge->hasValue('point_book') ? $this->Html->link($payjpCharge->point_book->id, ['plugin' => 'Point', 'prefix' => 'Admin', 'controller' => 'PointBooks', 'action' => 'view', $payjpCharge->point_book->id]) : '' ?>
                    </li>
                    <li class="list-group-item"><span class="text-muted me-2">ステータス:</span><?= $this->Mem->statusBadge($payjpCharge->status) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">type:</span><?= h($payjpCharge->type) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">payjp_status:</span><?= h($payjpCharge->payjp_status) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">payjp_customer_code:</span><?= h($payjpCharge->payjp_customer_code) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">ayjp_checkout_session_code:</span><?= h($payjpCharge->ayjp_checkout_session_code) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">payjp_payment_flow_code:</span><?= h($payjpCharge->payjp_payment_flow_code) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">payjp_payment_method_code:</span><?= h($payjpCharge->payjp_payment_method_code) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">amount:</span><?= h($payjpCharge->amount) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">card_brand:</span><?= h($payjpCharge->card_brand) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">card_last4:</span><?= h($payjpCharge->card_last4) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">idempotency_key:</span><?= h($payjpCharge->idempotency_key) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">log:</span><?= $payjpCharge->log ? nl2br(h($payjpCharge->log)) : '' ?></li>
                </ul>
            </div>
            <div class="col-md-5"></div>
        </div>
    </section>

    <?= $this->element('admin/change_log') ?>

</div>
