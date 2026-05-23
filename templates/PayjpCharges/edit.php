<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $payjpCharge
 * @var string[]|\Cake\Collection\CollectionInterface $users
 * @var string[]|\Cake\Collection\CollectionInterface $pointBooks
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $payjpCharge->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $payjpCharge->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Payjp Charges'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="payjpCharges form content">
            <?= $this->Form->create($payjpCharge) ?>
            <fieldset>
                <legend><?= __('Edit Payjp Charge') ?></legend>
                <?php
                    echo $this->Form->control('user_id', ['options' => $users]);
                    echo $this->Form->control('point_book_id', ['options' => $pointBooks]);
                    echo $this->Form->control('status');
                    echo $this->Form->control('type');
                    echo $this->Form->control('payjp_status');
                    echo $this->Form->control('payjp_customer_code');
                    echo $this->Form->control('payjp_charge_code');
                    echo $this->Form->control('amount');
                    echo $this->Form->control('payjp_card_token');
                    echo $this->Form->control('card_brand');
                    echo $this->Form->control('card_last4');
                    echo $this->Form->control('idempotency_key');
                    echo $this->Form->control('log');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
