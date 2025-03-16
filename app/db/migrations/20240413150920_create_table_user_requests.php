<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTableUserRequests extends AbstractMigration
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
		$table = $this->table('user_requests');
		$table->addColumn('request_no', 'string', ['limit' => 50])
			->addColumn('title', 'string', ['limit' => '255'])
			->addColumn('user_id', 'integer')
			->addColumn('request_id', 'integer', ['comment' => 'request_type'])
			->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2])
			->addColumn('description', 'text')
			->addColumn('beneficiaries', 'text', ['comment' => 'multiple beneficiaries'])
			->addColumn('deduction', 'decimal', ['precision' => 10, 'scale' => 2])
			->addColumn('withhold_tax', 'decimal', ['precision' => 10, 'scale' => 2])
			->addColumn('vat', 'decimal', ['precision' => 10, 'scale' => 4])
			->addColumn('stamp_duty', 'decimal', ['precision' => 10, 'scale' => 2])
			->addColumn('total_amount', 'decimal', ['precision' => 10, 'scale' => 2])
			->addColumn('request_status', 'enum', [
				'values' => [
					'pending',
					'rejected',
					'approved',
					'paid'
				],
				'default' => 'pending',
			])
			->addColumn('project_task_id', 'integer', ['null' => true])
			->addColumn('feedback', 'text', ['null' => true])
			->addColumn('date_approved', 'timestamp', ['null' => true])
			->addTimestamps()
			->create();
	}
}
