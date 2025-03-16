<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTableUserRequestLog extends AbstractMigration
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
		$table = $this->table('user_requests_log');
		$table->addColumn('username', 'string', ['limit' => 100])
			->addColumn('operation', 'text')
			->addColumn('user_id', 'integer')
			->addColumn('request_no', 'string', ['limit' => 100])
			->addColumn('request_id', 'integer')
			->addColumn('beneficiaries', 'text')
			->addColumn('charges', 'text', ['comment' => 'all charges'])
			->addColumn('total_amount', 'decimal', ['precision' => 10, 'scale' => 4])
			->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
			->create();
	}
}
