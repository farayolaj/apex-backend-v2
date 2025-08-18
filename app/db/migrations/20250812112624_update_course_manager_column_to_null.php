<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateCourseManagerColumnToNull extends AbstractMigration
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
		$this->execute("
            UPDATE course_manager
            SET
              essential_inline_waiver    = NULL,
              webinar_excess_work_load   = NULL,
              physical_interaction       = NULL,
              data_allowance             = NULL
            
            WHERE
              essential_inline_waiver  IS NOT NULL OR
              webinar_excess_work_load IS NOT NULL OR
              physical_interaction     IS NOT NULL OR
              data_allowance           IS NOT NULL
        ");
	}

	public function down()
	{

	}
}
