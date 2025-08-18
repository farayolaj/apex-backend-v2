<?php


use Phinx\Seed\AbstractSeed;

class TransactionReserveUntilSeeder extends AbstractSeed
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
		$intervalHours = 48;
		$futureDate = $this->getFutureDateTime($intervalHours);

		$chunkSize = 1000;
		$ids = $this->fetchAll("
    		SELECT id FROM transaction 
    		WHERE payment_status NOT IN ('00', '01')
		");

		// Process in chunks
		foreach (array_chunk($ids, $chunkSize) as $chunk) {
			$idList = implode(',', array_column($chunk, 'id'));

			$this->execute("UPDATE transaction SET reserved_until = '{$futureDate}'
        		WHERE id IN ({$idList})
    		");
		}
	}

	private function getFutureDateTime(int $hours): string
	{
		return (new DateTime())
			->add(new DateInterval("PT{$hours}H"))
			->format('Y-m-d H:i:s');
	}
}
