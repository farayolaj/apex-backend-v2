<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddColumnExternalIdToMatrixRoomsTable extends AbstractMigration
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
        $this->table('matrix_rooms')
            ->addColumn('external_id', 'string', ['limit' => 255, 'null' => true, 'after' => 'room_id', 'comment' => 'External identifier for the room, if applicable'])
            ->addIndex(['external_id'], ['unique' => true, 'name' => 'idx_unique_external_id'])
            ->update();
    }
}
