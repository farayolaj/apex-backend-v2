<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveRolesAddUserColumn extends AbstractMigration
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
		$table = $this->table('roles');
		$table->removeColumn('slug')
			->save();

		$table = $this->table('staffs');
		$table->addColumn('outflow_slug', 'string', ['limit' => 50])
			->update();
	}

	public function down()
	{
		$table = $this->table('roles');
		$table->addColumn('slug', 'string', ['limit' => 50])
			->update();

		$table = $this->table('staffs');
		$table->removeColumn('outflow_slug')
			->save();
	}
}
