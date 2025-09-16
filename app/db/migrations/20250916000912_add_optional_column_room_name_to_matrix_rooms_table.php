<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddOptionalColumnRoomNameToMatrixRoomsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('matrix_rooms');
        if (!$table->hasColumn('room_name')) {
            $table->addColumn('room_name', 'string', ['null' => true, 'length' => 255, 'after' => 'room_id'])
                ->update();
        }
    }
}
