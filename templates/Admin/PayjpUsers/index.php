<div class="container-fluid">

    <div class="page-title-box">
        <h4 class="page-title">Payjp Users一覧</h4>
    </div>

    <?= $this->Form->create(null, ['align' => 'inline', 'class' => 'row-cols-auto ms-1', 'type' => 'get', 'valueSources' => 'query', 'spacing' => 'g-1']) ?>
    <a href="/admin/payjpUsers" class="btn btn-sm btn-outline-light me-1"><i class="mdi mdi-reload"></i></a>
    <?= $this->Form->control('id', ['type' => 'text', 'value' => $id, 'escape' => true, 'style' => 'width: 60px;']) ?>
    <?= $this->Form->control('user_id', ['type' => 'text', 'value' => $user_id, 'escape' => true, 'style' => 'width: 80px;']) ?>
    <?= $this->Form->control('status', ['type' => 'select', 'value' => $status, 'options' => $statuses, 'empty' => '-- ステータス --']) ?>
    <?= $this->Form->control('type', ['type' => 'select', 'value' => $type, 'options' => $types, 'empty' => '-- タイプ --']) ?>
    <?= $this->Form->button('検索', ['class' => 'btn btn-primary']) ?>
    <?php if ($Identity->can('add', $payjpUser)) : ?>
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
                                            <th>status</th>
                                            <th>type</th>
                                            <th>auto_charge_amount</th>
                                            <th>payjp_card_token</th>
                                            <th>payjp_customer_code</th>
                                            <th>card_brand</th>
                                            <th>card_last4</th>
                                            <th>last_synced</th>
                                            <th>log</th>
                                            <th>created</th>
                                            <th>modified</th>
                                        <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payjpUsers as $payjpUser) : ?>
                    <tr>
                                                                                                                                                                                                                                                                                                                                                                    <td><?= $this->Number->format($payjpUser->id) ?></td>
                                                                                                                                                                                                                                                                                        <td><?= $payjpUser->hasValue('user') ? $this->Html->link($payjpUser->user->name, ['controller' => 'Users', 'action' => 'view', $payjpUser->user->id]) : '' ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <td><?= h($payjpUser->status) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                <td><?= h($payjpUser->type) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                <td><?= $payjpUser->auto_charge_amount === null ? '' : $this->Number->format($payjpUser->auto_charge_amount) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                <td><?= h($payjpUser->payjp_card_token) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                <td><?= h($payjpUser->payjp_customer_code) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                <td><?= h($payjpUser->card_brand) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                <td><?= h($payjpUser->card_last4) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                <td><?= h($payjpUser->last_synced) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                <td><?= h($payjpUser->log) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                <td><?= h($payjpUser->created) ?></td>
                                                                                                                                                                                                                                                                                                                                                                                                                                <td><?= h($payjpUser->modified) ?></td>
                                                                                                            <td class="table-action">
                            <a href="/admin/payjpUsers/view/<?= $payjpUser->id ?>" class="action-icon"> <i class="mdi mdi-eye"></i></a>
                            <?php if ($Identity->can('edit', $payjpUser)) : ?>
                                <a href="/admin/payjpUsers/edit/<?= $payjpUser->id ?>" class="action-icon"> <i class="mdi mdi-pencil"></i></a>
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
        <li>検索はId、ユーザーId、ステータス、タイプで絞り込めます。</li>
    </ul>
</div>