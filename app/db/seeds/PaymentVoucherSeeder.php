<?php


use Phinx\Seed\AbstractSeed;

class PaymentVoucherSeeder extends AbstractSeed
{
	/**
	 * Run Method.
	 *
	 * Write your database seeder using this method.
	 *
	 * More information on writing seeders is available here:
	 * https://book.cakephp.org/phinx/0/en/seeding.html
	 */
	public function run(): void
	{
		$data = [
			[
				'name' => 'Salary Advance',
				'slug' => 'SAD',
				'type' => 'staff',
				'is_auditable' => 0
			],
			[
				'name' => 'Imprest',
				'slug' => 'IMP',
				'type' => 'staff',
				'is_auditable' => 1
			],
			[
				'name' => 'Claim',
				'slug' => 'CLA',
				'type' => 'staff',
				'is_auditable' => 1
			],
			[
				'name' => 'Honorarium',
				'slug' => 'HON',
				'type' => 'staff',
				'is_auditable' => 1
			],
			[
				'name' => 'Retire Salary Advance',
				'slug' => 'RSA',
				'type' => 'other',
				'is_auditable' => 1
			],
			[
				'name' => 'Invoice',
				'slug' => 'INV',
				'type' => 'contractor',
				'is_auditable' => 1
			],
			[
				'name' => 'Diem',
				'slug' => 'DIE',
				'type' => 'staff',
				'is_auditable' => 1
			],
		];

		$posts = $this->table('request_type');
		$posts->insert($data)
			->saveData();
	}
}
