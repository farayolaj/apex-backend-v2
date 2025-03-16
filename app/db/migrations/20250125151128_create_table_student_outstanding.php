<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTableStudentOutstanding extends AbstractMigration
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
        $table = $this->table('student_outstanding');
        $table->addColumn('student_id', 'integer', ['limit' => 11])
            ->addColumn('outstanding_session', 'string', ['limit' => 255])
            ->addColumn('rem_outstanding_session', 'string', ['limit' => 255])
            ->addTimestamps()
            ->create();
    }
}
