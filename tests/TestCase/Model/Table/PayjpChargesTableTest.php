<?php
declare(strict_types=1);

namespace Payjp\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;
use Payjp\Model\Table\PayjpChargesTable;

/**
 * Payjp\Model\Table\PayjpChargesTable Test Case
 *
 * @uses \Payjp\Model\Table\PayjpChargesTable
 */
class PayjpChargesTableTest extends TestCase
{
    protected PayjpChargesTable $PayjpCharges;

    protected array $fixtures = [
        'plugin.Payjp.PayjpCharges',
        'plugin.Payjp.Users',
        'plugin.Payjp.Companies',
        'plugin.Payjp.PointBooks',
        'plugin.Payjp.PointUsers',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('PayjpCharges') ? [] : ['className' => PayjpChargesTable::class];
        $this->PayjpCharges = $this->getTableLocator()->get('PayjpCharges', $config);
    }

    protected function tearDown(): void
    {
        unset($this->PayjpCharges);
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
            'point_book_id' => 1,
            'status' => 'success',
            'type' => 'one_time',
            'amount' => 1000,
        ];
    }

    // ---- validationDefault: status ----

    public function testValidation_status_requiredOnCreate(): void
    {
        $data = $this->validData();
        unset($data['status']);
        $entity = $this->PayjpCharges->newEntity($data);
        $this->assertNotEmpty($entity->getError('status'));
    }

    public function testValidation_status_allowedValues_valid(): void
    {
        foreach (['pending', 'processing', 'success', 'failure'] as $status) {
            $entity = $this->PayjpCharges->newEntity($this->validData(['status' => $status]));
            $this->assertEmpty($entity->getError('status'), "status={$status} は許可値");
        }
    }

    public function testValidation_status_invalidValue(): void
    {
        $entity = $this->PayjpCharges->newEntity($this->validData(['status' => 'unknown']));
        $this->assertNotEmpty($entity->getError('status'));
    }

    // ---- validationDefault: type ----

    public function testValidation_type_requiredOnCreate(): void
    {
        $data = $this->validData();
        unset($data['type']);
        $entity = $this->PayjpCharges->newEntity($data);
        $this->assertNotEmpty($entity->getError('type'));
    }

    public function testValidation_type_allowedValues_valid(): void
    {
        foreach (['one_time', 'auto_charge'] as $type) {
            $entity = $this->PayjpCharges->newEntity($this->validData(['type' => $type]));
            $this->assertEmpty($entity->getError('type'), "type={$type} は許可値");
        }
    }

    public function testValidation_type_invalidValue(): void
    {
        $entity = $this->PayjpCharges->newEntity($this->validData(['type' => 'invalid']));
        $this->assertNotEmpty($entity->getError('type'));
    }

    // ---- validationDefault: amount ----

    public function testValidation_amount_requiredOnCreate(): void
    {
        $data = $this->validData();
        unset($data['amount']);
        $entity = $this->PayjpCharges->newEntity($data);
        $this->assertNotEmpty($entity->getError('amount'));
    }

    public function testValidation_amount_mustBeInteger(): void
    {
        $entity = $this->PayjpCharges->newEntity($this->validData(['amount' => 'abc']));
        $this->assertNotEmpty($entity->getError('amount'));
    }

    // ---- buildRules ----

    public function testBuildRules_userId_notExists(): void
    {
        $entity = $this->PayjpCharges->newEntity($this->validData(['user_id' => 9999]));
        $this->assertFalse($this->PayjpCharges->save($entity));
        $this->assertNotEmpty($entity->getError('user_id'));
    }

    public function testBuildRules_pointBookId_exists(): void
    {
        $entity = $this->PayjpCharges->newEntity($this->validData(['point_book_id' => 1]));
        $this->assertNotFalse($this->PayjpCharges->save($entity));
    }

    public function testBuildRules_pointBookId_notExists(): void
    {
        $entity = $this->PayjpCharges->newEntity($this->validData(['point_book_id' => 9999]));
        $this->assertFalse($this->PayjpCharges->save($entity));
        $this->assertNotEmpty($entity->getError('point_book_id'));
    }

    public function testBuildRules_pointBookId_allowsNullForPending(): void
    {
        // 都度課金は webhook 確定前 point_book_id=NULL で保存できる
        $entity = $this->PayjpCharges->newEntity($this->validData([
            'status' => 'pending',
            'point_book_id' => null,
        ]));
        $this->assertNotFalse($this->PayjpCharges->save($entity));
    }

    // ---- findByUser ----

    public function testFindByUser_returnsOnlyMatchingUser(): void
    {
        $results = $this->PayjpCharges->find('byUser', userId: 1)->toArray();
        $this->assertNotEmpty($results);
        foreach ($results as $row) {
            $this->assertSame(1, $row->user_id);
        }
    }

    public function testFindByUser_orderedNewestFirst(): void
    {
        // fixture: user1 は id=2(2026-06-10), id=1(2026-06-01)。新しい順なら id=2 が先頭。
        $results = $this->PayjpCharges->find('byUser', userId: 1)->toArray();
        $this->assertCount(2, $results);
        $this->assertSame(2, $results[0]->id);
        $this->assertSame(1, $results[1]->id);
    }

    public function testFindByUser_noMatch_returnsEmpty(): void
    {
        $results = $this->PayjpCharges->find('byUser', userId: 999)->toArray();
        $this->assertSame([], $results);
    }

    // ---- findByCheckoutSession ----

    public function testFindByCheckoutSession_returnsMatchingRecord(): void
    {
        $results = $this->PayjpCharges->find('byCheckoutSession', sessionId: 'cs_test_002')->toArray();
        $this->assertCount(1, $results);
        $this->assertSame(2, $results[0]->id);
        $this->assertSame('cs_test_002', $results[0]->payjp_checkout_session_code);
    }

    public function testFindByCheckoutSession_noMatch_returnsEmpty(): void
    {
        $results = $this->PayjpCharges->find('byCheckoutSession', sessionId: 'cs_not_exist')->toArray();
        $this->assertSame([], $results);
    }
}
