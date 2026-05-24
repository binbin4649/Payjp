<?php

declare(strict_types=1);

namespace Payjp\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use Payjp\Model\Table\PayjpChargesTable;

class PayjpChargesTableTest extends TestCase
{
    protected PayjpChargesTable $PayjpCharges;

    protected array $fixtures = [
        'plugin.Payjp.PayjpCharges',
        'plugin.Payjp.Users',
        'plugin.Payjp.PointBooks',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Payjp.PayjpCharges') ? [] : ['className' => PayjpChargesTable::class];
        $this->PayjpCharges = $this->getTableLocator()->get('Payjp.PayjpCharges', $config);
    }

    protected function tearDown(): void
    {
        unset($this->PayjpCharges);
        parent::tearDown();
    }

    // ---- validationDefault ----

    public function testValidation_status_required(): void
    {
        $entity = $this->PayjpCharges->newEntity([
            'user_id' => 1,
            'type' => 'one_time',
            'amount' => 1000,
        ]);
        $this->assertNotEmpty($entity->getError('status'));
    }

    public function testValidation_status_allowedValues_invalid(): void
    {
        $entity = $this->PayjpCharges->newEntity([
            'user_id' => 1,
            'status' => 'invalid',
            'type' => 'one_time',
            'amount' => 1000,
        ]);
        $this->assertNotEmpty($entity->getError('status'));
    }

    public function testValidation_status_allowedValues_success(): void
    {
        $entity = $this->PayjpCharges->newEntity([
            'user_id' => 1,
            'status' => 'success',
            'type' => 'one_time',
            'amount' => 1000,
        ]);
        $this->assertEmpty($entity->getError('status'));
    }

    public function testValidation_status_allowedValues_failure(): void
    {
        $entity = $this->PayjpCharges->newEntity([
            'user_id' => 1,
            'status' => 'failure',
            'type' => 'one_time',
            'amount' => 1000,
        ]);
        $this->assertEmpty($entity->getError('status'));
    }

    public function testValidation_type_required(): void
    {
        $entity = $this->PayjpCharges->newEntity([
            'user_id' => 1,
            'status' => 'success',
            'amount' => 1000,
        ]);
        $this->assertNotEmpty($entity->getError('type'));
    }

    public function testValidation_type_allowedValues_invalid(): void
    {
        $entity = $this->PayjpCharges->newEntity([
            'user_id' => 1,
            'status' => 'success',
            'type' => 'invalid',
            'amount' => 1000,
        ]);
        $this->assertNotEmpty($entity->getError('type'));
    }

    public function testValidation_type_allowedValues_one_time(): void
    {
        $entity = $this->PayjpCharges->newEntity([
            'user_id' => 1,
            'status' => 'success',
            'type' => 'one_time',
            'amount' => 1000,
        ]);
        $this->assertEmpty($entity->getError('type'));
    }

    public function testValidation_type_allowedValues_auto_charge(): void
    {
        $entity = $this->PayjpCharges->newEntity([
            'user_id' => 1,
            'status' => 'success',
            'type' => 'auto_charge',
            'amount' => 1000,
        ]);
        $this->assertEmpty($entity->getError('type'));
    }

    public function testValidation_amount_required(): void
    {
        $entity = $this->PayjpCharges->newEntity([
            'user_id' => 1,
            'status' => 'success',
            'type' => 'one_time',
        ]);
        $this->assertNotEmpty($entity->getError('amount'));
    }

    public function testValidation_point_book_id_nullable(): void
    {
        $entity = $this->PayjpCharges->newEntity([
            'user_id' => 1,
            'status' => 'failure',
            'type' => 'one_time',
            'amount' => 1000,
            'point_book_id' => null,
        ]);
        $this->assertEmpty($entity->getError('point_book_id'));
    }

    // ---- buildRules ----

    public function testBuildRules_userId_exists(): void
    {
        $entity = $this->PayjpCharges->newEntity([
            'user_id' => 9999,
            'status' => 'success',
            'type' => 'one_time',
            'amount' => 1000,
        ]);
        $result = $this->PayjpCharges->save($entity);
        $this->assertFalse($result);
        $this->assertNotEmpty($entity->getError('user_id'));
    }

    // ---- findByUser ----

    public function testFindByUser_returnsRecordsOrderedByCreatedDesc(): void
    {
        $results = $this->PayjpCharges->find('byUser', userId: 1)->toArray();
        $this->assertNotEmpty($results);
        foreach ($results as $charge) {
            $this->assertSame(1, $charge->user_id);
        }
    }

    public function testFindByUser_noMatch(): void
    {
        $results = $this->PayjpCharges->find('byUser', userId: 999)->toArray();
        $this->assertCount(0, $results);
    }
}
