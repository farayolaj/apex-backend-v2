<?php
/**
 * This is a trait helper class for crud, such that methods that are peculiar
 * to the class is turned into a trait helper
 */
namespace App\Traits;

trait CrudInfo {

	public function totalEntityCount(string $tablename, string $queryclause = '') {
		$tablename = strtolower($tablename);
		$query = "SELECT count(*) as total from $tablename $queryclause";
		$result = $this->query($query);
		return ($result) ? $result[0]['total'] : 0;
	}

	public function totalEntitySum(string $tablename, string $column, string $queryclause = '') {
		$tablename = strtolower($tablename);
		$query = "SELECT sum($column) as total from $tablename $queryclause";
		$result = $this->query($query);
		return ($result) ? $result[0]['total'] : 0;
	}
}