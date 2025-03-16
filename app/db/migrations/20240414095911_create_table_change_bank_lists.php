<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTableChangeBankLists extends AbstractMigration
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
		$table = $this->table('bank_lists');
		$column = $table->hasColumn('code');

		if ($column) {
			$table->changeColumn('code', 'string', ['limit' => 20])->save();
		}
	}

	public function down(): void
	{
		$table = $this->table('bank_lists');
		$column = $table->hasColumn('code');

		if ($column) {
			$table->changeColumn('code', 'integer')->save();
		}
	}
}
