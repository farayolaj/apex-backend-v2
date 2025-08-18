<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddColumnCourseManagerExamtype extends AbstractMigration
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
		// check if column already exists
		if (!$table->hasColumn('exam_type')) {
			$table->addColumn('exam_type', 'string', ['null' => true])
				->addColumn('logistics_allowance', 'string', ['null' => true])
				->update();
		}
	}
}
