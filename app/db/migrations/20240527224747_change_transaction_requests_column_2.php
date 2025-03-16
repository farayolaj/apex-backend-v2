<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeTransactionRequestsColumn2 extends AbstractMigration
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
		$table = $this->table('transaction_request');
		$table->changeColumn('payment_status_description', 'string', [
				'default' => 'PENDING_START', 'limit' => 50]
		)->save();
	}

	public function down()
	{
		$table = $this->table('transaction_request');
		$table->changeColumn('payment_status_description', 'string', [
				'default' => 'PENDING', 'limit' => 200]
		)->save();
	}
}
