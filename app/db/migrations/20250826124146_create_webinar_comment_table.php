<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateWebinarCommentTable extends AbstractMigration
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
        $table = $this->table('webinar_comments');
        $table->addColumn('webinar_id', 'integer')
            ->addColumn('content', 'string', ['limit' => 255])
            ->addColumn('author_id', 'integer')
            ->addColumn('author_table', 'enum', ['values' => ['students', 'staffs']])
            ->addTimestamps()
            ->addIndex(['webinar_id'])
            ->addIndex(['created_at'], [
                'order' => ['created_at' => 'DESC']
            ])
            ->addIndex(['webinar_id', 'created_at'], [
                'order' => ['webinar_id' => 'ASC', 'created_at' => 'DESC']
            ])
            ->create();
    }
}
