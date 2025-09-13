<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMatrixRoomsTable extends AbstractMigration
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
        $table = $this->table('matrix_rooms', ['id' => false]);
        $table
            ->addColumn('room_id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('room_type', 'enum', [
                'values' => ['course', 'general', 'department'],
                'null' => false
            ])
            ->addColumn('entity_id', 'integer', [
                'null' => true,
                'comment' => 'ID of the associated entity (e.g., course ID or department ID), null for general rooms'
            ])
            ->addTimestamps()
            ->addIndex(['room_id'], ['unique' => true])
            ->addIndex(['entity_id'])
            ->addIndex(['entity_id', 'room_type'], ['unique' => true])
            ->create();
    }
}
