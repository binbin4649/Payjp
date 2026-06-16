<?php
declare(strict_types=1);

namespace Payjp\Model\Entity;
use Member\Model\Entity\AppEntity;

/**
 * PayjpUser Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $status
 * @property string $type
 * @property int|null $auto_charge_amount
 * @property string|null $payjp_customer_code
 * @property string|null $payjp_payment_method_code
 * @property string|null $card_brand
 * @property string|null $card_last4
 * @property \Cake\I18n\DateTime|null $last_synced
 * @property string|null $log
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \Payjp\Model\Entity\User $user
 */
class PayjpUser extends AppEntity
{
    public const STATUS = [
        'active' => '正常稼働',
        'suspended' => 'リトライ待ち',
        'inactive' => '停止',
        'failure' => '失敗',
        'deleted' => '退会済み',
    ];

    public const TYPE = [
        'auto_charge' => 'オートチャージ',
        'other' => 'その他',
    ];

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'status' => true,
        'type' => true,
        'auto_charge_amount' => true,
        'payjp_customer_code' => true,
        'payjp_payment_method_code' => true,
        'card_brand' => true,
        'card_last4' => true,
        'last_synced' => true,
        'log' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];
}
