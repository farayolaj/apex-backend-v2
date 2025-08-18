<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTableStudentPaymentBookstoreItems extends AbstractMigration
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
		$table = $this->table('student_payment_bookstore_items');
		$table->addColumn('student_payment_bookstore_id', 'integer', [
			'limit' => 11,
			'null' => false,
		])
			->addColumn('payment_bookstore_id', 'integer', [
				'limit' => 11,
				'null' => false,
			])
			->addColumn('title', 'string', [
				'limit' => 255,
				'null' => false,
				'comment' => 'code:title',
			])
			->addColumn('course_id', 'integer', [
				'limit' => 11,
				'null' => true,
			])
			->addColumn('quantity', 'integer', [
				'limit' => 11,
				'default' => 0,
				'null' => false,
			])
			->addColumn('amount', 'decimal', [
				'precision' => 10,
				'scale' => 2,
			])
			->addColumn('payment_id', 'integer', [
				'limit' => 11,
				'null' => false,
				'comment' => 'fee_description'
			])
			->addColumn('mainaccount_amount', 'decimal', [
				'precision' => 10,
				'scale' => 2,
			])
			->addColumn('subaccount_amount', 'decimal', [
				'precision' => 10,
				'scale' => 2,
			])
			->addColumn('created_at', 'timestamp', [
				'default' => 'CURRENT_TIMESTAMP',
			])
			->create();
	}
}
