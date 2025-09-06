<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateNotificationsTable extends AbstractMigration
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
        $table = $this->table('notifications');
        $table->addColumn('recipient_table', 'string', ['limit' => 50])
            ->addColumn('recipient_id', 'integer')
            ->addColumn('type', 'string', ['limit' => 100])
            ->addColumn('data', 'text')
            ->addColumn('is_read', 'boolean', ['default' => false])
            ->addIndex(['recipient_table', 'recipient_id'], ['name' => 'idx_recipient'])
            ->addTimestamps()
            ->create();
    }
}
