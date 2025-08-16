<?php

require_once('application/models/Crud.php');

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the staff_department table
 */
class Staff_department extends Crud
{

	protected static $tablename = 'Department'; // redirect to department table
	static $nullArray = array();
	/*this array contains the fields that are unique*/
	static $uniqueArray = array();
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('faculty_id' => 'int', 'name' => 'varchar', 'slug' => 'varchar', 'code' => 'varchar', 'active' => 'tinyint',
		'date_created' => 'datetime', 'type' => 'enum');
	static $displayField = 'name';
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'faculty_id' => 'Faculty', 'name' => '', 'slug' => '', 'code' => '', 'active' => '',
		'date_created' => '', 'type' => '');
	public static $apiSelectClause = ['id', 'name', 'slug', 'code'];

	public function __construct(array $array = [])
	{
		parent::__construct($array);
	}

	public function APIList($filterList, $queryString, $start, $len, $orderBy): array
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		$filterQuery .= ($filterQuery ? " and " : " where ") . " type='non-academic' ";

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by name asc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}
		$tablename = 'department';
		$query = "SELECT " . buildApiClause(static::$apiSelectClause, $tablename) . " from $tablename $filterQuery";

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
		return [$res, $res2];
	}


}
