<?php

declare (strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DropColumnUsers extends AbstractMigration
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
	private function duplicateUserTable()
	{
		$this->execute("
		CREATE TABLE users_new LIKE users;
            INSERT INTO users_new SELECT * FROM users;
		");
	}

	public function up(): void
	{
		$this->duplicateUserTable();
		if ($this->hasTable('users_new')) {
			$table = $this->table('users_new');
			$table->removeColumn('title')
				->removeColumn('staff_id')
				->removeColumn('firstname')
				->removeColumn('othernames')
				->removeColumn('lastname')
				->removeColumn('gender')
				->removeColumn('dob')
				->removeColumn('marital_status')
				->removeColumn('user_phone')
				->removeColumn('user_email')
				->removeColumn('user_unit')
				->removeColumn('user_rank')
				->removeColumn('user_role')
				->removeColumn('is_lecturer')
				->removeColumn('avatar')
				->removeColumn('address')
				->save();
		}

	}

	public function down(): void
	{
		$this->table('users_new')->drop()->save();
	}
}
