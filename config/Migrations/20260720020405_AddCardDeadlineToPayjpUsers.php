<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddCardDeadlineToPayjpUsers extends BaseMigration
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
        $table->addColumn('card_deadline', 'date', [
            'default' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
