<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateTableRequestCharges extends AbstractMigration
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
		$table = $this->table('request_charges');
		$table->addColumn('name', 'string', ['limit' => 100])
			->addColumn('slug', 'string', ['limit' => 50, 'null' => true])
			->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 4])
			->addColumn('active', 'integer', [
				'limit' => MysqlAdapter::INT_TINY,
				'null' => false,
				'default' => 1
			])
			->addTimestamps()
			->addIndex(['name', 'slug'], ['unique' => true])
			->create();
	}
}
