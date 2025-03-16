<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeUserRequestsColumn3 extends AbstractMigration
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
		$table->changeColumn('stage', 'enum', [
			'values' => [
				'payment_voucher',
				'auditor',
				'mandate',
				'payment',
				'retire_salary_advance',
			],
			'null' => 'payment_voucher',
		])
			->save();
	}

	public function down()
	{
		$table = $this->table('user_requests');
		$table->changeColumn('stage', 'enum', [
			'values' => [
				'payment_voucher',
				'auditor',
				'mandate',
				'payment',
				'retire_salary_advance_pending',
				'retire_salary_advance_complete',
			],
			'null' => 'payment_voucher',
		])
			->save();
	}
}
