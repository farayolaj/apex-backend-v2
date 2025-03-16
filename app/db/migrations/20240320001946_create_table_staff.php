<?php

declare (strict_types = 1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateTableStaff extends AbstractMigration {
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
	public function change(): void {
		$table = $this->table('staffs');
		$table->addColumn('title', 'string', [
			'limit' => 20,
			'null' => false,
		])
			->addColumn('staff_id', 'string', [
				'limit' => 20,
				'null' => false,
			])
			->addColumn('firstname', 'string', [
				'limit' => 50,
				'null' => false,
			])
			->addColumn('lastname', 'string', [
				'limit' => 50,
				'null' => false,
			])
			->addColumn('othernames', 'string', [
				'limit' => 50,
				'null' => false,
			])
			->addColumn('gender', 'enum', [
				'values' => [
					'Male',
					'Female',
					'Others',
				],
				'default' => 'Others',
			])
			->addColumn('dob', 'string', [
				'limit' => 10,
				'null' => false,
			])
			->addColumn('marital_status', 'string', [
				'limit' => 10,
				'null' => false,
				'default' => 'Single',
			])
			->addColumn('phone_number', 'string', [
				'limit' => 15,
				'null' => false,
			])
			->addColumn('email', 'string', [
				'limit' => 100,
				'null' => false,
			])
			->addColumn('units_id', 'integer', [
				'limit' => 11,
				'null' => true,
				'comment' => 'equiv user_unit',
			])
			->addColumn('rank', 'string', [
				'limit' => 50,
				'null' => false,
				'comment' => 'equiv user_rank',
			])
			->addColumn('role', 'string', [
				'limit' => 50,
				'null' => false,
				'comment' => 'equiv user_role',
			])
			->addColumn('avatar', 'string', [
				'limit' => 150,
				'null' => false,
			])
			->addColumn('address', 'string', [
				'limit' => 225,
				'null' => false,
			])
			->addColumn('active', 'integer', [
				'limit' => MysqlAdapter::INT_TINY,
				'default' => 1,
			])
			->addTimestamps()
			->create();
	}
}
