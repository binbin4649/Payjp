<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Cake\Datasource\EntityInterface> $payjpCharges
 */
?>
<div class="payjpCharges index content">
    <?= $this->Html->link(__('New Payjp Charge'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Payjp Charges') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('point_book_id') ?></th>
                    <th><?= $this->Paginator->sort('status') ?></th>
                    <th><?= $this->Paginator->sort('type') ?></th>
                    <th><?= $this->Paginator->sort('payjp_status') ?></th>
                    <th><?= $this->Paginator->sort('payjp_customer_code') ?></th>
                    <th><?= $this->Paginator->sort('payjp_charge_code') ?></th>
                    <th><?= $this->Paginator->sort('amount') ?></th>
                    <th><?= $this->Paginator->sort('payjp_card_token') ?></th>
                    <th><?= $this->Paginator->sort('card_brand') ?></th>
                    <th><?= $this->Paginator->sort('card_last4') ?></th>
                    <th><?= $this->Paginator->sort('idempotency_key') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payjpCharges as $payjpCharge): ?>
                <tr>
                    <td><?= $this->Number->format($payjpCharge->id) ?></td>
                    <td><?= $payjpCharge->hasValue('user') ? $this->Html->link($payjpCharge->user->name, ['controller' => 'Users', 'action' => 'view', $payjpCharge->user->id]) : '' ?></td>
                    <td><?= $payjpCharge->hasValue('point_book') ? $this->Html->link($payjpCharge->point_book->action, ['controller' => 'PointBooks', 'action' => 'view', $payjpCharge->point_book->id]) : '' ?></td>
                    <td><?= h($payjpCharge->status) ?></td>
                    <td><?= h($payjpCharge->type) ?></td>
                    <td><?= h($payjpCharge->payjp_status) ?></td>
                    <td><?= h($payjpCharge->payjp_customer_code) ?></td>
                    <td><?= h($payjpCharge->payjp_charge_code) ?></td>
                    <td><?= $this->Number->format($payjpCharge->amount) ?></td>
                    <td><?= h($payjpCharge->payjp_card_token) ?></td>
                    <td><?= h($payjpCharge->card_brand) ?></td>
                    <td><?= h($payjpCharge->card_last4) ?></td>
                    <td><?= h($payjpCharge->idempotency_key) ?></td>
                    <td><?= h($payjpCharge->created) ?></td>
                    <td><?= h($payjpCharge->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $payjpCharge->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $payjpCharge->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $payjpCharge->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $payjpCharge->id),
                            ]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>