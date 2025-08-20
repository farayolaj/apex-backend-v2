<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

use App\Enums\CommonEnum as CommonSlug;

final class UpdateApplicantPutmeOlevel extends AbstractMigration
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
	public function up(): void
	{
		// update applicant_post_utme entry_mode to applicant_putme_olevel
		$olevel = CommonSlug::O_LEVEL->value;
		$newLevel = CommonSlug::O_LEVEL_PUTME->value;
		$this->execute("UPDATE applicant_post_utme SET entry_mode = \"$newLevel\" WHERE entry_mode = \"$olevel\" ");

		// update those already admitted from applicant_post_utme into academic_record mapping them to the new entry_mode
		$this->execute("UPDATE academic_record, applicant_post_utme SET academic_record.entry_mode = \"$newLevel\" where 
        academic_record.application_number = applicant_post_utme.applicant_id and applicant_post_utme.is_admitted = '1' ");
	}

	public function down(): void
	{
		// update applicant_putme_olevel entry_mode to applicant_post_utme
		$olevel = CommonSlug::O_LEVEL;
		$newLevel = CommonSlug::O_LEVEL_PUTME;
		$this->execute("UPDATE applicant_post_utme SET entry_mode = \"$olevel\" WHERE entry_mode = \"$newLevel\" ");

		// update those already admitted from applicant_post_utme into academic_record mapping them to the new entry_mode
		$this->execute("UPDATE academic_record, applicant_post_utme SET academic_record.entry_mode = \"$olevel\" where 
		academic_record.application_number = applicant_post_utme.applicant_id and applicant_post_utme.is_admitted = '1' ");
	}
}
