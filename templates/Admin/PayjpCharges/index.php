<div class="container-fluid">

    <div class="page-title-box">
        <h4 class="page-title">Payjp Charges一覧</h4>
    </div>

    <?= $this->Form->create(null, ['align' => 'inline', 'class' => 'row-cols-auto ms-1', 'type' => 'get', 'valueSources' => 'query', 'spacing' => 'g-1']) ?>
    <a href="/admin/payjpCharges" class="btn btn-sm btn-outline-light me-1"><i class="mdi mdi-reload"></i></a>
    <?= $this->Form->control('id', ['type' => 'text', 'value' => $id, 'escape' => true, 'style' => 'width: 60px;']) ?>
    <?= $this->Form->control('q', ['type' => 'text', 'value' => $keyword, 'escape' => true]) ?>
    <?= $this->Form->button('検索', ['class' => 'btn btn-primary']) ?>
    <?php if ($Identity->can('add', $payjpCharge)) : ?>
        <?= $this->Html->link('新規作成', ['action' => 'add'], ['class' => 'btn btn-primary ms-4']) ?>
    <?php endif; ?>

    <?= $this->Form->end() ?>

    <section class="mx-1 my-3">
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0 text-nowrap">
            <thead>
                <tr>
                                            <th>id</th>
                                            <th>user_id</th>
                                            <th>point_book_id</th>
                                            <th>status</th>
                                            <th>type</th>
                                            <th>payjp_status</th>
                                            <th>payjp_customer_code</th>
                                            <th>ayjp_checkout_session_code</th>
                                            <th>payjp_payment_flow_code</th>
                                            <th>payjp_payment_method_code</th>
                                            <th>amount</th>
                                            <th>card_brand</th>
                                            <th>card_last4</th>
                                            <th>idempotency_key</th>
                                            <th>log</th>
                                            <th>created</th>
                                            <th>modified</th>
                                        <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payjpCharges as $payjpCharge) : ?>
                    <tr>
                                                                                                                                                                                                                                                                                                                                                                                                                                        <td><?= $this->Number->format($payjpCharge->id) ?></td>
                                                                                                                                                                                                                                                                                        <td><?= $payjpCharge->hasValue('user') ? $this->Html->link($payjpCharge->user->name, ['controller' => 'Users', 'action' => 'view', $payjpCharge->user->id]) : '' ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <td><?= $payjpCharge->hasValue('point_book') ? $this->Html->link($payjpCharge->point_book->action, ['controller' => 'PointBooks', 'action' => 'view', $payjpCharge->point_book->id]) : '' ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <td><?= h($payjpCharge->status) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <td><?= h($payjpCharge->type) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <td><?= h($payjpCharge->payjp_status) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <td><?= h($payjpCharge->payjp_customer_code) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <td><?= h($payjpCharge->ayjp_checkout_session_code) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <td><?= h($payjpCharge->payjp_payment_flow_code) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <td><?= h($payjpCharge->payjp_payment_method_code) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <td><?= $this->Number->format($payjpCharge->amount) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <td><?= h($payjpCharge->card_brand) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <td><?= h($payjpCharge->card_last4) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <td><?= h($payjpCharge->idempotency_key) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <td><?= h($payjpCharge->log) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <td><?= h($payjpCharge->created) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <td><?= h($payjpCharge->modified) ?></td>
                                                                                                            <td class="table-action">
                            <a href="/admin/payjpCharges/view/<?= $payjpCharge->id ?>" class="action-icon"> <i class="mdi mdi-eye"></i></a>
                            <?php if ($Identity->can('edit', $payjpCharge)) : ?>
                                <a href="/admin/payjpCharges/edit/<?= $payjpCharge->id ?>" class="action-icon"> <i class="mdi mdi-pencil"></i></a>
                            <?php endif; ?>
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
        <li>検索は左から、Id、フリーキーワードです。</li>
        <li>フリーキーワードは、名前、が対象です。</li>
    </ul>
</div>