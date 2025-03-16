<?php

declare (strict_types = 1);

use Phinx\Migration\AbstractMigration;

final class ApplicantTransferLogs extends AbstractMigration {
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
	public function change(): void {
		$table = $this->table('applicant_transfer_logs');
		$table->addColumn('host', 'string', ['limit' => 150])
			->addColumn('url', 'string', ['limit' => 150])
			->addColumn('user_agent', 'string', ['limit' => 150])
			->addColumn('ip_address', 'string', ['limit' => 50])
			->addColumn('request', 'text', ['null' => true])
			->addColumn('response', 'text', ['null' => true])
			->addColumn('created_at', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
			->create();
	}
}
