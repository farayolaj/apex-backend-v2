<?php


use Phinx\Seed\AbstractSeed;

class UpdateBookStoreSeeder extends AbstractSeed
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
		// update payment_bookstore.book_type values based on if it course code is ges
		$query = "UPDATE payment_bookstore a JOIN courses b ON a.course_id = b.id SET a.book_type = 'ges' 
                WHERE b.code LIKE 'GES%'";
		$this->execute($query);
	}
}
