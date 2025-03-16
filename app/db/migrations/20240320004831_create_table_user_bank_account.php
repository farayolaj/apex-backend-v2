<?php

declare (strict_types = 1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateTableUserBankAccount extends AbstractMigration {
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
		$table = $this->table('user_banks');
		$table->addColumn('users_id', 'integer', [
			'limit' => 11,
			'null' => false,
		])
			->addColumn('account_name', 'string', [
				'limit' => 150,
				'null' => false,
			])
			->addColumn('account_number', 'string', [
				'limit' => 20,
				'null' => false,
			])
			->addColumn('bank_lists_id', 'integer', [
				'limit' => 11,
				'null' => false,
			])
			->addColumn('is_primary', 'integer', [
				'limit' => MysqlAdapter::INT_TINY,
				'null' => false,
				'default' => 0,
			])
			->addTimestamps()
			->addIndex(['account_number'], ['unique' => true])
			->create();
	}
}
