<?php

namespace App\Models;

use App\Traits\CrudTrait;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\Query;
use DateTime;
use Exception;

class Crud {
	use CrudTrait;

    /**
     * @var mixed|null
     */
    private static mixed $baseurl;

    protected array $array = [];

    protected string $foreignKeyEnd = '_id';

	protected BaseConnection $db;

	private string $entitiesNameSpace = 'App\\Entities\\';

	private bool $modelPaging = false;

    private bool $getWhereResultAsObject = true;

    protected array  $searchable = []; // e.g. ['a.title','a.code']

    protected array  $sortable   = []; // e.g. ['code'=>'a.code','id'=>'a.id']

    /**
     * @throws Exception
     */
    public function __construct($array = []) {
		helper(['string', 'text', 'array']);
		if (!is_array($array)) {
			throw new Exception("Constructor argument must be an array");
		}
		$this->array = $array;
		$this->db = db_connect();
	}

    /**
     * This function builds the select clause needed to retrieve feed of this table while substituting
     * the foreign key id with the display name.
     * @param bool $calfound determines if rows should be included in the generated query
     * @param string $search
     * @return string the sql select clause that will replace the foreign key id with the
     * corresponding display name
     */
	private function buildSelectClause(bool $calfound = true, string $search = ''): string
    {
		if (isset(static::$selectQuery) && static::$selectQuery) {
			return static::$selectQuery . $search;
		}
		$whereClause = '';
		$foundrowString = $calfound ? 'SQL_CALC_FOUND_ROWS ' : '';
		$thisTable = $this->getTableName();
		$onclause = "";
		$foreignTable = array();
		$fields = array_keys(static::$labelArray);
		if (!$this->foreignKeyPresent($fields)) {
			return "SELECT * from {$thisTable}";
		}
		$this->buildTableJoinQuery($thisTable, $fields, $onclause, $foreignTable);
		$fieldList = implode(',', $fields);
		$tableList = '(' . implode(',', $foreignTable) . ')';
		// $joinStatement = empty($foreignTable)?'':"left join $tableList on ($onclause)";
		$joinStatement = $onclause;
        return "SELECT {$foundrowString} {$fieldList} from {$thisTable} {$joinStatement} {$search} ";
	}

    /**
     * This is for getting the name of the table
     * @param string|null $external [description]
     * @return string [type] [description]
     */
	public function getTableName(string $external = null): string
    {
        if (!$external) {
			$tableName = strtolower(static::$tablename);
		} else {
			$tableName = strtolower($external);
		}

		return $tableName;
	}

	private function foreignKeyPresent($fields): bool
    {
		for ($i = 0; $i < count($fields); $i++) {
			$field = $fields[$i];
			if (endsWith($field, $this->foreignKeyEnd)) {
				return true;
			}
		}
		return false;
	}

    /**
     * TODO: FIXING PRIMARY TABLE APPEARING AS A FOREIGN TABLE IN THE QUERY
     * I think this had been fixed
     * @param $thisTable
     * @param $fields
     * @param $onclause
     * @param $foreignTable
     * @return void [type]                [description]
     */
	public function buildTableJoinQuery($thisTable, &$fields, &$onclause, &$foreignTable): void
    {
		for ($i = 0; $i < count($fields); $i++) {
			$field = $fields[$i];
            $isValidModel = true;
			if (endsWith($field, $this->foreignKeyEnd)) {
				$tablename = substr($field, 0, strlen($field) - strlen($this->foreignKeyEnd));
				$tablename = strtolower($tablename);
				$oldTableName = $tablename;
				if (!class_exists($tablename)) {
                    try{
                        $modelName = $this->entitiesNameSpace . ucfirst($tablename);
                        $tablename = new $modelName;
                    }catch (Exception $e){
                        $isValidModel = false;
                    }
				}

				if ($isValidModel && isset($tablename::$displayField)) {
					$display = '';
					if (is_array($tablename::$displayField)) {
						$display = "concat_ws(' '";
						foreach ($tablename::$displayField as $tval) {
							$display .= "," . $oldTableName . '.' . $tval;
						}
						$display .= ") as $field";
					} else {
						$display = strtolower($tablename::$tablename) . '.' . $tablename::$displayField . ' as ' . $field;
					}
					$foreignTable[] = $oldTableName;
					$temp = $thisTable . '.' . $oldTableName . $this->foreignKeyEnd;

					$usse = isset($tablename::$joinField) ? $oldTableName . '.' . $tablename::$joinField : "$oldTableName.id";

					$onclause .= " left join $oldTableName on $temp = $usse ";

				} else {
					$display = $thisTable . '.' . $field;
				}
				$fields[$i] = $display;
			} else {
				$fields[$i] = $thisTable . '.' . $field;
			}
		}
	}

	/**
	 * This is a setter magic method
	 * @param string 		$name  [description]
	 * @param 	$value [description]
	 */
	public function __set(string $name, $value) {
		$this->array[$name] = $value;
	}

    /**
     *  This is a getter magic method
     * @param string $name [description]
     * @return mixed|null [type]       [description]
     */
	public function __get(string $name) {
		$methodName = "get" . ucfirst($name);
		if (array_key_exists($name, $this->array)) {
			return $this->array[$name];
		} else if (method_exists($this, $methodName)) {
			return $this->$methodName();
		} else {
            // this would mean that the property does not exist
            // and this would trigger an error for any GET method call
			return null;
		}
	}

	public function getModelPaging(): bool
    {
		return $this->modelPaging;
	}

	public function setModelPaging(bool $value): void
    {
		$this->modelPaging = $value;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
    {
		return $this->array;
	}

	/**
	 * @param array $array
	 */
	public function setArray(array $array): void
    {
		$this->array = $array;
	}

	/*
	 * This just save data, it updates if present else insert if not
	 */
	public function save() {
        $db = $this->db;
		$temp = $this->getWhere($this->array, $totalRow, 0, null, false, $db);
		if ($temp) {
			$this->id = $temp[0]->id;
			return $this->update($db);
		} else {
			return $this->insert($db);
		}
	}

	protected function buildWhereString($id, &$data): string
    {
		$data = array();
		if (is_array($id)) {
			$keys = array_keys($id);
			$data = array_values($id);
			for ($i = 0; $i < count($keys); $i++) {
				if (($pos = strpos($keys[$i], ' like ')) !== false) {
					$keys[$i] = $i == 0 ? $keys[$i] . '?' : " AND " . $keys[$i] . '?';
					continue;
				}
				$keys[$i] = $i == 0 ? $keys[$i] . '=?' : " AND " . $keys[$i] . '=?';
			}
			return implode(' ', $keys);
		} else {
			// assumes its a string
			$data[] = $id;
			return "id=?";
		}
	}

	/**
	 *the where contains the list of fieldname and the value
	 * @param array $parameter
	 * @param int &$totalRow
	 * @param int $start
	 * @param int $length
	 * @param bool $resolveForeign
	 * @param string $sort
	 * @param &$dbObject
	 * @return object|array|false
	 */
	public function getWhere($parameter, &$totalRow = -1, $start = 0, $length = null, $resolveForeign = true, $sort = '', &$dbObject = null) {
		$tablename = $this->getTableName();
		$limit = "";
		$array = array();
		if ($length) {
			$limit = " LIMIT ?,?";
			$array = array($start, $length);
		}
		$whereString = $this->buildWhereString($parameter, $data);
		if (!empty($array)) {
			$data = array_merge($data, $array);
		}

		if ($whereString) {
			$query = $resolveForeign ? $this->buildSelectClause() . " where {$whereString} {$sort} {$limit} " : "SELECT SQL_CALC_FOUND_ROWS * from {$tablename} where {$whereString} {$sort} {$limit} ";

		} else {
			$query = $resolveForeign ? $this->buildSelectClause() . " {$sort} {$limit} " : "SELECT SQL_CALC_FOUND_ROWS * from {$tablename} {$sort} {$limit} ";

		}
		// $query.=' '.$sort;
		$result = $this->query($query, $data, $dbObject);
		$result2 = $this->query("SELECT FOUND_ROWS() as totalCount");
		$totalRow = $result2[0]['totalCount'];
		if ($result) {
			return $this->getWhereResultAsObject ? $this->buildObject($tablename, $result) : $result;
		} else {
			return false;
		}
	}

	/**
	 * Get where without returning an object
	 * @param array $parameter
	 * @param int &$totalRow
	 * @param int $start
	 * @param int $length
	 * @param bool $resolveForeign
	 * @param string $sort
	 * @param &$dbObject
	 * @return array|false
	 */
	public function getWhereNonObject($parameter, &$totalRow = -1, $start = 0, $length = null, $resolveForeign = true, $sort = '', &$dbObject = null)
    {
		$this->getWhereResultAsObject = false;
        return $this->getWhere($parameter, $totalRow, $start, $length, $resolveForeign, $sort, $dbObject);
	}

    /**
     * This function build a crud object array from an array of array
     * @param string $classname the name of the class
     * @param array $result of array $result    The array needed to be converted to crud object.
     * @return array The array of object built
     */
	protected function buildObject(string $classname, array $result): array
    {
        $objectArray = [];
        // Check if the class exists in the global namespace
        if (!class_exists($classname)) {
            $modelName = $this->entitiesNameSpace . ucfirst($classname);
            if (!class_exists($modelName)) {
                throw new \RuntimeException("Class '$classname' or '$modelName' does not exist.");
            }
            $classname = $modelName;
        }
        // Create objects from the result set
        foreach ($result as $row) {
            $objectArray[] = new $classname($row);
        }
        return $objectArray;
	}

	public function allNonObject(&$totalRow = 0, $resolveForeign = true, $lower = 0, $length = NULL, $sort = '', $where = '') {
		$this->getWhereResultAsObject = false;
		return $this->all($totalRow, $resolveForeign, $lower, $length, $sort, $where);
	}

	public function all(&$totalRow = 0, $resolveForeign = true, $lower = 0, $length = null, $sort = '', $where = '') {
		$tablename = $this->getTableName();
		$limit = "";
		$array = [];
		if ($length) {
			$limit = " limit ?, ? ";
			$array = [$lower, $length];
		}

		$query = $resolveForeign ? $this->buildSelectClause() . " {$where} {$sort} {$limit} " : "SELECT SQL_CALC_FOUND_ROWS * FROM $tablename $where $sort $limit ";
		$result = $this->query($query, $array);
		$result2 = $this->query("SELECT FOUND_ROWS() as totalCount");
		$totalRow = $result2[0]['totalCount'];
		return $this->getWhereResultAsObject ? $this->buildObject($tablename, $result) : $result;
	}

	private function buildOtherClause(): string
    {
		$clause = "*";
		if (isset(static::$apiSelectClause) && static::$apiSelectClause) {
			$clause = implode(',', static::$apiSelectClause);
		}
		return "SQL_CALC_FOUND_ROWS " . $clause;
	}

    /**
     * Added this for json request and response using a framework like vue
     * the where contains the list of fieldname and the value
     * @param array $parameter
     * @param int $totalRow
     * @param int $start
     * @param int|null $length
     * @param bool $resolveForeign
     * @param string $sort
     * @param string|null $whereClause
     * @param object|null $dbObject
     * @return array
     */
	public function allListFiltered(array $parameter, int &$totalRow = -1,
		int $start = 0, int $length = null, bool $resolveForeign = true,
		string $sort = ' order by id desc ', string $whereClause = null,
		object &$dbObject = null
	) {

		$tablename = $this->getTableName();
		$classname = ucfirst($tablename);
		$limit = "";
		// using this for filters based on limiting the result
		if ($length && !$this->modelPaging) {
			$limit = " LIMIT $start, $length";
		} else {
			// using this for paging
			$start = ($start > 0) ? ($start * $length) : 0;
			$end = $length;
			$limit = " LIMIT $start, $end";
		}
		$whereString = $this->buildWhereString($parameter, $data);
        if (!empty($array)) {
            $data = array_merge($data, $array);
        }
		if ($whereString) {
			if ($whereClause) {
				$whereClause = " and ($whereClause) ";
				$whereString .= $whereClause;
			}
			$otherClause = "SELECT " . $this->buildOtherClause() . " from $tablename where  $whereString $sort $limit";
			$query = $resolveForeign ? $this->buildSelectClause() . " where  $whereString $sort $limit" : $otherClause;
		} else {
			if ($whereClause) {
				$whereClause = " where ($whereClause) ";
			}
			$otherClause = "SELECT " . $this->buildOtherClause() . " from $tablename $whereClause $sort $limit";
			$query = $resolveForeign ? $this->buildSelectClause() . " $whereClause $sort  $limit" : $otherClause;
		}
		$result = $this->query($query, $data, $dbObject);
		$result2 = $this->query("SELECT FOUND_ROWS() as totalCount");
		$totalRow = $result2[0]['totalCount'];
		return array($result, $totalRow);
	}

    public function apiQueryListFiltered(array $selectData, ?array $filterList, ?string $queryString,
                                               $start, $len, ?string $orderBy = null): array
    {
        $orderByString = !isset($_GET['sortBy']);
        $temp = getFilterQueryFromDict($filterList);
        $filterQuery = buildCustomWhereString($temp[0], $queryString, $orderByString);
        $filterValues = $temp[1];

        if (isset($_GET['sortBy']) && $orderBy) {
            $filterQuery .= " order by $orderBy ";
        }

        if (isset($_GET['start']) && $len) {
            $filterQuery .= " limit $start, $len";
        }
        if (!$filterValues) {
            $filterValues = [];
        }
        $tablename = $this->getTableName();
        $query = "SELECT " . buildApiClause($selectData, 'a') . " from $tablename a $filterQuery";
        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query, $filterValues);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        return [$res, $res2];
    }

    public function apiQueryListCustomFiltered(string $query, ?array $queryData = []): array
    {
        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query, $queryData);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        return [$res, $res2];
    }

	public function allQuery($query): string
    {
		$tempQuery = $this->db->escapeString($query);
		$fields = array_keys(static::$labelArray);
		$isFirst = true;
		$result = "";
		foreach ($fields as $field) {
			if (!$isFirst) {
				$result .= ' or ';
				$isFirst = false;
				continue;
			}
			$result .= " $field like '%$tempQuery%' ";
		}
		return $result;

	}

	public function search($q, &$totalRow = 0, $resolveForeign = true, $lower = 0, $length = null, $sort = '') {
		$tablename = $this->getTableName();
		$limit = "";
		$array = array();
		if ($length) {
			$limit = " LIMIT ?,?";
			$array = array($lower, $length);
		}
		$allQuery = $this->allQuery($q);
		$query = $resolveForeign ? $this->buildSelectClause(true, $allQuery) . " $sort $limit" : "SELECT SQL_CALC_FOUND_ROWS * FROM $tablename $sort $limit ";
		$result = $this->query($query, $array);
		$result2 = $this->query("SELECT FOUND_ROWS() as totalCount");
		$totalRow = $result2[0]['totalCount'];
		return $result;
	}

    /**
     * @param int|null $id
     * @param object|null $dbObject
     * @return false|mixed
     * @throws Exception
     * @throws Exception
     */
	public function view(int $id = null, object &$dbObject = null) {
		if (!$id) {
			if (array_key_exists('id', $this->array)) {
				$id = $this->array['id'];
			} else {
				throw new Exception('Please specify the index or set the index value as a parameter');
			}
		}
		$tablename = $this->getTableName();
		$query = "SELECT * FROM $tablename where id=?";
		$result = $this->query($query, array($id), $dbObject);
		if (count($result) == 0) {
			return false;
		}
		$result = $result[0];
		$tablename = $this->entitiesNameSpace . $tablename;
		return new $tablename($result);
	}

    /**
     * @param int|null $id
     * @param object|null $dbObject
     * @return bool
     * @throws Exception
     */
	public function load(int $id = null, object &$dbObject = null) {
		$result = $this->view($id, $dbObject);
		if ($result) {
			$this->array = $result->toArray();
			return true;
		} else {
			return false;
		}
	}

	public function queryTable(string $query, array $data = array(), object &$dbObject = null) {
		$tablename = $this->getTableName();
		$result = $this->query($query, $data, $dbObject);
		$resultObjects = array();
		$tablename = $this->entitiesNameSpace . $tablename;
		foreach ($result as $value) {
			$resultObjects[] = new $tablename($value);
		}
		return $resultObjects;
	}

    /*
     * This is to query the database
     */
	public function query(string $query, array $data = [], object &$dbObject = null) {
		$db = $this->db;
		if ($dbObject) {
			$db = $dbObject;
		}
		$result = $db->query($query, $data);
		if (!is_object($result)) {
			return $result;
		}
        return $result->getResultArray();
	}

    /**
     * This is to update the model
     * @param int|null $id [description]
     * @param object|null &$dbObject [description]
     * @return bool [type] [description]
     * @throws Exception
     */
	public function update(int $id = NULL, object &$dbObject = null) {
		if (empty($id) && !isset($this->array['id'])) {
			throw new Exception("Entity Property id cannot be empty");
		}
		$tablename = $this->getTableName();
		$id = $id == null ? $this->array['id'] : $id;
		$query = "UPDATE $tablename SET ";
		$query .= $this->buildUpdateQuery($data);
		$whereCondition = $this->buildWhereString($id, $temp);
		$query .= " WHERE $whereCondition";
		$data = array_merge($data, $temp);

		$result = $this->query($query, $data, $dbObject);
		if ($result > 0) {
			return true;
		} else {
			return false;
		}
	}

    /**
     * [buildUpdateQuery description]
     * @param array|null $data [description]
     * @return string        [description]
     */
	private function buildUpdateQuery(array &$data = null): string
    {
		$result = " ";
		$data = array();
		$keys = array_keys($this->array);
		$new = true;
		for ($i = 0; $i < count($keys); $i++) {
			$key = $keys[$i];
			if ($key == "id") {
				continue;
			}
			$result .= $new ? " $key = ?" : ", $key = ?";
			$new = false;
			$data[] = $this->array[$key];
		}
		return $result;
	}

	private function buildExistWhereString($id, &$data): string
    {
		if (empty($id)) {
			return '';
		}
		$data = array();
		$result = "";
		for ($i = 0; $i < count($id); $i++) {
			$current = $id[$i];
			$result .= $i == 0 ? " $current = ?" : " and $current = ? ";
			$data[] = @$this->array[$current];
		}
		return $result;
	}

	/**
	 * @param int 		$id
	 * @param object	&$dbObject
	 * @param array 	&$arrData
	 * @return bool
	 */
	public function exists($id, &$dbObject = null, &$arrData = array()): bool
    {
		$tablename = $this->getTableName();
		$wherelist = $this->buildExistWhereString($id, $data);
		$arrData = $data;
		$query = "SELECT count(*) as countData from $tablename where $wherelist";
		$result = $this->query($query, $data, $dbObject);
		return $result[0]['countData'] != 0;
	}

	/**
	 * @return array|bool
     */
	private function checkExist() {
		if (isset(static::$uniqueArray) && !empty(static::$uniqueArray)) {
			$uniqueKeys = static::$uniqueArray;
			foreach ($uniqueKeys as $value) {
				$result = $this->exists(array($value), $dbObject, $arrData);
				if ($result) {
					return (!empty($arrData)) ? $arrData : true;
				}
			}
		}

		if (isset(static::$compositePrimaryKey) && !empty(static::$compositePrimaryKey)) {
			$uniqueKeys = static::$compositePrimaryKey;
            return $this->exists($uniqueKeys);
		}
		return false;
	}

    /**
     * @param object|null &$dbObject
     * @param string|null $message
     * @return bool
     * @throws Exception
     */
	public function insert(?object &$dbObject = null, ?string &$message = ''): bool
    {
		$tablename = $this->getTableName();
		if (empty($this->array)) {
            throw new Exception("No value to insert");
		}

		if ($checkMsg = $this->checkExist()) {
			if (is_array($checkMsg)) {
				$string = implode(",", $checkMsg);
				$string = ellipsize($string, 40, 1);
				$message = "Oops, {$tablename}[{$string}] data already exist";
			} else {
				$message = 'Oops, data already exist';
			}
			return false;
		}
		$tablename = $this->getTableName();
		$query = "INSERT INTO $tablename (";
		$data = array();
		$partTwo = "";
		$keys = array_keys($this->array);
		for ($i = 0; $i < count($keys); $i++) {
			$key = $keys[$i];
			$query .= $i == 0 ? "$key" : "," . "$key";
			$data[] = $this->array[$key];
			$partTwo .= $i == 0 ? "?" : "," . "?";
		}
		$query .= ") VALUES (";
		$query .= $partTwo . ")";
		$result = $this->query($query, $data, $dbObject);
		if ($result) {
			return true;
		} else {
			return false;
		}
	}

    /**
     * [delete description]
     * @param int|null $id [description]
     * @param object|null &$dbObject [description]
     * @return array|array[]|bool|BaseResult|Query [type] [description]
     * @throws Exception
     */
	public function delete(int $id = null, object &$dbObject = null) {
		if (!$id && !isset($this->array['id'])) {
			throw new Exception("Object does not have id");
		}

		if (!$id) {
			$id = $this->array["id"];
		}
		$tablename = $this->getTableName();
		$query = "DELETE from $tablename where id=?";
		return $this->query($query, [$id], $dbObject);

	}

    /**
     * @throws Exception
     */
    public function enable($id = null, &$dbObject = null): bool
    {
        if (!$id && !isset($this->array['id'])) {
            throw new Exception("Object does not have id");
        }
        if (!$id) {
            $id = $this->array["id"];
        }
        return $this->setEnabled($id, 1, $dbObject, 'enable');
    }

    /**
     * @throws Exception
     */
    public function disable($id = null, &$dbObject = null): bool
    {
        if (!$id && !isset($this->array['id'])) {
            throw new Exception("Object does not have id");
        }

        if (!$id) {
            $id = $this->array["id"];
        }
        return $this->setEnabled($id, 0, $dbObject, 'disable');
    }

    /**
     * This method is used to set the status of a record to either enable or disable
     * @param $id
     * @param $value
     * @param $dbObject
     * @param $type
     * @return bool
     */
    protected function setEnabled($id, $value, &$dbObject, $type = null): bool
    {
        $tablename = $this->getTableName();
        $dbField = 'status';
        if (!$type) {
            $type = $value == 0 ? 'disable' : 'enable';
        }
        $dbField = $this->getDbStatusField($type, $dbField);
        $query = "UPDATE $tablename set {$dbField} = ? where id=?";
        $result = $this->query($query, array($value, $id), $dbObject);
        if ($result) {
            $this->array[$dbField] = $value;
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $type
     * @param $dbField
     * @return mixed|string
     */
    private function getDbStatusField($type, $dbField)
    {
        if (isset(static::$modelActionStatus)) {
            $temp = static::$modelActionStatus;
            $dbField = $temp[$type] ?: 'status';
        }
        return $dbField;
    }

    /**
     * Function to return the array need
     * @param $label
     * @param $link
     * @param $critical
     * @param $ajax
     * @return array
     */
	protected function buildActionArray($label, $link, $critical, $ajax): array
    {
		$result = array();
		$result['label'] = $label;
		$result['link'] = $link;
		$result['isCritical'] = $critical;
		$result['ajax'] = $ajax;
        return $result;
	}

    /**
     * This method return  true if the method is validated or false. if false, the
     * message is passed to the message variable displayed.
     * @param string|null $message
     * @param bool $validateType
     * @return bool
     */
	public function validateInsert(?string &$message, bool $validateType = false): bool
    {
        $result = 0;
        $message = '';
        $condition = empty(static::$nullArray);
        foreach ($this->array as $key => $value) {
            if (empty($value) && ($condition || !in_array($key, static::$nullArray))) {
                if ($result !== 0) {
                    $message .= ',';
                }
                $message .= empty(static::$labelArray[$key]) ? $this->generateFormlabel($key) : static::$labelArray[$key];
                $result++;
            }

        }

        if ($result) {
            if ($result == 1) {
                $message = $message . ' cannot be empty';
            } else {
                $message = 'The following fields cannot be empty ' . $message;
            }
            return false;
        } else {
            return true;
        }
	}

	/**
	 * @param string $fieldname
	 * @return string
	 */
	private function generateFormLabel($fieldname): string
    {
		$result = '';
		if (empty($fieldname)) {
			return $result;
		}
		$list = explode("_", $fieldname);

		for ($i = 0; $i < count($list); $i++) {
			$current = ucfirst($list[$i]);
			$result .= $i == 0 ? $current : " $current";
		}
		return $result;
	}

	//the array must contain the table name and the display value field then it can load options
	public function loadOption($array, $val = false, $prepend = '', $orderBy = '', $hidden = false): string
    {
		extract($array);
		// the $hidden parameter means to use the display value has the hidden value for select option
		if ($orderBy != '') {
			$orderBy = $orderBy;
		} else {
			$orderBy = $display;
		}

		if (is_array($table) && count($table) == 2) {
			$query = $this->buildJoin($table, $display);
		} else {
			$orderBy = ($orderBy != '') ? $orderBy : "order by id asc";
			$query = "SELECT id,$display as value FROM $table $orderBy";
		}
		$result = $this->query($query);
		if (is_array($prepend)) {
			$result = array_merge($prepend, $result);
		}
		return $this->buildSelectOption($result, $val, $hidden);

	}

	private function buildJoin($table, $display): string
    {
		$first = $table[0];
		$second = $table[1];
		$firstWithId = $first . '.id';
		$secondWithId = $second . '.id';
        return "SELECT $firstWithId as id, $display as value from $first left join $second on $firstWithId = $secondWithId";
	}

	public function loadValueOption($arrayValue, $value = false): string
    {
		$result = "<option value='' selected='selected'>..choose..</option>";
		if ($value) {
			$result = "<option value=''>..choose..</option>";
		}
		for ($i = 0; $i < count($arrayValue); $i++) {
			$current = $arrayValue[$i];
			if ($value && $value == $current) {
				$result .= "<option value='$current' selected='selected'>$current</option>";
				continue;
			}
			$result .= "<option value='$current'>$value</option>";
		}
		return $result;
	}

	public function buildSelectOption($array, $val, $hidden = false): string
    {

		$result = "<option value='' selected='selected'>...choose option...</option>";
		if ($val) {
			$result = "<option value=''>..choose..</option>";
		}
		for ($i = 0; $i < count($array); $i++) {
			$current = $array[$i];
			extract($current);
			$hiddenValue = '';
			if ($hidden) {
				$hiddenValue = $value;
			} else {
				$hiddenValue = $id;
			}
			if ($val && $val == $id) {
				$result .= "<option value='$id' selected='selected'>$value</option>";
				continue;
			} else if ($val && $val == $value) {
				$result .= "<option value='$value' selected='selected'>$value</option>";
				continue;
			}
			$result .= "<option value='$hiddenValue'>$value</option>";
		}
		return $result;
	}

	public function buildSelectOptionNonAssoc($array, $val): string
    {
		$result = '';
		for ($i = 0; $i < count($array); $i++) {
			$current = $array[$i];
			if ($val && $val == $current) {
				$result .= "<option value='$current' selected='selected'>$current</option>";
				continue;
			}
			$result .= "<option value='$current'>$current</option>";
		}
		return $result;
	}

	// a function to upload multiple row of data
	public function upload($fields, $data, &$message = '', $dbObject = null, $translate = true) {
		$tables = $this->getTemplateTablesUpload();
		if (is_array($tables)) {
			return $this->uploadMultiples($tables, $fields, $data, $message, $dbObject, $translate);
		} else {
			return $this->uploadSingle($tables, $fields, $data, $message, $dbObject, $translate);
		}
	}
	private function getTemplateTablesUpload() {
		$tablename = $this->getTableName();
		if (isset(static::$uploadDependency) && static::$uploadDependency) {
			$result = static::$uploadDependency;
			$result[] = $tablename;
			return $result;
		}
		return $tablename;
	}
	private function buildOnUpdateField($fields): string
    {
		$result = " on duplicate key update ";
		foreach ($fields as $field) {
			$result .= "$field=values($field) ,";
		}
        return rtrim($result, ',');
	}

    /**
     * @param string $model
     * @param $fields
     * @param array $data
     * @param string    &$message - Reference parameters to the invoker
     * @param string $dbObject
     * @param bool $translate
     * @return array|bool
     * @throws Exception
     */
	public function uploadSingle($model, $fields, $data, &$message = '', $dbObject = '', $translate = false) {
		if (empty($data)) {
			$message = "no data found in the template file uploaded";
			return false;
		}
		$temp = $fields;
		if (!$this->validateHeader($temp, $model)) {
			$message = 'invalid file format. make sure you are using the downloaded template.';
			return false;
		}
		$hashPassword = strtolower($model) == 'user';
		$data = $translate ? $this->transformData($model, $data, $fields, $hashPassword) : $data;
		if (is_string($data)) {
			$message = $data;
			return false;
		}
		$query = $this->buildInsertSection($fields, $model);
		$query .= $this->buildmultipleInsertValue($data, $fields);
		$query .= $this->buildOnUpdateField($fields);
		$db = $dbObject == null ? $this->db : $dbObject;

		$result = $db->query($query, []);
		$mess = $db->query("show errors");
		$message = $mess->getResultArray();
		$message = $message ? $message[0]['Message'] : '';
		return $result;
	}

	/**
	 * @param array $header
	 * @param string $model
	 * @return bool
     */
	private function validateHeader(array $header, string $model) {
        $modelHeader = $this->buildTemplate($model);
        $common = array_intersect($header, $modelHeader);
        $diff = array_diff($header, $common);
        return empty($intersect);
		// return $header==$common;
	}

	/**
	 *  this function will replace the content of the array with the foreign table id,
	 * in case password is present make sure you has the value before sending into the
	 * database. so as not to introduce any error.
	 * @param string $model
	 * @param array $data
	 * @param array $fields
	 * @param bool $hashPassword
	 * @return array
	 */
	private function transformData($model, $data, &$fields, $hashPassword = false) {
		$temp = $this->buildTemplate($model, false);
		$foreign = $this->extractFk($temp);
		if (!empty($foreign)) {
			$fields = $this->replaceForeignField($fields, $foreign);
		}
		$passwordIndex = '';
		$usernameIndex = '';
		if ($hashPassword) {
			$passwordIndex = array_search('password', $fields);
			if ($passwordIndex == false) {
				throw new Exception("there is no password field to hash so set the user field currectly.", 1);
			}
			$usernameIndex = array_search('username', $fields);
		}
		for ($i = 0; $i < count($data); $i++) {
			if (!isset($data[$i])) {
				continue;
			}
			foreach ($foreign as $key => $value) {
				// $index = $fkKeys[$j];
				if (!isset($data[$i][$value])) {
					continue;
				}
				$key = loadClass($key);
				if (isset($key::$displayField)) {
					$fieldName = $key::$displayField;
					$tp = $this->getFieldID($fieldName, $data[$i][$value], $key);

					if ($tp === false) {
						$dt = $data[$i][$value];
						$line = $i + 1;
						exit("$dt does not exist  for $key on line $line, please check your file and try again");
					}

					$data[$i][$value] = $tp;
				}

			}
			if ($hashPassword) {
				$data[$i][$passwordIndex] = @crypt($data[$i][$usernameIndex]); //hash the password;
			}
			$assoc = array_combine($fields, $data[$i]);
			$this->setArray($assoc);
			$status = $this->validateInsert($message);
			if (!($status || $this->checkExist())) {
				//check if the value already exist
				return "$message on line " . ($i + 1) . " in the uploaded file ";
			}
		}
		return $data;
	}

	/**
	 * @param array $fields
	 * @param array $foreign
	 * @return array
	 */
	private function replaceForeignField($fields, &$foreign): array
    {
		$dictionary = $this->buildFkDictionary($foreign);
		$len = count($fields);
		for ($i = 0; $i < $len; $i++) {
			if (!isset($fields[$i])) {
				$len++;
				continue;
			}
			if (isset($dictionary[$fields[$i]])) {
				$fields[$i] = $dictionary[$fields[$i]];
				$classname = substr($fields[$i], 0, strlen($fields[$i]) - 3);
				$foreign[$classname] = $i;
			}
		}
		return $fields;
	}

	/**
	 * @param array $fk
	 * @return array
	 */
	private function buildFkDictionary(array $fk): array
    {
		$fk = array_keys($fk);
		$result = array();
		for ($i = 0; $i < count($fk); $i++) {
			$current = $fk[$i];
			$current = loadClass($current);
			if (isset($current::$displayField)) {
				$displayName = $current::$displayField;
				$result[$displayName] = $current . '_id';
			}
		}
		return $result;
	}

	/**
	 * @param array $fields
	 * @return array 	$result
	 */
	private function extractFk(array $fields): array
    {
		$result = array();
		for ($i = 0; $i < count($fields); $i++) {
			if (endsWith($fields[$i], '_id')) {
				$fieldName = substr($fields[$i], 0, strlen($fields[$i]) - strlen("_id"));
				$result[$fieldName] = $i;
			}
		}
		return $result;
	}

	/**
	 * @param string $fieldname
	 * @param string $fieldValue
	 * @param string $tablename
	 * @return int|bool
	 */
	private function getFieldID($fieldname, $fieldValue, $tablename) {
		$fieldname = trim($fieldname);
		$fieldValue = trim($fieldValue);
		$query = "SELECT id from $tablename where $fieldname =?";
		$result = $this->query($query, [$fieldValue]);
		if ($result) {
			return $result[0]['id'];
		}
		return false;
	}

	/**
	 * @param array $field
	 * @param string $tablename
	 * @return string
	 */
	private function buildInsertSection($field, $tablename): string
    {
		$fieldList = implode(',', $field);
		$query = "INSERT into $tablename ($fieldList) values ";
		return $query;
	}

	/**
	 *  It is assumed that the file is save for data  insertion
	 * @param array $data
	 * @param bool $fields
	 * @return array
	 */
	private function buildmultipleInsertValue($data, $fields = false): string
    {
		$result = '';
		for ($i = 0; $i < count($data); $i++) {
			$current = $data[$i];
			$current = $this->performValueCheck($current, $fields);
			$temp = implode(',', $current);
			if ($i != 0) {
				$result .= ',';
			}
			$result .= "($temp)";
		}
		return $result;
	}

	private function sqlDateFormat($date) {
		$d = Datetime::createFromFormat('d/m/Y', $date);
		$result = $d->format('Y-m-d');
		return $result;
	}

	/**
	 * This function checks that the data does not contain sql query by filtering the
	 * value and checking for invalid value
	 *
	 * @param array $values
	 * @param array $fields
	 * @return array
	 */
	private function performValueCheck($values, $fields): array
    {
		for ($i = 0; $i < count($values); $i++) {
			$value = $values[$i];
			$values[$i] = trim($value);
			if (strpos($fields[$i], 'date')) {
				$values[$i] = $this->sqlDateFormat($value);
			}
			if (!$this->isSqlSafeInput($value)) {
				exit('error while processing request. please try again');
			}
			// $temp = $values[$i];
			$temp = $this->db->escapeString($values[$i]);
			$values[$i] = "$temp";
		}
		return $values;
	}

    /**
     * This could be implemented if necessary for full security
     * @param string $query
     * @param array $data
     * @return array
     */
	private function isSqlSafeInput($value) {
		return true;
	}

	/*
		|	this function will export data to .csv extension format.
		|	@return the .csv data exported for download even if a where clause is stated as a condition
	*/
	public function export($condition = null) {
		$data = $this->getExportData($condition);
		if (!$data) {
			exit("no data found to export.");
		}
		$filename = static::$tablename . '_export.csv';
		$header = 'text/csv';
		$content = arrayToCsvString($data); //convert the two dimenensional array to csv here.
		sendDownload($content, $header, $filename);
	}

	/*
		|	this function will get the necessary data to be use as content in the exported file
		|	NOTE: it also accomomdate a conditional statement (i.e a where clause)
		|	@return the necessary data from the db
	*/
	private function getExportData($condition = null) {
		$fields = $this->getModelTemplateHeader(true, true);
		$fieldList = implode(',', $fields);
		$joinString = $this->getExportJoinString(static::$tablename);
		$data = array();
		$conditionString = $condition == null ? '' : $this->buildWhereString($condition, $data);
		$query = "SELECT $fieldList from $joinString $conditionString";
        return $this->query($query, $data);
	}
	/*
		|	this  function is use to resolve any foreign table in a model
		|	@return a proper formal result for a join string statement to be use in query
	*/
    /**
     * @throws Exception
     */
    private function getExportJoinString($mainTable) {
		$tables = $this->getModelTemplateHeader(false, false, $ending);
		//reverse the array
		$temp = $this->extractFk($tables);
		$tables = array_keys($temp);
		array_unshift($tables, $mainTable);
		if (empty($tables)) {
			throw new Exception("error while processing kindly check your code and the model file.", 1);
		}
		$parents = empty($ending) ? array() : array_keys($ending);
		$result = $tables[0];
		$parent = empty($ending) ? $tables[0] : $parents[0]; //initialize the pareent variable
		$count = 0;
		for ($i = 1; $i < count($tables); $i++) {
			$table = $tables[$i];
			if (!empty($ending)) {
				$key = $temp[$table];
				if ($key >= $count) {
					$parent = $this->parentWithMinIndex($key, $ending);
				}
			}
			$result .= " left join $table on $parent.$table" . "_id = $table.id ";
		}
		return $result;
	}

	private function parentWithMinIndex($item, $array) {
		foreach ($array as $key => $value) {
			if ($value > $item) {
				return $key;
			}
		}
	}

	/*
		|	this function is use to return a list of header for the template
		|	@return lists of template header for the model,
		|	resolving any foreign table or an array of tables (i.e multiple table if stated in the entity model)
	*/
	private function getModelTemplateHeader($resolve = true, $isSql = false, &$ending = '') {
		$tables = $this->getTemplateTables();
		if (is_array($tables)) {
			return $this->combineFields($tables, $resolve, $isSql, $ending);
		} else {
			return $this->getTemplates($tables, $resolve, $isSql);
		}
	}

	/*
		|	this function get the necessary table needed for the template
		|	NOTE: if static::uploadDependency is stated in the entity model,
		|	array of table name will be returned
		|	@return either a single table name or an array of table name for the template
	*/
	private function getTemplateTables(): array|string|null
    {
		$tablename = $this->getTableName();
		if (isset(static::$uploadDependency)) {
			$result = array($tablename);
            return array_merge($result, static::$uploadDependency);
		}
		return $tablename;
	}
	/*
		|	this function is use to generate a list of field containing an array of table name
		|	@return a list of array field since it involves an array of table
	*/
	private function combineFields($tables, $resolve = true, $isSql = false, &$ending = ''): array
    {
		$result = array();
		$ending = array();
		$previousCount = 0;
		for ($i = 0; $i < count($tables); $i++) {
			$temp = $this->getTemplates($tables[$i], $resolve, $isSql);
			$ending[$tables[$i]] = count($temp) + $previousCount;
			$previousCount += count($temp);
			$result = array_merge($result, $temp);
		}
		return $result;
	}

	/**
     * This function is to get the field needed for the template download
     * static::$uploadFields contain the fields you want to include only in the template with
     * the name of the fields to be included as the values for the array element
     * @return array
     */
	private function getTemplates($table, $resolve = true, $isSql = false) {
		$fields = '';
		$table = loadClass($table);
		if (isset($table::$uploadFields)) {
			$fields = $table::$uploadFields;
		} else {
			$fields = $this->buildTemplate($table, $resolve, $isSql);
		}
		return $fields;
	}

	/**
	|	this function is used to download model template for batch uploading.
	|	This function is dynamic in nature and not hard-coded.
	|	@return .csv file extension is returned for download
	 */
	public function downloadTemplate($exception = null) {
		$fields = $this->getModelTemplateHeader();
		if ($exception != null) {
			$fields = array_diff($fields, $exception);
		}
		$fields = array_unique($fields);
		$filename = static::$tablename . '_template.csv';
		$content = singleRowToCsvString($fields);
		$header = 'text/csv';
		sendDownload($content, $header, $filename);
	}

	/**
	 * This is to generate the fields to be included in the download template,
	 * provided the static::uploadFields is not stated in the entity file model
	 * It will resolve any foreign key in the model to their respective table.
	 *
	 * @param string $model
	 * @param bool $resolve
	 * @param bool $isSql
	 * @return array
	 */
	private function buildTemplate($model = '', $resolve = true, $isSql = false) {
		if (empty($model)) {
			$model = $this->getTableName();
		}

		if (!is_object($model)) {
			$model = loadClass($model);
		}
		$labels = $model::$labelArray;
		unset($labels['status'], $labels['id'], $labels['date_created']);
		$fields = array_keys($labels);
		if ($resolve && !isset(static::$ignoreTranslation)) {
			for ($i = 0; $i < count($fields); $i++) {
				$current = $fields[$i];
				if (endsWith($current, '_id')) {
					$classname = substr($current, 0, strrpos($current, '_id', -1));
					$classname = loadClass($classname);
					$classname = ucfirst($classname);
					$joiner = '';
					if ($isSql) {
						$joiner = isset($classname::$displayField) ? $classname . '.' : $model . '.';
					}
					$fields[$i] = isset($classname::$displayField) ? $joiner . $classname::$displayField : $joiner . $fields[$i];
				} else {
					if ($isSql) {
						$fields[$i] = $model . '.' . $fields[$i];
					}

				}
				$fields[$i] = trim($fields[$i]);
			}
		}
		return $fields;
	}

	//function that handles uploads that involves multiple tables
	public function uploadMultiples($tables, $fields, $data, &$message = '', $dbObject = null, $translate = false) {
		$tableFields = array(); // will have the same length as that of the real table
		$tableData = array();
		if (!$dbObject) {
			$dbObject = $this->db;
		}
		//think about how to include last insert id and how to undo the reverse
		$dbObject->transBegin();
		for ($i = 0; $i < count($tables); $i++) {
			$table = $tables[$i];
			$tableField = $this->extractTableInfos($table, $fields, $data, $tableData, $message, $translate);
			if (!$tableField) {
				return false;
			}
			$res = $this->uploadSingle($table, $tableField, $tableData, $message, $dbObject, $translate);
			if (!$res) {
				$dbObject->transRollback();
				return false;
			}
		}
		$dbObject->transCommit();
		return true;
	}

    /**
     * This function need to extract the field needed by the model and the corresponding data
     * needed by the  field for insertion. Note that upload single will perform data transformation
     * when inserting the record
     * @param $model
     * @param $fields
     * @param $data
     * @param $tableData
     * @param $message
     * @return array
     */
	private function extractTableInfos($model, $fields, $data, &$tableData, &$message = '') {
		$result = array();
		$indexes = array();
		$defaultFields = $this->buildTemplate($model);
		for ($i = 0; $i < count($defaultFields); $i++) {
			$current = $defaultFields[$i];
			if (($index = array_search($current, $fields)) !== false) {
				$result[] = $current;
				$indexes[] = $index;
			}
		}

		$tableData = copyMultiArrayWithIndex($indexes, $data);
		return $result;
	}

	public function getLastInsertId() {
		$query = "SELECT LAST_INSERT_ID() AS last"; //sud specify the table
		$result = $this->db->query($query);
		$result = $result->getResultArray();
		return $result[0]['last'];

	}

    /**
     * function to get all the total value of item present in  the database
     *
     * @param string|null $queryclause
     * @return int|mixed
     */
	public static function totalCount(?string $queryclause = '') {
		$crud = new Crud();
		return $crud->totalEntityCount(static::$tablename, $queryclause);
	}

	public static function totalSum(string $column, string $queryclause = '') {
		$crud = new Crud();
		return $crud->totalEntitySum(static::$tablename, $column, $queryclause);
	}

	public function removeProp($property) {
		unset($this->array[$property]);
	}
}
