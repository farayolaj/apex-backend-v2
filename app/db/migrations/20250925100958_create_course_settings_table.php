<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCourseSettingsTable extends AbstractMigration
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
        $table = $this->table('course_settings');
        $table->addColumn('course_id', 'integer', [
            'null' => false,
            'comment' => 'Foreign key to courses table'
        ])
            ->addColumn('session_id', 'integer', [
                'null' => false,
                'comment' => 'Foreign key to session table'
            ])
            ->addColumn('overview', 'text', [
                'null' => true,
                'comment' => 'Course overview content'
            ])
            ->addColumn('mission', 'text', [
                'null' => true,
                'comment' => 'Course mission statement'
            ])
            ->addColumn('objectives', 'text', [
                'null' => true,
                'comment' => 'Course objectives'
            ])
            ->addColumn('course_guide_id', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => 'Google Drive file ID for course guide document'
            ])
            ->addTimestamps()
            ->addIndex(['course_id', 'session_id'], ['unique' => true])
            ->create();
    }
}
