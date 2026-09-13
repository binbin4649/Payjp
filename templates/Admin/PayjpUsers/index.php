<?php

/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface[] $payjpUsers
 * @var string $id
 * @var string $keyword
 * @var \Member\Model\Entity\User $Identity
 * @var \Payjp\Model\Entity\PayjpUser $payjpUser
 * @var array $statuses
 * @var string $status
 * @var string $user_id
 */
?>
<div class="container-fluid">

    <div class="page-title-box">
        <h4 class="page-title">Payjp Users一覧</h4>
    </div>

    <?= $this->Form->create(null, ['align' => 'inline', 'class' => 'row-cols-auto ms-1', 'type' => 'get', 'valueSources' => 'query', 'spacing' => 'g-1']) ?>
    <a href="/payjp/admin/payjp-users" class="btn btn-sm btn-outline-light me-1"><i class="mdi mdi-reload"></i></a>
    <?= $this->Form->control('id', ['type' => 'text', 'value' => $id, 'escape' => true, 'style' => 'width: 60px;', 'placeholder' => 'id']) ?>
    <?= $this->Form->control('user_id', ['type' => 'text', 'value' => $user_id, 'escape' => true, 'style' => 'width: 60px;', 'placeholder' => 'user_id']) ?>
    <?= $this->Form->control('status', ['type' => 'select', 'options' => $statuses, 'value' => $status, 'empty' => true, 'id' => 'status_select']) ?>
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
                        <th>status</th>
                        <th>type</th>
                        <th>amount</th>
                        <th>created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payjpUsers as $payjpUser) : ?>
                        <tr>
                            <td>
                                <?= $payjpUser->id ?>
                                <?php if ($payjpUser->log) : ?>
                                    <i class="mdi mdi-alert-circle text-danger"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $payjpUser->hasValue('user') ? $this->Mem->adminLink($payjpUser->user->name, 'Users', $payjpUser->user->id) : '' ?>
                            </td>
                            <td><?= $statuses[$payjpUser->status] ?></td>
                            <td><?= h($payjpUser->type) ?></td>
                            <td><?= $payjpUser->auto_charge_amount === null ? '' : $this->Number->format($payjpUser->auto_charge_amount) ?></td>
                            <td><?= $payjpUser->created ?></td>
                            <td class="table-action">
                                <a href="/payjp/admin/payjpUsers/view/<?= $payjpUser->id ?>" class="action-icon"> <i class="mdi mdi-eye"></i></a>
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
        <li>検索は左から、id、user_id、status、フリーキーワードです。</li>
        <li>フリーキーワードは、payjp_customer_code、payjp_payment_method_code、card_brand、card_last4、ユーザー名が対象です。</li>
    </ul>
</div>