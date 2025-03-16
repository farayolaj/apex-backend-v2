<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTableTransactionRequest extends AbstractMigration
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
		$table = $this->table('transaction_request');
		$table->addColumn('request_type_id', 'integer', ['null' => false])
			->addColumn('payment_description', 'string', ['limit' => 100, 'null' => false])
			->addColumn('user_id', 'integer', ['null' => false])
			->addColumn('session', 'integer', ['null' => false])
			->addColumn('source_account_name', 'string', ['limit' => 100, 'null' => false])
			->addColumn('source_account_number', 'string', ['limit' => 20, 'null' => false])
			->addColumn('source_bank_code', 'string', ['limit' => 15, 'null' => false])
			->addColumn('destination_account_name', 'string', ['limit' => 100, 'null' => false])
			->addColumn('destination_account_number', 'string', ['limit' => 20, 'null' => false])
			->addColumn('destination_bank_code', 'string', ['limit' => 15, 'null' => false])
			->addColumn('transaction_ref', 'string', ['limit' => 30, 'null' => false])
			->addColumn('rrr_code', 'string', ['limit' => 100, 'null' => true])
			->addColumn('batch_ref', 'string', ['limit' => 50, 'null' => true])
			->addColumn('payment_status', 'string', ['limit' => 3, 'null' => true])
			->addColumn('payment_status_description', 'string', ['limit' => 200, 'null' => true])
			->addColumn('payment_status_message', 'string', ['limit' => 200, 'null' => true])
			->addColumn('deduction', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
			->addColumn('withhold_tax', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
			->addColumn('vat', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
			->addColumn('stamp_duty', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
			->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2])
			->addColumn('total_amount', 'decimal', ['precision' => 10, 'scale' => 2])
			->addColumn('fee_amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true, 'comment' => 'remita fee'])
			->addColumn('date_paid', 'datetime', ['null' => true])
			->addTimestamps()
			->addIndex(['transaction_ref', 'rrr_code'], ['unique' => true])
			->addIndex(['payment_status', 'user_id'])
			->addIndex(['user_id', 'rrr_code'])
			->create();
	}
}
