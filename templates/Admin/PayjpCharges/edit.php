<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $payjpCharge
 * @var string[]|\Cake\Collection\CollectionInterface $users
 * @var string[]|\Cake\Collection\CollectionInterface $pointBooks
 */
?>
<div class="container-fluid">
    <div class="page-title-box">
        <h4 class="page-title">Payjp Charges 編集</h4>
    </div>
    <section class="row mx-1 my-1">
        <div class="col-6">
            <?= $this->Html->link('一覧に戻る', ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= $this->Html->link('詳細に戻る', ['action' => 'view', $payjpCharge->id], ['class' => 'btn btn-outline-primary']) ?>
        </div>
        <div class="col-6">
        </div>
    </section>

<section class="p-2">
    <div class="row mx-1 my-1">
        <div class="col-md-8">
        <?= $this->Form->create($payjpCharge, ['novalidate' => true]) ?>
                            <div class="row mb-3">
                    <div class="col-12 small text-muted">
                        <span class="me-2">id:<?= $payjpCharge->id ?></span>
                        <span class="me-2">modified:<?= $payjpCharge->modified ?></span>
                        <span class="me-2">created:<?= $payjpCharge->created ?></span>
                    </div>
                </div>
                                                    <div class="row">                    <div class="col-6">
                        <?= $this->Form->control('user_id', ['type' => 'text', 'label' => 'user_id']) ?>
                    </div>
                    <div class="col-6"></div>
                                </div>                            <div class="row">                    <div class="col-6">
                        <?= $this->Form->control('point_book_id', ['type' => 'text', 'label' => 'point_book_id']) ?>
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
                        <?= $this->Form->control('payjp_status', ['type' => 'text', 'label' => 'payjp_status']) ?>
                    </div>
                    <div class="col-6"></div>
                                </div>                            <div class="row">                    <div class="col-6">
                        <?= $this->Form->control('payjp_customer_code', ['type' => 'text', 'label' => 'payjp_customer_code']) ?>
                    </div>
                    <div class="col-6"></div>
                                </div>                            <div class="row">                    <div class="col-6">
                        <?= $this->Form->control('ayjp_checkout_session_code', ['type' => 'text', 'label' => 'ayjp_checkout_session_code']) ?>
                    </div>
                    <div class="col-6"></div>
                                </div>                            <div class="row">                    <div class="col-6">
                        <?= $this->Form->control('payjp_payment_flow_code', ['type' => 'text', 'label' => 'payjp_payment_flow_code']) ?>
                    </div>
                    <div class="col-6"></div>
                                </div>                            <div class="row">                    <div class="col-6">
                        <?= $this->Form->control('payjp_payment_method_code', ['type' => 'text', 'label' => 'payjp_payment_method_code']) ?>
                    </div>
                    <div class="col-6"></div>
                                </div>                            <div class="row">                    <div class="col-6">
                        <?= $this->Form->control('amount', ['type' => 'text', 'label' => 'amount']) ?>
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
                        <?= $this->Form->control('idempotency_key', ['type' => 'text', 'label' => 'idempotency_key']) ?>
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