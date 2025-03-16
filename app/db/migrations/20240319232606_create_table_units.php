<?php

declare (strict_types = 1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateTableUnits extends AbstractMigration {
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
		$table = $this->table('units');
		$table->addColumn('name', 'string', ['limit' => 50])
			->addColumn('slug', 'string', ['limit' => 10])
			->addColumn('status', 'integer', ['limit' => MysqlAdapter::INT_TINY])
			->addTimestamps()
			->create();

	}
}
