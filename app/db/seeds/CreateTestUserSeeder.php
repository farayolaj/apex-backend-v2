<?php


use Phinx\Seed\AbstractSeed;

class CreateTestUserSeeder extends AbstractSeed
{
	/**
	 * Run Method.
	 *
	 * Write your database seeder using this method.
	 *
	 * More information on writing seeders is available here:
	 * https://book.cakephp.org/phinx/0/en/seeding.html
	 */

	private function dummyData()
	{
		return [
			[
				'insertion' => [
					'title' => 'Prof',
					'staff_id' => '999990',
					'firstname' => ucwords(strtolower('Director')),
					'othernames' => ucwords(strtolower('Director')),
					'lastname' => ucwords(strtolower('Director')),
					'gender' => 'Male',
					'dob' => '',
					'email' => 'director@test.com',
					'phone_number' => '08100000000',
					'marital_status' => 'Married',
					'user_rank' => '',
					'role' => '',
					'avatar' => '',
					'address' => '',
					'active' => 1,
					'can_upload' => 0,
					'units_id' => 0
				],
				'role' => 'dir'
			],
			[
				'insertion' => [
					'title' => 'Prof',
					'staff_id' => '999991',
					'firstname' => ucwords(strtolower('Deputy Bursar')),
					'othernames' => ucwords(strtolower('DB')),
					'lastname' => ucwords(strtolower('Bursar')),
					'gender' => 'Male',
					'dob' => '',
					'email' => 'db@test.com',
					'phone_number' => '08100000001',
					'marital_status' => 'Married',
					'user_rank' => '',
					'role' => '',
					'avatar' => '',
					'address' => '',
					'active' => 1,
					'can_upload' => 0,
					'units_id' => 0
				],
				'role' => 'db'
			],
			[
				'insertion' => [
					'title' => 'Prof',
					'staff_id' => '999992',
					'firstname' => ucwords(strtolower('Bursary Staff')),
					'othernames' => ucwords(strtolower('DB-Staff')),
					'lastname' => ucwords(strtolower('Staff')),
					'gender' => 'Male',
					'dob' => '',
					'email' => 'db-staff@test.com',
					'phone_number' => '08100000002',
					'marital_status' => 'Married',
					'user_rank' => '',
					'role' => '',
					'avatar' => '',
					'address' => '',
					'active' => 1,
					'can_upload' => 0,
					'units_id' => 0
				],
				'role' => 'db-staff'
			],
			[
				'insertion' => [
					'title' => 'Prof',
					'staff_id' => '999993',
					'firstname' => ucwords(strtolower('Audit Officer')),
					'othernames' => ucwords(strtolower('Audit')),
					'lastname' => ucwords(strtolower('Staff')),
					'gender' => 'Male',
					'dob' => '',
					'email' => 'audit@test.com',
					'phone_number' => '08100000003',
					'marital_status' => 'Married',
					'user_rank' => '',
					'role' => '',
					'avatar' => '',
					'address' => '',
					'active' => 1,
					'can_upload' => 0,
					'units_id' => 0
				],
				'role' => 'aud'
			],
			[
				'insertion' => [
					'title' => 'Prof',
					'staff_id' => '999994',
					'firstname' => ucwords(strtolower('Procurement Officer')),
					'othernames' => ucwords(strtolower('Proc')),
					'lastname' => ucwords(strtolower('Officer')),
					'gender' => 'Male',
					'dob' => '',
					'email' => 'procurement@test.com',
					'phone_number' => '08100000004',
					'marital_status' => 'Married',
					'user_rank' => '',
					'role' => '',
					'avatar' => '',
					'address' => '',
					'active' => 1,
					'can_upload' => 0,
					'units_id' => 0
				],
				'role' => 'proc'
			]
		];
	}

	/**
	 * @throws Exception
	 */
	public function run(): void
	{
//		$this->getAdapter()->getConnection()->beginTransaction();
		try {
			$data = $this->dummyData();
			foreach ($data as $value) {
				$posts = $this->table('staffs');
				$insertion = $value['insertion'];
				$insertion['outflow_slug'] = $value['role'];

				$posts->insert($insertion)
					->saveData();
				$lastUserInsertedId = $this->getAdapter()->getConnection()->lastInsertId();
				$password = password_hash('_12345678', PASSWORD_DEFAULT);

				// insertion to users_new table
				$afterData = [
					'user_table_id' => $lastUserInsertedId,
					'user_type' => 'staff',
					'active' => 1,
					'user_pass' => '',
					'password' => $password,
					'user_login' => $insertion['email']
				];
				$table = $this->table('users_new');
				$table->insert($afterData)->saveData();
				$lastUserNewInsertedId = $this->getAdapter()->getConnection()->lastInsertId();

				// create role for the user
				$roleData = [
					'name' => $insertion['firstname'],
					'active' => 1,
				];
				$table = $this->table('roles');
				$table->insert($roleData)->saveData();
				$lastRoleInsertedId = $this->getAdapter()->getConnection()->lastInsertId();

				// insert role user
				$roleUserData = [
					'role_id' => $lastRoleInsertedId,
					'user_id' => $lastUserNewInsertedId
				];
				$table = $this->table('roles_user');
				$table->insert($roleUserData)->saveData();
			}

//			$this->getAdapter()->getConnection()->commit();

		} catch (Exception $e) {
//			$this->getAdapter()->getConnection()->rollBack();
			throw $e;
		}

	}
}
