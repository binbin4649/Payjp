<?php
declare(strict_types=1);

namespace Payjp\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Member\Model\Table\AppTable;
use Payjp\Model\Entity\PayjpUser;

/**
 * PayjpUsers Model
 *
 * @property \Payjp\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \Payjp\Model\Entity\PayjpUser newEmptyEntity()
 * @method \Payjp\Model\Entity\PayjpUser newEntity(array $data, array $options = [])
 * @method array<\Payjp\Model\Entity\PayjpUser> newEntities(array $data, array $options = [])
 * @method \Payjp\Model\Entity\PayjpUser get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Payjp\Model\Entity\PayjpUser findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \Payjp\Model\Entity\PayjpUser patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Payjp\Model\Entity\PayjpUser> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Payjp\Model\Entity\PayjpUser|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Payjp\Model\Entity\PayjpUser saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\Payjp\Model\Entity\PayjpUser>|\Cake\Datasource\ResultSetInterface<\Payjp\Model\Entity\PayjpUser>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\Payjp\Model\Entity\PayjpUser>|\Cake\Datasource\ResultSetInterface<\Payjp\Model\Entity\PayjpUser> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\Payjp\Model\Entity\PayjpUser>|\Cake\Datasource\ResultSetInterface<\Payjp\Model\Entity\PayjpUser>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\Payjp\Model\Entity\PayjpUser>|\Cake\Datasource\ResultSetInterface<\Payjp\Model\Entity\PayjpUser> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PayjpUsersTable extends AppTable
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

        $this->setTable('payjp_users');
        $this->setDisplayField('status');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Member.ChangeLog');
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
            'className' => 'Payjp.Users',
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
            ->scalar('status')
            ->maxLength('status', 255)
            ->inList('status', array_keys(PayjpUser::STATUS))
            ->requirePresence('status', 'create')
            ->notEmptyString('status');

        $validator
            ->scalar('type')
            ->maxLength('type', 255)
            ->inList('type', array_keys(PayjpUser::TYPE))
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
            ->integer('auto_charge_amount')
            ->allowEmptyString('auto_charge_amount');

        $validator
            ->scalar('payjp_card_token')
            ->maxLength('payjp_card_token', 255)
            ->allowEmptyString('payjp_card_token');

        $validator
            ->scalar('payjp_customer_code')
            ->maxLength('payjp_customer_code', 255)
            ->allowEmptyString('payjp_customer_code');

        $validator
            ->scalar('card_brand')
            ->maxLength('card_brand', 255)
            ->allowEmptyString('card_brand');

        $validator
            ->scalar('card_last4')
            ->maxLength('card_last4', 255)
            ->allowEmptyString('card_last4');

        $validator
            ->dateTime('last_synced')
            ->allowEmptyDateTime('last_synced');

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
        return $query->where(['PayjpUsers.user_id' => $userId]);
    }

    public function findActiveByUser(SelectQuery $query, int $userId): SelectQuery
    {
        return $query->where(['PayjpUsers.user_id' => $userId, 'PayjpUsers.status' => 'active']);
    }
}
