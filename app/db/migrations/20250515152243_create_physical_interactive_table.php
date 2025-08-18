<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePhysicalInteractiveTable extends AbstractMigration
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
		$table = $this->table('physical_interactive_event');
		$table->addColumn('course_id', 'integer')
			->addColumn('user_id', 'integer')
			->addColumn('session_id', 'integer')
			->addColumn('event_date', 'date')
			->addColumn('start_time', 'string', ['limit' => 10])
			->addColumn('end_time', 'string', ['limit' => 10])
			->addColumn('venue', 'string', ['limit' => 100])
			->addColumn('remark', 'text')
			->addTimestamps()
			->create();
	}
}
