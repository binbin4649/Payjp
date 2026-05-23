<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $payjpCharge
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Payjp Charge'), ['action' => 'edit', $payjpCharge->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Payjp Charge'), ['action' => 'delete', $payjpCharge->id], ['confirm' => __('Are you sure you want to delete # {0}?', $payjpCharge->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Payjp Charges'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Payjp Charge'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="payjpCharges view content">
            <h3><?= h($payjpCharge->status) ?></h3>
            <table>
                <tr>
                    <th><?= __('User') ?></th>
                    <td><?= $payjpCharge->hasValue('user') ? $this->Html->link($payjpCharge->user->name, ['controller' => 'Users', 'action' => 'view', $payjpCharge->user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Point Book') ?></th>
                    <td><?= $payjpCharge->hasValue('point_book') ? $this->Html->link($payjpCharge->point_book->action, ['controller' => 'PointBooks', 'action' => 'view', $payjpCharge->point_book->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Status') ?></th>
                    <td><?= h($payjpCharge->status) ?></td>
                </tr>
                <tr>
                    <th><?= __('Type') ?></th>
                    <td><?= h($payjpCharge->type) ?></td>
                </tr>
                <tr>
                    <th><?= __('Payjp Status') ?></th>
                    <td><?= h($payjpCharge->payjp_status) ?></td>
                </tr>
                <tr>
                    <th><?= __('Payjp Customer Code') ?></th>
                    <td><?= h($payjpCharge->payjp_customer_code) ?></td>
                </tr>
                <tr>
                    <th><?= __('Payjp Charge Code') ?></th>
                    <td><?= h($payjpCharge->payjp_charge_code) ?></td>
                </tr>
                <tr>
                    <th><?= __('Payjp Card Token') ?></th>
                    <td><?= h($payjpCharge->payjp_card_token) ?></td>
                </tr>
                <tr>
                    <th><?= __('Card Brand') ?></th>
                    <td><?= h($payjpCharge->card_brand) ?></td>
                </tr>
                <tr>
                    <th><?= __('Card Last4') ?></th>
                    <td><?= h($payjpCharge->card_last4) ?></td>
                </tr>
                <tr>
                    <th><?= __('Idempotency Key') ?></th>
                    <td><?= h($payjpCharge->idempotency_key) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($payjpCharge->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Amount') ?></th>
                    <td><?= $this->Number->format($payjpCharge->amount) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($payjpCharge->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($payjpCharge->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Log') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($payjpCharge->log)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>