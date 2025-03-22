<?php
namespace App\Entities;

use App\Models\Crud;

/** 
* This class is automatically generated based on the structure of the table.
* And it represent the model of the student_verification_cards table
*/
class Student_verification_cards extends Crud {

/** 
* This is the entity name equivalent to the table name
* @var string
*/
protected static $tablename = "Student_verification_cards"; 

/** 
* This array contains the field that can be null
* @var array
*/
public static $nullArray = ['date_created','date_modified','usage_status'];

/** 
* This are fields that must be unique across a row in a table.
* Similar to composite primary key in sql(oracle,mysql)
* @var array
*/
public static $compositePrimaryKey = [];

/** 
* This is to provided an array of fields that can be used for building a
* template header for batch upload using csv format
* @var array
*/
public static $uploadDependency = [];

/** 
* If there is a relationship between this table and another table, this display field properties is used as a column in the query.
* A field in the other table that displays the connection between this name and this table's name,something along these lines
* table_id. We cannot use a name similar to table id in the table that is displayed to the user, so the display field is used in
* place of it. To ensure that the other model queries use that field name as a column to be fetched with the query rather than the
* table id alone, the display field name provided must be a column in the table to replace the table id shown to the user.
* @var array|string
*/
public static $displayField = '';// this display field properties is used as a column in a query if a their is a relationship between this table and another table.In the other table, a field showing the relationship between this name having the name of this table i.e something like this. table_id. We cant have the name like this in the table shown to the user like table_id so the display field is use to replace that table_id.However,the display field name provided must be a column in the table to replace the table_id shown to the user,so that when the other model queries,it will use that field name as a column to be fetched along the query rather than the table_id alone.;

/** 
* This array contains the fields that are unique
* @var array
*/
public static $uniqueArray = [];

/** 
* This is an associative array containing the fieldname and the datatype
* of the field
* @var array
*/
public static $typeArray = ['student_id' => 'int','verification_cards_id' => 'int','usage_status' => 'tinyint','date_modified' => 'timestamp','date_created' => 'timestamp'];

/** 
* This is a dictionary that map a field name with the label name that
* will be shown in a form
* @var array
*/
public static $labelArray = ['ID' => '','student_id' => '','verification_cards_id' => '','usage_status' => '','date_modified' => '','date_created' => ''];

/** 
* Associative array of fields in the table that have default value
* @var array
*/
public static $defaultArray = ['usage_status' => '0','date_modified' => 'current_timestamp()','date_created' => 'current_timestamp()'];

/** 
*  This is an array containing an associative array of field that should be regareded as document field.
* it will contain the setting for max size and data type. Example: populate this array with fields that
* are meant to be displayed as document in the format
* array('fieldname'=>array('type'=>array('jpeg','jpg','png','gif'),'size'=>'1048576','directory'=>'directoryName/','preserve'=>false,'max_width'=>'1000','max_height'=>'500')).
* the folder to save must represent a path from the basepath. it should be a relative path,preserve
* filename will be either true or false. when true,the file will be uploaded with it default filename
* else the system will pick the current user id in the session as the name of the file 
* @var array
*/
public static $documentField = []; 

/** 
* This is an associative array of fields showing relationship between
* entities
* @var array
*/
public static $relation = ['student' => array('student_id','id')
,'verification_cards' => array('verification_cards_id','id')
];

/** 
* This are the action allowed to be performed on the entity and this can
* be changed in the formConfig model file for flexibility
* @var array
*/
public static $tableAction = ['delete' => 'delete/student_verification_cards', 'edit' => 'edit/student_verification_cards'];

public function __construct(array $array = [])
{
	parent::__construct($array);
}
 
public function getStudent_idFormField($value = ''){
	$fk = null; 

		if(is_null($fk)){
			return $result = "<input type='hidden' name='student_id' id='student_id' value='$value' class='form-control' />";
		}

		if(is_array($fk)){
			
			$result ="<div class='form-group'>
			<label for='student_id'>Student</label>";
			$option = $this->loadOption($fk,$value);
			//load the value from the given table given the name of the table to load and the display field
			$result.="<select name='student_id' id='student_id' class='form-control'>
						$option
					</select>";
					$result.="</div>";
		return $result;
		}
		
}
public function getVerification_cards_idFormField($value = ''){
	$fk = null; 

		if(is_null($fk)){
			return $result = "<input type='hidden' name='verification_cards_id' id='verification_cards_id' value='$value' class='form-control' />";
		}

		if(is_array($fk)){
			
			$result ="<div class='form-group'>
			<label for='verification_cards_id'>Verification Cards</label>";
			$option = $this->loadOption($fk,$value);
			//load the value from the given table given the name of the table to load and the display field
			$result.="<select name='verification_cards_id' id='verification_cards_id' class='form-control'>
						$option
					</select>";
					$result.="</div>";
		return $result;
		}
		
}
public function getUsage_statusFormField($value = ''){
	return "<div class='form-group'>
				<label for='usage_status'>Usage Status</label>
				<input type='text' name='usage_status' id='usage_status' value='$value' class='form-control' required />
			</div>";
} 
public function getDate_modifiedFormField($value = ''){
	return "";
} 
public function getDate_createdFormField($value = ''){
	return "";
} 

protected function getStudent(){
	$query = 'SELECT * FROM student WHERE id=?';
	$result = $this->query($query,[$this->student_id]);
	if (!$result) {
		return false;
	}
	return new \App\Entities\Students($result[0]);
}

protected function getVerification_cards(){
	$query = 'SELECT * FROM verification_cards WHERE id=?';
	$result = $this->query($query,[$this->verification_cards_id]);
	if (!$result) {
		return false;
	}
	return new \App\Entities\Verification_cards($result[0]);
}

public function APIList($filterList, $queryString,$start,$len, $orderBy)
{
	$temp = getFilterQueryFromDict($filterList);
	$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
	$filterValues = $temp[1];

	if(isset($_GET['sortBy']) && $orderBy){
		$filterQuery .= " order by $orderBy ";
	}else{
		$filterQuery .= " order by student_verification_cards.id desc";
	}

	if ($len) {
		$start = $this->db->escape($start);
		$len = $this->db->escape($len);
		$filterQuery.=" limit $start, $len";
	}
	if (!$filterValues) {
		$filterValues = [];
	}

	$tablename = $this->getTableName();
	$query = "SELECT SQL_CALC_FOUND_ROWS concat(students.firstname,' ',students.lastname,' ',students.othernames) as fullname,academic_record.application_number,student_verification_cards.usage_status,verification_cards.serial_number,verification_cards.pin_number,student_verification_cards.date_created as assign_date from student_verification_cards join students on students.id = student_verification_cards.student_id join verification_cards on verification_cards.id = student_verification_cards.verification_cards_id join academic_record on academic_record.student_id = students.id $filterQuery";
	
	$res = $this->apiQueryListCustomFiltered($query, $filterValues);
	return [$res[0], $res[1]];
}

 
}

