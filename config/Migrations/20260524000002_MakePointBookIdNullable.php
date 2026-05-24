<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class MakePointBookIdNullable extends BaseMigration
{
    public function change(): void
    {
        $this->table('payjp_charges')
            ->changeColumn('point_book_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->update();
    }
}
