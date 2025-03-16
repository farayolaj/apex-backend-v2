<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CourseRequestClaims extends AbstractMigration
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
		$table = $this->table('course_request_claims');
		$table->addColumn('course_id', 'integer', ['null' => false])
			->addColumn('session_id', 'integer', ['null' => false])
			->addColumn('course_manager_id', 'integer', ['null' => false])
			->addColumn('exam_type', 'string', ['limit' => 100, 'default' => 'paper'])
			->addColumn('enrolled', 'integer', ['default' => 0])
			->addColumn('with_score', 'integer', ['default' => 0])
			->addColumn('user_request_id', 'integer', ['null' => false])
			->addColumn('with_score_amount', 'decimal', ['precision' => 10, 'scale' => 2])
			->addColumn('with_score_extra', 'integer', ['limit' => 11])
			->addColumn('with_score_extra_unit', 'integer', ['limit' => 11])
			->addColumn('with_score_extra_amount', 'decimal', ['precision' => 10, 'scale' => 2])
			->addColumn('total_amount', 'decimal', ['precision' => 10, 'scale' => 2])
			->addColumn('status', 'boolean', ['default' => 0])
			->addTimestamps()
			->create();
	}
}
