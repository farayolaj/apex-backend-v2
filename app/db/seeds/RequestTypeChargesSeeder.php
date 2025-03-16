<?php


use Phinx\Seed\AbstractSeed;

class RequestTypeChargesSeeder extends AbstractSeed
{
	/**
	 * Run Method.
	 *
	 * Write your database seeder using this method.
	 *
	 * More information on writing seeders is available here:
	 * https://book.cakephp.org/phinx/0/en/seeding.html
	 */

	/*public function getDependencies(): array
	{
		return [
			'PaymentVoucherSeeder',
			'RequestChargesSeeder'
		];
	}*/

	private function filterOutCharges($array, $result): array
	{
		// Get the keys of the filtered array
		$filteredKeys = array_keys($result);
		$originalKeys = array_keys($array);

		$nonMatchingKeys = array_diff_key(array_flip($originalKeys), array_flip($filteredKeys));
		return array_intersect_key($array, array_flip($nonMatchingKeys));
	}

	public function run(): void
	{
		$query = "SELECT * from request_type where active = '1'";
		$data = $this->query($query);
		$types = $data->fetchAll();

		$query = "SELECT * from request_charges where active = '1'";
		$data = $this->query($query);
		$charges = $data->fetchAll();

		foreach ($types as $type) {
			if ($type['type'] === 'contractor') {
				$processedCharges = array_filter($charges, function ($charge) {
					return $charge['slug'] !== 'DEF';
				}, ARRAY_FILTER_USE_BOTH);
				$filteredOut = $this->filterOutCharges($charges, $processedCharges);

				if ($processedCharges) {
					foreach ($processedCharges as $charge) {
						$insertData = [
							'request_type_id' => $type['id'],
							'request_charge_id' => $charge['id'],
							'is_editable' => 1,
							'active' => 1,
						];

						$posts = $this->table('request_type_charges');
						$posts->insert($insertData)
							->saveData();

					}
				}

				if ($filteredOut) {
					foreach ($filteredOut as $charge) {
						$insertData = [
							'request_type_id' => $type['id'],
							'request_charge_id' => $charge['id'],
							'is_editable' => 0,
							'active' => 0,
						];

						$posts = $this->table('request_type_charges');
						$posts->insert($insertData)
							->saveData();

					}
				}
			}

			if ($type['type'] === 'staff') {
				$processedCharges = array_filter($charges, function ($charge) {
					return $charge['slug'] === 'DEF';
				}, ARRAY_FILTER_USE_BOTH);
				$filteredOut = $this->filterOutCharges($charges, $processedCharges);

				if ($processedCharges) {
					foreach ($processedCharges as $charge) {
						$insertData = [
							'request_type_id' => $type['id'],
							'request_charge_id' => $charge['id'],
							'is_editable' => 1,
							'active' => 1,
						];

						$posts = $this->table('request_type_charges');
						$posts->insert($insertData)
							->saveData();

					}
				}

				if ($filteredOut) {
					foreach ($filteredOut as $charge) {
						$insertData = [
							'request_type_id' => $type['id'],
							'request_charge_id' => $charge['id'],
							'is_editable' => 1,
							'active' => 0,
						];

						$posts = $this->table('request_type_charges');
						$posts->insert($insertData)
							->saveData();

					}
				}
			}

		}
	}
}
