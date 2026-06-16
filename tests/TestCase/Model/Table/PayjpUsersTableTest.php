<?php
declare(strict_types=1);

namespace Payjp\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use Payjp\Model\Table\PayjpUsersTable;

/**
 * Payjp\Model\Table\PayjpUsersTable Test Case
 *
 * @uses \Payjp\Model\Table\PayjpUsersTable
 */
class PayjpUsersTableTest extends TestCase
{
    protected PayjpUsersTable $PayjpUsers;

    protected array $fixtures = [
        'plugin.Payjp.PayjpUsers',
        'plugin.Payjp.Users',
        'plugin.Payjp.Companies',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('PayjpUsers') ? [] : ['className' => PayjpUsersTable::class];
        $this->PayjpUsers = $this->getTableLocator()->get('PayjpUsers', $config);
    }

    protected function tearDown(): void
    {
        unset($this->PayjpUsers);
        parent::tearDown();
    }

    /**
     * 必須・許可値を満たす基本データ。
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return $overrides + [
            'user_id' => 1,
            'status' => 'active',
            'type' => 'auto_charge',
            'auto_charge_amount' => 10000,
        ];
    }

    // ---- validationDefault: status ----

    public function testValidation_status_requiredOnCreate(): void
    {
        $data = $this->validData();
        unset($data['status']);
        $entity = $this->PayjpUsers->newEntity($data);
        $this->assertNotEmpty($entity->getError('status'));
    }

    public function testValidation_status_allowedValues_valid(): void
    {
        foreach (['active', 'suspended', 'inactive', 'failure', 'deleted'] as $status) {
            $entity = $this->PayjpUsers->newEntity($this->validData(['status' => $status]));
            $this->assertEmpty($entity->getError('status'), "status={$status} は許可値");
        }
    }

    public function testValidation_status_invalidValue(): void
    {
        $entity = $this->PayjpUsers->newEntity($this->validData(['status' => 'unknown']));
        $this->assertNotEmpty($entity->getError('status'));
    }

    // ---- validationDefault: type ----

    public function testValidation_type_requiredOnCreate(): void
    {
        $data = $this->validData();
        unset($data['type']);
        $entity = $this->PayjpUsers->newEntity($data);
        $this->assertNotEmpty($entity->getError('type'));
    }

    public function testValidation_type_allowedValues_valid(): void
    {
        foreach (['auto_charge', 'other'] as $type) {
            $entity = $this->PayjpUsers->newEntity($this->validData(['type' => $type]));
            $this->assertEmpty($entity->getError('type'), "type={$type} は許可値");
        }
    }

    public function testValidation_type_invalidValue(): void
    {
        $entity = $this->PayjpUsers->newEntity($this->validData(['type' => 'invalid']));
        $this->assertNotEmpty($entity->getError('type'));
    }

    // ---- validationDefault: auto_charge_amount ----

    public function testValidation_autoChargeAmount_allowsEmpty(): void
    {
        $entity = $this->PayjpUsers->newEntity($this->validData(['auto_charge_amount' => null]));
        $this->assertEmpty($entity->getError('auto_charge_amount'));
    }

    public function testValidation_autoChargeAmount_mustBeInteger(): void
    {
        $entity = $this->PayjpUsers->newEntity($this->validData(['auto_charge_amount' => 'abc']));
        $this->assertNotEmpty($entity->getError('auto_charge_amount'));
    }

    // ---- buildRules: user_id existsIn ----

    public function testBuildRules_userId_exists(): void
    {
        $entity = $this->PayjpUsers->newEntity($this->validData(['user_id' => 1]));
        $this->assertNotFalse($this->PayjpUsers->save($entity));
    }

    public function testBuildRules_userId_notExists(): void
    {
        $entity = $this->PayjpUsers->newEntity($this->validData(['user_id' => 9999]));
        $this->assertFalse($this->PayjpUsers->save($entity));
        $this->assertNotEmpty($entity->getError('user_id'));
    }

    // ---- findByUser ----

    public function testFindByUser_returnsOnlyMatchingUser(): void
    {
        $results = $this->PayjpUsers->find('byUser', userId: 1)->toArray();
        $this->assertNotEmpty($results);
        foreach ($results as $row) {
            $this->assertSame(1, $row->user_id);
        }
    }

    public function testFindByUser_noMatch_returnsEmpty(): void
    {
        $results = $this->PayjpUsers->find('byUser', userId: 999)->toArray();
        $this->assertSame([], $results);
    }

    // ---- findActiveByUser ----

    public function testFindActiveByUser_returnsActiveWithPaymentMethod(): void
    {
        // user 1: active かつ payjp_payment_method_code あり → hit
        $results = $this->PayjpUsers->find('activeByUser', userId: 1)->toArray();
        $this->assertCount(1, $results);
        $this->assertSame(1, $results[0]->id);
        $this->assertSame('active', $results[0]->status);
        $this->assertNotEmpty($results[0]->payjp_payment_method_code);
    }

    public function testFindActiveByUser_excludesActiveWithoutPaymentMethod(): void
    {
        // user 2: active だが payjp_payment_method_code が NULL → 除外
        $results = $this->PayjpUsers->find('activeByUser', userId: 2)->toArray();
        $this->assertSame([], $results);
    }

    public function testFindActiveByUser_excludesSuspended(): void
    {
        // user 3: suspended（pm あり）→ 除外
        $results = $this->PayjpUsers->find('activeByUser', userId: 3)->toArray();
        $this->assertSame([], $results);
    }

    public function testFindActiveByUser_excludesInactive(): void
    {
        // user 4: inactive → 除外
        $results = $this->PayjpUsers->find('activeByUser', userId: 4)->toArray();
        $this->assertSame([], $results);
    }
}
