<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTableStudentPaymentBookstore extends AbstractMigration
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
		$table = $this->table('student_payment_bookstore');
		$table->addColumn('student_id', 'integer', [
			'limit' => 11,
			'null' => false,
		])
			->addColumn('session', 'integer', [
				'limit' => 11,
				'null' => false,
			])
			->addColumn('order_id', 'string', [
				'limit' => 30,
				'null' => false,
			])
			->addColumn('total_amount', 'decimal', [
				'precision' => 10,
				'scale' => 2,
			])
			->addColumn('service_charge', 'decimal', [
				'precision' => 10,
				'scale' => 2,
			])
			->addColumn('transaction_ref', 'string', [
				'limit' => 30,
				'null' => false,
			])
			->addColumn('reserved_period', 'timestamp', [
				'null' => true,
			])
			->addColumn('book_status', 'string', [
				'limit' => 20,
				'default' => 'pending',
			])
			->addColumn('active', 'tinyinteger', [
				'limit' => 1,
				'default' => 1,
			])
			->addColumn('created_at', 'timestamp', [
				'default' => 'CURRENT_TIMESTAMP',
			])
			->addColumn('updated_at', 'timestamp', [
				'default' => 'CURRENT_TIMESTAMP',
				'update' => 'CURRENT_TIMESTAMP',
			])
			->addIndex(['order_id'], [
				'unique' => true,
			])
			->addIndex(['transaction_ref'], [
				'unique' => true,
			])
			->create();
	}
}
