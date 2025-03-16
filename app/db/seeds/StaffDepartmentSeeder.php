<?php


use Phinx\Seed\AbstractSeed;

class StaffDepartmentSeeder extends AbstractSeed
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
				'name' => 'Directorate',
				'slug' => 'DIR',
				'code' => 'DIR',
				'active' => 1,
				'type' => 'non-academic',
				'faculty_id' => 0
			],
			[
				'name' => 'Course Materials Development',
				'slug' => 'CMD',
				'code' => 'CMD',
				'active' => 1,
				'type' => 'non-academic',
				'faculty_id' => 0
			],
			[
				'name' => 'Procurement',
				'slug' => 'PRO',
				'code' => 'PRO',
				'active' => 1,
				'type' => 'non-academic',
				'faculty_id' => 0
			],
			[
				'name' => 'Security',
				'slug' => 'SEC',
				'code' => 'SEC',
				'active' => 1,
				'type' => 'non-academic',
				'faculty_id' => 0
			],
			[
				'name' => 'Maintenance',
				'slug' => 'MAI',
				'code' => 'MAI',
				'active' => 1,
				'type' => 'non-academic',
				'faculty_id' => 0
			],
			[
				'name' => 'Admissions',
				'slug' => 'ADM',
				'code' => 'ADM',
				'active' => 1,
				'type' => 'non-academic',
				'faculty_id' => 0
			],
			[
				'name' => 'Communications & Media',
				'slug' => 'COM',
				'code' => 'COM',
				'active' => 1,
				'type' => 'non-academic',
				'faculty_id' => 0
			],
			[
				'name' => 'Examinations',
				'slug' => 'EXA',
				'code' => 'EXA',
				'active' => 1,
				'type' => 'non-academic',
				'faculty_id' => 0
			],
			[
				'name' => 'ICT',
				'slug' => 'ICT',
				'code' => 'ICT',
				'active' => 1,
				'type' => 'non-academic',
				'faculty_id' => 0
			],
			[
				'name' => 'Learner Support',
				'slug' => 'LES',
				'code' => 'LES',
				'active' => 1,
				'type' => 'non-academic',
				'faculty_id' => 0
			],
			[
				'name' => 'Programmes',
				'slug' => 'PRG',
				'code' => 'PRG',
				'active' => 1,
				'type' => 'non-academic',
				'faculty_id' => 0
			],
			[
				'name' => 'Records',
				'slug' => 'REC',
				'code' => 'REC',
				'active' => 1,
				'type' => 'non-academic',
				'faculty_id' => 0
			],
			[
				'name' => 'Finance',
				'slug' => 'FIN',
				'code' => 'FIN',
				'active' => 1,
				'type' => 'non-academic',
				'faculty_id' => 0
			],
		];

		$department = $this->table('department');

		$department->insert($data)
			->saveData();
	}
}
