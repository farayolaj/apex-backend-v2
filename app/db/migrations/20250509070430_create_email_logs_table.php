<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEmailLogsTable extends AbstractMigration
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
		$table = $this->table('email_logs');
		$table->addColumn('type', 'string', ['limit' => 50])
			->addColumn('to_email', 'string', ['limit' => 150])
			->addColumn('subject', 'string', ['limit' => 100])
			->addColumn('message', 'text')
			->addColumn('attempts', 'integer', ['default' => 0])
			->addColumn('sent_at', 'timestamp', ['default' => null])
			->addTimestamps(null, false)
			->create();
	}
}
