<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTableUserRequestsArchive extends AbstractMigration
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

	private function duplicateUserRequestsTable()
	{
		$this->execute("DROP TABLE IF EXISTS user_requests_archive");
		$this->execute("
			CREATE TABLE user_requests_archive LIKE user_requests;
		");
	}

	public function up(): void
	{

		$this->duplicateUserRequestsTable();
		$table = $this->table('user_requests_archive');
		$table->addColumn('new_request_id', 'integer')
			->update();
	}

	public function down()
	{
		// Drop the recreated table
		$this->execute("DROP TABLE IF EXISTS user_requests_archive");
	}
}
