<?php
declare(strict_types=1);

namespace Payjp\Test\TestCase\Model\Table;

use Cake\I18n\Date;
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

    // ---- validationDefault: card_deadline ----

    public function testValidation_cardDeadline_allowsEmpty(): void
    {
        $entity = $this->PayjpUsers->newEntity($this->validData(['card_deadline' => null]));
        $this->assertEmpty($entity->getError('card_deadline'));
    }

    public function testValidation_cardDeadline_acceptsDate(): void
    {
        $entity = $this->PayjpUsers->newEntity($this->validData(['card_deadline' => '2027-03-31']));
        $this->assertEmpty($entity->getError('card_deadline'));
    }

    public function testValidation_cardDeadline_invalidValue(): void
    {
        $entity = $this->PayjpUsers->newEntity($this->validData(['card_deadline' => 'not-a-date']));
        $this->assertNotEmpty($entity->getError('card_deadline'));
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

    // ---- findCurrentByUser ----

    public function testFindCurrentByUser_includesActiveSuspendedInactiveWithPaymentMethod(): void
    {
        // user 1: active+pm / user 3: suspended+pm / user 4: inactive+pm → いずれも「現在の登録カード行」
        foreach ([1, 3, 4] as $userId) {
            $row = $this->PayjpUsers->find('currentByUser', userId: $userId)->first();
            $this->assertNotNull($row, "user {$userId} は対象");
            $this->assertNotEmpty($row->payjp_payment_method_code);
        }
    }

    public function testFindCurrentByUser_excludesProvisionalWithoutPaymentMethod(): void
    {
        // user 2: active だが pm NULL（仮登録相当）→ 除外
        $this->assertNull($this->PayjpUsers->find('currentByUser', userId: 2)->first());
    }

    public function testFindCurrentByUser_excludesDeletedAndFailure(): void
    {
        $row = $this->PayjpUsers->get(1);
        foreach (['deleted', 'failure'] as $status) {
            $row->status = $status;
            $this->PayjpUsers->save($row);
            $this->assertNull(
                $this->PayjpUsers->find('currentByUser', userId: 1)->first(),
                "{$status} は除外",
            );
        }
    }

    public function testFindCurrentByUser_returnsLatestRow(): void
    {
        // user 1 に新しい行（pm あり）を追加 → 最新行が返る
        $new = $this->PayjpUsers->newEntity([
            'user_id' => 1,
            'status' => 'active',
            'type' => 'auto_charge',
            'auto_charge_amount' => 6000,
            'payjp_customer_code' => 'cus_new_100',
            'payjp_payment_method_code' => 'pm_new_100',
        ]);
        $this->PayjpUsers->save($new);

        $row = $this->PayjpUsers->find('currentByUser', userId: 1)->first();
        $this->assertSame($new->id, $row->id);
    }

    // ---- findExpiringInMonth ----

    /**
     * fixture 行に card_deadline を設定するヘルパー。
     */
    private function setCardDeadline(int $id, ?string $deadline): void
    {
        $row = $this->PayjpUsers->get($id);
        $row->card_deadline = $deadline === null ? null : Date::parse($deadline);
        $this->PayjpUsers->save($row);
    }

    public function testFindExpiringInMonth_includesDeadlineWithinMonth(): void
    {
        // user 1: active+pm。当該月の末日ちょうど → hit
        $this->setCardDeadline(1, '2027-03-31');

        $results = $this->PayjpUsers->find('expiringInMonth', month: Date::parse('2027-03-15'))->toArray();
        $this->assertCount(1, $results);
        $this->assertSame(1, $results[0]->id);
    }

    public function testFindExpiringInMonth_excludesPreviousAndNextMonth(): void
    {
        // 前月末日 / 翌月1日 は範囲外
        foreach (['2027-02-28', '2027-04-01'] as $deadline) {
            $this->setCardDeadline(1, $deadline);
            $results = $this->PayjpUsers->find('expiringInMonth', month: Date::parse('2027-03-15'))->toArray();
            $this->assertSame([], $results, "card_deadline={$deadline} は 2027-03 の対象外");
        }
    }

    public function testFindExpiringInMonth_excludesNullDeadline(): void
    {
        // fixture 全行 card_deadline NULL → 空
        $results = $this->PayjpUsers->find('expiringInMonth', month: Date::parse('2027-03-15'))->toArray();
        $this->assertSame([], $results);
    }

    public function testFindExpiringInMonth_excludesNonActiveOrNoPaymentMethod(): void
    {
        // user 2: active だが pm NULL / user 3: suspended / user 4: inactive → いずれも除外
        foreach ([2, 3, 4] as $id) {
            $this->setCardDeadline($id, '2027-03-31');
        }

        $results = $this->PayjpUsers->find('expiringInMonth', month: Date::parse('2027-03-15'))->toArray();
        $this->assertSame([], $results);
    }
    // ---- findSearch（管理画面の一覧検索） ----

    /**
     * キーワード検索が SQL エラーにならないこと。
     *
     * ひな型の `PayjpUsers.name LIKE` が残っており、payjp_users に name カラムが
     * 無いため管理画面が 500 になっていた（回帰防止）。
     */
    public function testFindSearchDoesNotReferenceMissingColumn(): void
    {
        $this->assertIsInt($this->PayjpUsers->find('search', keyword: 'anything')->count());
    }

    public function testFindSearchByCustomerCode(): void
    {
        $ids = $this->PayjpUsers->find('search', keyword: 'cus_test_1')->all()->extract('id')->toList();

        $this->assertNotEmpty($ids);
        foreach ($ids as $id) {
            $this->assertSame('cus_test_1', $this->PayjpUsers->get($id)->payjp_customer_code);
        }
    }

    public function testFindSearchByPaymentMethodCode(): void
    {
        $ids = $this->PayjpUsers->find('search', keyword: 'pm_test_1')->all()->extract('id')->toList();

        $this->assertNotEmpty($ids, 'PaymentMethod コードで引ける');
    }

    /**
     * 関連ユーザーの名前でも引ける（Users を JOIN している担保）。
     */
    public function testFindSearchByUserName(): void
    {
        $user = $this->getTableLocator()->get('Payjp.Users')->find()->firstOrFail();

        $ids = $this->PayjpUsers->find('search', keyword: $user->name)->all()->extract('id')->toList();

        $this->assertNotEmpty($ids);
    }

    public function testFindSearchWithoutKeywordReturnsAll(): void
    {
        $all = $this->PayjpUsers->find()->count();

        $this->assertSame($all, $this->PayjpUsers->find('search')->count());
    }

    public function testFindSearchWithNoMatchReturnsZero(): void
    {
        $this->assertSame(0, $this->PayjpUsers->find('search', keyword: 'zzz-not-found')->count());
    }

    public function testFindSearchById(): void
    {
        $this->assertSame(1, $this->PayjpUsers->find('search', id: '1')->count());
    }
}
