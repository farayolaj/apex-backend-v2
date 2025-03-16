<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserRequestColumn9 extends AbstractMigration
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
		$table = $this->table('user_requests');
		$table->changeColumn('request_status', 'enum', [
			'values' => [
				'pending',
				'rejected',
				'approved',
				'paid',
				'advance_return_pending',
				'advance_return_confirmed'
			],
			'default' => 'pending',
		])
			->save();
	}

	public function down()
	{
		$table = $this->table('user_requests');
		$table->changeColumn('request_status', 'enum', [
			'values' => [
				'pending',
				'rejected',
				'approved',
				'paid'
			],
			'default' => 'pending',
		])
			->save();
	}
}
