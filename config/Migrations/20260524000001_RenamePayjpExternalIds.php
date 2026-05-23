<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class RenamePayjpExternalIds extends BaseMigration
{
    public function up(): void
    {
        $this->table('payjp_charges')
            ->renameColumn('payjp_charge_id', 'payjp_charge_code')
            ->renameColumn('payjp_customer_id', 'payjp_customer_code')
            ->update();

        $this->table('payjp_users')
            ->renameColumn('payjp_customer_id', 'payjp_customer_code')
            ->update();
    }

    public function down(): void
    {
        $this->table('payjp_charges')
            ->renameColumn('payjp_charge_code', 'payjp_charge_id')
            ->renameColumn('payjp_customer_code', 'payjp_customer_id')
            ->update();

        $this->table('payjp_users')
            ->renameColumn('payjp_customer_code', 'payjp_customer_id')
            ->update();
    }
}
