<?php


use Phinx\Seed\AbstractSeed;

class FacultySeeder extends AbstractSeed
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
		// create the data for faculty named Faculty of Service Department
		$data = [
			[
				'name' => 'Faculty of Service Department',
				'slug' => 'f_se',
				'active' => '1',
				'date_created' => date('Y-m-d H:i:s')
			]
		];
		$posts = $this->table('faculty');
		$posts->insert($data)
			->saveData();
		// get the inserted ID
		$lastInsertId = $this->getAdapter()->getConnection()->lastInsertId();
		$data = [
			[
				'faculty_id' => $lastInsertId,
				'name' => 'Social Science Service Department',
				'slug' => 'SOD',
				'code' => 'SOD',
				'active' => 1,
				'date_created' => date('Y-m-d H:i:s'),
				'type' => 'academic'
			],
			[
				'faculty_id' => $lastInsertId,
				'name' => 'Art Service Department',
				'slug' => 'ARD',
				'code' => 'ARD',
				'active' => 1,
				'date_created' => date('Y-m-d H:i:s'),
				'type' => 'academic'
			],
			[
				'faculty_id' => $lastInsertId,
				'name' => 'Education Service department',
				'slug' => 'EDD',
				'code' => 'EDD',
				'active' => 1,
				'date_created' => date('Y-m-d H:i:s'),
				'type' => 'academic'
			],
			[
				'faculty_id' => $lastInsertId,
				'name' => 'Science Service Department',
				'slug' => 'SCD',
				'code' => 'SCD',
				'active' => 1,
				'date_created' => date('Y-m-d H:i:s'),
				'type' => 'academic'
			],
			[
				'faculty_id' => $lastInsertId,
				'name' => 'Agriculture Service Department',
				'slug' => 'AGD',
				'code' => 'AGD',
				'active' => 1,
				'date_created' => date('Y-m-d H:i:s'),
				'type' => 'academic'
			]
		];
		$posts = $this->table('department');
		$posts->insert($data)
			->saveData();

	}
}
