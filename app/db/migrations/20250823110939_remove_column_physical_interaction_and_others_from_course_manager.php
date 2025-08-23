<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveColumnPhysicalInteractionAndOthersFromCourseManager extends AbstractMigration
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
        $table = $this->table('course_manager');
        $table->removeColumn('physical_interaction')
        ->removeColumn('data_allowance')
        ->removeColumn('webinar_excess_work_load')
        ->removeColumn('exam_type')
        ->removeColumn('logistics_allowance')
        ->removeColumn('writing_course_material')
        ->removeColumn('review_course_material')
        ->removeColumn('essential_inline_waiver')
              ->save();
    }
}
