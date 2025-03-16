<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCourseRequestClaimItems extends AbstractMigration
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
		$table = $this->table('course_request_claim_items');
		$table->addColumn('course_request_claim_id', 'integer', ['null' => false])
			->addColumn('with_score_amount', 'decimal', ['precision' => 10, 'scale' => 2])
			->addColumn('with_score_extra', 'integer', ['limit' => 11])
			->addColumn('with_score_extra_unit', 'integer', ['limit' => 11])
			->addColumn('with_score_extra_amount', 'decimal', ['precision' => 10, 'scale' => 2])
			->addColumn('claim_type', 'string', ['limit' => 50, 'null' => true, 'default' => 'script'])
			->addTimestamps()
			->create();
	}
}
