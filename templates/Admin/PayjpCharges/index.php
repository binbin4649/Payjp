<?php

/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface[] $payjpCharges
 * @var string $id
 * @var string $keyword
 * @var string $userId
 * @var \Authorization\IdentityInterface $Identity
 * @var \Payjp\Model\Entity\PayjpCharge $payjpCharge
 */
?>

<div class="container-fluid">

    <div class="page-title-box">
        <h4 class="page-title">Payjp Charges一覧</h4>
    </div>

    <?= $this->Form->create(null, ['align' => 'inline', 'class' => 'row-cols-auto ms-1', 'type' => 'get', 'valueSources' => 'query', 'spacing' => 'g-1']) ?>
    <a href="/payjp/admin/payjp-charges" class="btn btn-sm btn-outline-light me-1"><i class="mdi mdi-reload"></i></a>
    <?= $this->Form->control('id', ['type' => 'text', 'value' => $id, 'escape' => true, 'style' => 'width: 60px;', 'placeholder' => 'id']) ?>
    <?= $this->Form->control('user_id', ['type' => 'text', 'value' => $userId, 'escape' => true, 'style' => 'width: 60px;', 'placeholder' => 'user_id']) ?>
    <?= $this->Form->control('q', ['type' => 'text', 'value' => $keyword, 'escape' => true]) ?>
    <?= $this->Form->button('検索', ['class' => 'btn btn-primary']) ?>
    <?= $this->Form->end() ?>

    <section class="mx-1 my-3">
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0 text-nowrap">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>user_id</th>
                        <th>point_book</th>
                        <th>status</th>
                        <th>type</th>
                        <th>payjp_status</th>
                        <th>amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payjpCharges as $payjpCharge) : ?>
                        <tr>
                            <td><?= $this->Number->format($payjpCharge->id) ?></td>
                            <td>
                                <?= $this->Mem->adminLink($payjpCharge->user->name, 'Users', $payjpCharge->user->id) ?>
                            </td>
                            <td>
                                <?= $payjpCharge->hasValue('point_book') ? $this->Html->link($payjpCharge->point_book->id, ['plugin' => 'Point', 'prefix' => 'Admin', 'controller' => 'PointBooks', 'action' => 'view', $payjpCharge->point_book->id]) : '' ?>
                            </td>
                            <td><?= h($payjpCharge->status) ?></td>
                            <td><?= h($payjpCharge->type) ?></td>
                            <td><?= h($payjpCharge->payjp_status) ?></td>
                            <td><?= $this->Number->format($payjpCharge->amount) ?></td>
                            <td class="table-action">
                                <a href="/payjp/admin/payjp-charges/view/<?= $payjpCharge->id ?>" class="action-icon"> <i class="mdi mdi-eye"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?= $this->element('admin/pagination') ?>

</div>

<div class="container-fluid mt-3">
    <ul class="small text-muted">
        <li>検索は左から、Id、ユーザーID、フリーキーワードです。</li>
        <li>フリーキーワードは、payjp_customer_code、payjp_checkout_session_code、payjp_payment_flow_code、card_last4、名前、が対象です。</li>
    </ul>
</div>
