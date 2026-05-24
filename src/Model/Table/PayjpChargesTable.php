<?php
declare(strict_types=1);

namespace Payjp\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Member\Model\Table\AppTable;
use Payjp\Model\Entity\PayjpCharge;

/**
 * PayjpCharges Model
 *
 * @property \Payjp\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \Payjp\Model\Table\PointBooksTable&\Cake\ORM\Association\BelongsTo $PointBooks
 *
 * @method \Payjp\Model\Entity\PayjpCharge newEmptyEntity()
 * @method \Payjp\Model\Entity\PayjpCharge newEntity(array $data, array $options = [])
 * @method array<\Payjp\Model\Entity\PayjpCharge> newEntities(array $data, array $options = [])
 * @method \Payjp\Model\Entity\PayjpCharge get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Payjp\Model\Entity\PayjpCharge findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \Payjp\Model\Entity\PayjpCharge patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Payjp\Model\Entity\PayjpCharge> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Payjp\Model\Entity\PayjpCharge|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Payjp\Model\Entity\PayjpCharge saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\Payjp\Model\Entity\PayjpCharge>|\Cake\Datasource\ResultSetInterface<\Payjp\Model\Entity\PayjpCharge>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\Payjp\Model\Entity\PayjpCharge>|\Cake\Datasource\ResultSetInterface<\Payjp\Model\Entity\PayjpCharge> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\Payjp\Model\Entity\PayjpCharge>|\Cake\Datasource\ResultSetInterface<\Payjp\Model\Entity\PayjpCharge>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\Payjp\Model\Entity\PayjpCharge>|\Cake\Datasource\ResultSetInterface<\Payjp\Model\Entity\PayjpCharge> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PayjpChargesTable extends AppTable
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('payjp_charges');
        $this->setDisplayField('status');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Member.ChangeLog');
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
            'className' => 'Payjp.Users',
        ]);
        $this->belongsTo('PointBooks', [
            'foreignKey' => 'point_book_id',
            'joinType' => 'LEFT',
            'className' => 'Payjp.PointBooks',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('point_book_id')
            ->allowEmptyString('point_book_id');

        $validator
            ->scalar('status')
            ->maxLength('status', 255)
            ->inList('status', array_keys(PayjpCharge::STATUS))
            ->requirePresence('status', 'create')
            ->notEmptyString('status');

        $validator
            ->scalar('type')
            ->maxLength('type', 255)
            ->inList('type', array_keys(PayjpCharge::TYPE))
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
            ->scalar('payjp_status')
            ->maxLength('payjp_status', 255)
            ->allowEmptyString('payjp_status');

        $validator
            ->scalar('payjp_customer_code')
            ->maxLength('payjp_customer_code', 255)
            ->allowEmptyString('payjp_customer_code');

        $validator
            ->scalar('payjp_charge_code')
            ->maxLength('payjp_charge_code', 255)
            ->allowEmptyString('payjp_charge_code');

        $validator
            ->integer('amount')
            ->requirePresence('amount', 'create')
            ->notEmptyString('amount');

        $validator
            ->scalar('payjp_card_token')
            ->maxLength('payjp_card_token', 255)
            ->allowEmptyString('payjp_card_token');

        $validator
            ->scalar('card_brand')
            ->maxLength('card_brand', 255)
            ->allowEmptyString('card_brand');

        $validator
            ->scalar('card_last4')
            ->maxLength('card_last4', 255)
            ->allowEmptyString('card_last4');

        $validator
            ->scalar('idempotency_key')
            ->maxLength('idempotency_key', 255)
            ->allowEmptyString('idempotency_key');

        $validator
            ->scalar('log')
            ->allowEmptyString('log');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    public function findByUser(SelectQuery $query, int $userId): SelectQuery
    {
        return $query->where(['PayjpCharges.user_id' => $userId])
                     ->orderByDesc('PayjpCharges.created');
    }
}
