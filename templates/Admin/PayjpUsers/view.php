<?php

/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $payjpUser
 * @var \Authorization\IdentityInterface $Identity
 * @var array $statuses
 */
?>

<div class="container-fluid">

    <div class="page-title-box">
        <h4 class="page-title">Payjp User詳細</h4>
    </div>
    <section class="row mx-1 my-1">
        <div class="col-6">
            <?= $this->Html->link('一覧に戻る', '/payjp/admin/payjp-users', ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <div class="col-6">
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
                    <li class="list-group-item">
                        <span class="text-muted me-2">user_id:</span>
                        <?= $this->Mem->adminLink($payjpUser->user->name, 'Users', $payjpUser->user->id) ?>
                    </li>
                    <li class="list-group-item"><span class="text-muted me-2">ステータン:</span><?= $statuses[$payjpUser->status] ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">type:</span><?= h($payjpUser->type) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">auto_charge_amount:</span><?= h($payjpUser->auto_charge_amount) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">payjp_customer_code:</span><?= h($payjpUser->payjp_customer_code) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">payjp_payment_method_code:</span><?= h($payjpUser->payjp_payment_method_code) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">card_brand:</span><?= h($payjpUser->card_brand) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">card_last4:</span><?= h($payjpUser->card_last4) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">last_synced:</span><?= h($payjpUser->last_synced) ?></li>
                    <li class="list-group-item"><span class="text-muted me-2">log:</span><?= $payjpUser->log ? nl2br(h($payjpUser->log)) : '' ?></li>
                </ul>
            </div>
            <div class="col-md-5"></div>
        </div>
    </section>

    <?= $this->element('admin/change_log') ?>

</div>