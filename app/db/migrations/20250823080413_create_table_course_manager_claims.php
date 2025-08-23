<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateTableCourseManagerClaims extends AbstractMigration
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
		$table = $this->table('course_manager_claims');
		$table->addColumn('course_manager_id', 'integer')
			->addColumn('session_id', 'integer')
			->addColumn('user_id', 'integer')
			->addColumn('course_id', 'integer')
			->addColumn('physical_interaction', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'default' => false, 'null' => true])
			->addColumn('data_allowance', 'string', ['limit' => 100, 'null' => true])
			->addColumn('webinar_excess_work_load', 'string', ['limit' => 100, 'null' => true])
			->addColumn('exam_type', 'string', ['limit' => 100, 'null' => true])
			->addColumn('logistics_allowance', 'string', ['limit' => 100, 'null' => true])
			->addColumn('writing_course_material', 'string', ['limit' => 100, 'null' => true])
			->addColumn('review_course_material', 'string', ['limit' => 100, 'null' => true])
			->addColumn('essential_inline_waiver', 'text', ['null' => true])
			->addColumn('active', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'default' => true])
			->addTimestamps()
			->create();
	}
}
