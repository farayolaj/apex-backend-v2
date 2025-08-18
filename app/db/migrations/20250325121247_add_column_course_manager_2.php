<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddColumnCourseManager2 extends AbstractMigration
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
    public function up(): void
    {
        $users = $this->table('course_manager');
        $users->changeColumn('course_lecturer_id', 'string', ['limit' => 255])
            ->changeColumn('date_created', 'timestamp', ['default' => 'current_timestamp'])
            ->save();
    }

    public function down(){
        $users = $this->table('course_manager');
        $users->changeColumn('course_lecturer_id', 'string', ['limit' => 50])
            ->changeColumn('date_created', 'datetime')
            ->save();
    }
}
