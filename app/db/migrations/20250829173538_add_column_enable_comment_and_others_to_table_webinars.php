<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddColumnEnableCommentAndOthersToTableWebinars extends AbstractMigration
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
        $this->table('webinars')
            ->addColumn('enable_comments', 'boolean', ['default' => true])
            ->addColumn('send_notifications', 'boolean', ['default' => true])
            ->addColumn('join_count', 'integer', ['default' => 0])
            ->addColumn('playback_count', 'integer', ['default' => 0])
            ->update();
    }
}
