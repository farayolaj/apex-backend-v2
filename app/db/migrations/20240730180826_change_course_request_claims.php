<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeCourseRequestClaims extends AbstractMigration
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
		$table->addColumn('interact_with_student', 'boolean', ['default' => false])
			->addColumn('facilitation', 'boolean', ['default' => false, 'comment' => 'online'])
			->addColumn('interact_with_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
			->addColumn('facilitation_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0.00])
			->update();
	}
}
