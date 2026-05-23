<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $payjpUser
 * @var string[]|\Cake\Collection\CollectionInterface $users
 */
?>
<div class="container-fluid">
    <div class="page-title-box">
        <h4 class="page-title">Payjp Users 編集</h4>
    </div>
    <section class="row mx-1 my-1">
        <div class="col-6">
            <?= $this->Html->link('一覧に戻る', ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= $this->Html->link('詳細に戻る', ['action' => 'view', $payjpUser->id], ['class' => 'btn btn-outline-primary']) ?>
        </div>
        <div class="col-6">
        </div>
    </section>

<section class="p-2">
    <div class="row mx-1 my-1">
        <div class="col-md-8">
        <?= $this->Form->create($payjpUser, ['novalidate' => true]) ?>
                            <div class="row mb-3">
                    <div class="col-12 small text-muted">
                        <span class="me-2">id:<?= $payjpUser->id ?></span>
                        <span class="me-2">modified:<?= $payjpUser->modified ?></span>
                        <span class="me-2">created:<?= $payjpUser->created ?></span>
                    </div>
                </div>
                                                    <div class="row">                    <div class="col-6">
                        <?= $this->Form->control('user_id', ['type' => 'text', 'label' => 'user_id']) ?>
                    </div>
                    <div class="col-6"></div>
                                </div>                            <div class="row">                    <div class="col-6">
                                                    <?= $this->Form->control('status', ['options' => $statuses, 'type' => 'select', 'label' => 'status', 'id' => 'statusStatus']) ?>
                                            </div>
                    <div class="col-6"></div>
                                </div>                            <div class="row">                    <div class="col-6">
                        <?= $this->Form->control('type', ['type' => 'text', 'label' => 'type']) ?>
                    </div>
                    <div class="col-6"></div>
                                </div>                            <div class="row">                    <div class="col-6">
                        <?= $this->Form->control('auto_charge_amount', ['type' => 'text', 'label' => 'auto_charge_amount']) ?>
                    </div>
                    <div class="col-6"></div>
                                </div>                            <div class="row">                    <div class="col-6">
                        <?= $this->Form->control('payjp_card_token', ['type' => 'text', 'label' => 'payjp_card_token']) ?>
                    </div>
                    <div class="col-6"></div>
                                </div>                            <div class="row">                    <div class="col-6">
                        <?= $this->Form->control('payjp_customer_id', ['type' => 'text', 'label' => 'payjp_customer_id']) ?>
                    </div>
                    <div class="col-6"></div>
                                </div>                            <div class="row">                    <div class="col-6">
                        <?= $this->Form->control('card_brand', ['type' => 'text', 'label' => 'card_brand']) ?>
                    </div>
                    <div class="col-6"></div>
                                </div>                            <div class="row">                    <div class="col-6">
                        <?= $this->Form->control('card_last4', ['type' => 'text', 'label' => 'card_last4']) ?>
                    </div>
                    <div class="col-6"></div>
                                </div>                            <div class="row">                    <div class="col-6">
                        <?= $this->Form->control('last_synced', ['type' => 'datetime', 'label' => 'last_synced']) ?>
                    </div>
                    <div class="col-6"></div>
                                </div>                            <div class="row">                    <div class="col-12">
                        <?= $this->Form->control('log', ['type' => 'textarea', 'label' => 'log', 'rows' => 3]) ?>
                    </div>
                                </div>                                    
            <div class="my-4 text-center">
                                    <?= $this->Form->submit('編集', ['class' => 'btn btn-success']) ?>
                            </div>
        </div>
        <div class="col-md-4"></div>
        <?= $this->Form->end() ?>
    </div>
</section>


</div>

<div class="container-fluid mt-3">
    <ul class="small text-muted">
        <li>補足説明</li>
    </ul>
</div>