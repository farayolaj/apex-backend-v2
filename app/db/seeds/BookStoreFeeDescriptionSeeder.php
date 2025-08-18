<?php


use Phinx\Seed\AbstractSeed;

class BookStoreFeeDescriptionSeeder extends AbstractSeed
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
				'description' => 'Online Book Store',
				'category' => '5',
				'code' => 'OBS',
				'active' => '1',
				'date_created' => date('Y-m-d H:i:s')
			]
		];
		$posts = $this->table('fee_description');
		$posts->insert($data)
			->saveData();
	}
}
