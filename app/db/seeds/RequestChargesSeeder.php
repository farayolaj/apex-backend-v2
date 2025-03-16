<?php


use Phinx\Seed\AbstractSeed;

class RequestChargesSeeder extends AbstractSeed
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
				'name' => 'Withholding Tax',
				'slug' => 'WHT',
				'amount' => 0.05
			],
			[
				'name' => 'Value Added Tax',
				'slug' => 'VAT',
				'amount' => 0.075
			],
			[
				'name' => 'Stamp Duty',
				'slug' => 'STD',
				'amount' => 0.01
			],
			[
				'name' => 'Deduction Fee',
				'slug' => 'DEF',
				'amount' => 0.1
			]
		];

		$posts = $this->table('request_charges');
		$posts->insert($data)
			->saveData();
	}
}
