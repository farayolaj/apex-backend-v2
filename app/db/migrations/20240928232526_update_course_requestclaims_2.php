<?php

declare (strict_types = 1);

use Phinx\Migration\AbstractMigration;

final class UpdateCourseRequestclaims2 extends AbstractMigration {
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
	public function up(): void {

		$this->execute("UPDATE course_request_claims set exam_type = 'written' where exam_type = 'paper';");
	}

	public function down() {
		$this->execute("UPDATE course_request_claims set exam_type = 'paper' where exam_type = 'written';");
	}
}
