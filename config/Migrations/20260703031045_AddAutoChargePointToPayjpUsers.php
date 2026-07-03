<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddAutoChargePointToPayjpUsers extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('payjp_users');
        $table->addColumn('auto_charge_point', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => true,
        ]);
        $table->update();
    }
}
