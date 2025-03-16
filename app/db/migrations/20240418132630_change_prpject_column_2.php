<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangePrpjectColumn2 extends AbstractMigration
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
		$table = $this->table('projects');
		$table->changeColumn('project_status', 'enum',
			[
				'values' => [
					'pending',
					'completed'
				],
				'default' => 'pending',
			])
			->save();
	}

	public function down(): void
	{
		$table = $this->table('projects');
		$table->changeColumn('project_status', 'enum',
			[
				'values' => [
					'pending',
					'ongoing',
					'completed'
				],
				'default' => 'pending',
			])
			->save();
	}
}
