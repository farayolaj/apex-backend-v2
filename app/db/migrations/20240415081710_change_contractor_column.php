<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeContractorColumn extends AbstractMigration
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
		$table = $this->table('contractors');
		$table->renameColumn('address', 'description')->save();
		$table->changeColumn('description', 'text')->save();
	}

	public function down(): void
	{
		$table = $this->table('contractors');
		$table->renameColumn('description', 'address')->save();
		$table->changeColumn('address', 'string', ['limit' => 255])->save();
	}
}
