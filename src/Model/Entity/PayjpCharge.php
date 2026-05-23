<?php
declare(strict_types=1);

namespace Payjp\Model\Entity;
use Member\Model\Entity\AppEntity;

/**
 * PayjpCharge Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $point_book_id
 * @property string $status
 * @property string $type
 * @property string|null $payjp_status
 * @property string|null $payjp_customer_code
 * @property string|null $payjp_charge_code
 * @property int $amount
 * @property string|null $payjp_card_token
 * @property string|null $card_brand
 * @property string|null $card_last4
 * @property string|null $idempotency_key
 * @property string|null $log
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \Payjp\Model\Entity\User $user
 * @property \Payjp\Model\Entity\PointBook $point_book
 */
class PayjpCharge extends AppEntity
{
    /**
    public const STATUS = [
        'temp' => '仮登録',
        'active' => '有効',
        'inactive' => '無効',
    ];
    */

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
        'point_book_id' => true,
        'status' => true,
        'type' => true,
        'payjp_status' => true,
        'payjp_customer_code' => true,
        'payjp_charge_code' => true,
        'amount' => true,
        'payjp_card_token' => true,
        'card_brand' => true,
        'card_last4' => true,
        'idempotency_key' => true,
        'log' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'point_book' => true,
    ];
}
