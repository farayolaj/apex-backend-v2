<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateTableRequestTypeCharges extends AbstractMigration
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
		$table = $this->table('request_type_charges');
		$table->addColumn('request_type_id', 'integer')
			->addColumn('request_charge_id', 'integer')
			->addColumn('is_editable', 'boolean', ['default' => true])
			->addColumn('active', 'integer', [
				'limit' => MysqlAdapter::INT_TINY,
				'null' => false,
				'default' => 1
			])
			->addTimestamps()
			->create();
	}
}
