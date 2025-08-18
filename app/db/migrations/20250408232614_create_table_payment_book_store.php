<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTablePaymentBookStore extends AbstractMigration
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
		$table = $this->table('payment_bookstore');
		$table->addColumn('course_id', 'integer', [
			'limit' => 11,
			'null' => true,
		])
			->addColumn('title', 'string', [
				'limit' => 255,
				'null' => false,
			])
			->addColumn('authors', 'text', [
				'null' => true,
			])
			->addColumn('price', 'decimal', [
				'precision' => 10,
				'scale' => 2,
				'null' => false,
			])
			->addColumn('quantity', 'integer', [
				'limit' => 11,
				'null' => false,
			])
			->addColumn('fee_description', 'string', [
				'limit' => 20,
				'null' => false,
			])
			->addColumn('service_type_id', 'string', [
				'limit' => 20,
				'null' => false,
			])
			->addColumn('amount', 'string', [
				'limit' => 15,
				'null' => false,
			])
			->addColumn('subaccount_amount', 'string', [
				'limit' => 15,
				'null' => false,
			])
			->addColumn('service_charge', 'integer', [
				'limit' => 11,
				'null' => false,
			])
			->addColumn('session', 'integer', [
				'limit' => 11,
				'null' => true,
			])
			->addColumn('is_visible', 'tinyinteger', [
				'limit' => 1,
				'default' => 1,
				'null' => false,
			])
			->addColumn('created_at', 'timestamp', [
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
			])
			->addColumn('updated_at', 'timestamp', [
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'update' => 'CURRENT_TIMESTAMP',
			])
			->create();

	}
}
