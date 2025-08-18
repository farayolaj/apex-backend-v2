<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class EmailLogMeta extends AbstractMigration
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
		$table = $this->table('email_batches');
		$table->addColumn('email_ref', 'string', ['limit' => 50, 'null' => false])
			->addColumn('type', 'string', ['limit' => 50])
			->addColumn('subject', 'string', ['limit' => 150])
			->addColumn('message', 'text')
			->addColumn('query', 'text', ['null' => true])
			->addTimestamps()
			->create();
	}
}
