<?php

declare (strict_types = 1);

use Phinx\Migration\AbstractMigration;

final class CreateApplicantsPostUtme extends AbstractMigration {
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
		$this->execute("DROP TABLE IF EXISTS applicant_post_utme");
		$this->execute("
            CREATE TABLE applicant_post_utme LIKE applicants;
        ");
	}

	public function down() {
		$this->execute("DROP TABLE IF EXISTS applicant_post_utme");
	}
}
