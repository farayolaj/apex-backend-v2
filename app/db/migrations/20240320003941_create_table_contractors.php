<?php

declare (strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateTableContractors extends AbstractMigration
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
		$table = $this->table('contractors');
		$table->addColumn('registered_name', 'string', [
			'limit' => 255,
			'null' => false,
		])
			->addColumn('cac_number', 'string', [
				'limit' => 20,
				'null' => false,
			])
			->addColumn('tin_number', 'string', [
				'limit' => 20,
				'null' => false,
			])
			->addColumn('cac_certificate', 'string', [
				'limit' => 210,
				'null' => false,
			])
			->addColumn('address', 'string', [
				'limit' => 255,
				'null' => true,
			])
			->addColumn('email', 'string', [
				'limit' => 100,
				'null' => true,
			])
			->addColumn('phone_number', 'string', [
				'limit' => 20,
				'null' => true,
			])
			->addColumn('active', 'integer', [
				'limit' => MysqlAdapter::INT_TINY,
				'default' => 1,
			])
			->addTimestamps()
			->create();
	}
}
