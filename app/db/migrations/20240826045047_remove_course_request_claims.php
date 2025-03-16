<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveCourseRequestClaims extends AbstractMigration
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
		$table = $this->table('course_request_claims');
		$table->removeColumn('with_score_amount')
			->removeColumn('with_score_extra')
			->removeColumn('with_score_extra_unit')
			->removeColumn('with_score_extra_amount')
			->removeColumn('claim_type')
			->removeColumn('interact_with_student')
			->removeColumn('interact_with_amount')
			->removeColumn('facilitation')
			->removeColumn('facilitation_amount')
			->save();
	}

	public function down()
	{
		$table = $this->table('course_request_claims');
		$table->addColumn('with_score_amount', 'decimal', ['precision' => 10, 'scale' => 2])
			->addColumn('with_score_extra', 'integer', ['limit' => 11])
			->addColumn('with_score_extra_unit', 'integer', ['limit' => 11])
			->addColumn('with_score_extra_amount', 'decimal', ['precision' => 10, 'scale' => 2])
			->addColumn('claim_type', 'string', ['limit' => 50, 'null' => true, 'default' => 'script'])
			->addColumn('interact_with_student', 'integer', ['limit' => 11])
			->addColumn('interact_with_amount', 'decimal', ['precision' => 10, 'scale' => 2])
			->addColumn('facilitation', 'integer', ['limit' => 11])
			->addColumn('facilitation_amount', 'decimal', ['precision' => 10, 'scale' => 2])
			->update();
	}
}
