<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddColumnCourseManager3 extends AbstractMigration
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
		$table->addColumn('course_question_tutor', 'integer', [
			'limit' => 11,
			'null' => true,
		])
			->addColumn('course_e_tutor', 'integer', [
				'limit' => 11,
				'null' => true,
			])
			->addColumn('data_allowance', 'boolean', ['default' => false])
			->addColumn('physical_interaction', 'boolean', [
				'default' => false,
			])->update();

	}
}
