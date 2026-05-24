<?php

declare(strict_types=1);

namespace Payjp\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use Payjp\Model\Table\PayjpUsersTable;

class PayjpUsersTableTest extends TestCase
{
    protected PayjpUsersTable $PayjpUsers;

    protected array $fixtures = [
        'plugin.Payjp.PayjpUsers',
        'plugin.Payjp.Users',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Payjp.PayjpUsers') ? [] : ['className' => PayjpUsersTable::class];
        $this->PayjpUsers = $this->getTableLocator()->get('Payjp.PayjpUsers', $config);
    }

    protected function tearDown(): void
    {
        unset($this->PayjpUsers);
        parent::tearDown();
    }

    // ---- validationDefault ----

    public function testValidation_status_required(): void
    {
        $entity = $this->PayjpUsers->newEntity([
            'user_id' => 1,
            'type' => 'auto_charge',
        ]);
        $this->assertNotEmpty($entity->getError('status'));
    }

    public function testValidation_status_allowedValues_invalid(): void
    {
        $entity = $this->PayjpUsers->newEntity([
            'user_id' => 1,
            'status' => 'invalid',
            'type' => 'auto_charge',
        ]);
        $this->assertNotEmpty($entity->getError('status'));
    }

    public function testValidation_status_allowedValues_active(): void
    {
        $entity = $this->PayjpUsers->newEntity([
            'user_id' => 1,
            'status' => 'active',
            'type' => 'auto_charge',
        ]);
        $this->assertEmpty($entity->getError('status'));
    }

    public function testValidation_status_allowedValues_suspended(): void
    {
        $entity = $this->PayjpUsers->newEntity([
            'user_id' => 1,
            'status' => 'suspended',
            'type' => 'auto_charge',
        ]);
        $this->assertEmpty($entity->getError('status'));
    }

    public function testValidation_status_allowedValues_inactive(): void
    {
        $entity = $this->PayjpUsers->newEntity([
            'user_id' => 1,
            'status' => 'inactive',
            'type' => 'auto_charge',
        ]);
        $this->assertEmpty($entity->getError('status'));
    }

    public function testValidation_status_allowedValues_failure(): void
    {
        $entity = $this->PayjpUsers->newEntity([
            'user_id' => 1,
            'status' => 'failure',
            'type' => 'auto_charge',
        ]);
        $this->assertEmpty($entity->getError('status'));
    }

    public function testValidation_status_allowedValues_deleted(): void
    {
        $entity = $this->PayjpUsers->newEntity([
            'user_id' => 1,
            'status' => 'deleted',
            'type' => 'auto_charge',
        ]);
        $this->assertEmpty($entity->getError('status'));
    }

    public function testValidation_type_required(): void
    {
        $entity = $this->PayjpUsers->newEntity([
            'user_id' => 1,
            'status' => 'active',
        ]);
        $this->assertNotEmpty($entity->getError('type'));
    }

    public function testValidation_type_allowedValues_invalid(): void
    {
        $entity = $this->PayjpUsers->newEntity([
            'user_id' => 1,
            'status' => 'active',
            'type' => 'invalid',
        ]);
        $this->assertNotEmpty($entity->getError('type'));
    }

    public function testValidation_type_allowedValues_auto_charge(): void
    {
        $entity = $this->PayjpUsers->newEntity([
            'user_id' => 1,
            'status' => 'active',
            'type' => 'auto_charge',
        ]);
        $this->assertEmpty($entity->getError('type'));
    }

    public function testValidation_type_allowedValues_other(): void
    {
        $entity = $this->PayjpUsers->newEntity([
            'user_id' => 1,
            'status' => 'active',
            'type' => 'other',
        ]);
        $this->assertEmpty($entity->getError('type'));
    }

    public function testValidation_auto_charge_amount_nullable(): void
    {
        $entity = $this->PayjpUsers->newEntity([
            'user_id' => 1,
            'status' => 'active',
            'type' => 'auto_charge',
            'auto_charge_amount' => null,
        ]);
        $this->assertEmpty($entity->getError('auto_charge_amount'));
    }

    // ---- buildRules ----

    public function testBuildRules_userId_exists(): void
    {
        $entity = $this->PayjpUsers->newEntity([
            'user_id' => 9999,
            'status' => 'active',
            'type' => 'auto_charge',
        ]);
        $result = $this->PayjpUsers->save($entity);
        $this->assertFalse($result);
        $this->assertNotEmpty($entity->getError('user_id'));
    }

    // ---- findByUser ----

    public function testFindByUser_returnsRecords(): void
    {
        $results = $this->PayjpUsers->find('byUser', userId: 1)->toArray();
        $this->assertCount(1, $results);
        $this->assertSame(1, $results[0]->user_id);
    }

    public function testFindByUser_noMatch(): void
    {
        $results = $this->PayjpUsers->find('byUser', userId: 999)->toArray();
        $this->assertCount(0, $results);
    }

    // ---- findActiveByUser ----

    public function testFindActiveByUser_returnsActiveOnly(): void
    {
        $results = $this->PayjpUsers->find('activeByUser', userId: 1)->toArray();
        $this->assertCount(1, $results);
        $this->assertSame('active', $results[0]->status);
    }

    public function testFindActiveByUser_excludesSuspended(): void
    {
        $results = $this->PayjpUsers->find('activeByUser', userId: 2)->toArray();
        $this->assertCount(0, $results);
    }

    public function testFindActiveByUser_noMatch(): void
    {
        $results = $this->PayjpUsers->find('activeByUser', userId: 999)->toArray();
        $this->assertCount(0, $results);
    }
}
