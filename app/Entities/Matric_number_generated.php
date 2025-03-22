<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the matric_number_generated table.
 */
class Matric_number_generated extends Crud
{
	protected static $tablename = 'Matric_number_generated';
	/* this array contains the field that can be null*/
	static $nullArray = array();
	static $compositePrimaryKey = array();
	static $uploadDependency = array();
	/*this array contains the fields that are unique*/
	static $uniqueArray = array();
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('department_id' => 'int', 'programme_id' => 'int', 'last_generated_number' => 'varchar', 'date_first_inserted' => 'datetime', 'date_updated' => 'datetime');
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'department_id' => '', 'programme_id' => '', 'last_generated_number' => '', 'date_first_inserted' => '', 'date_updated' => '');
	/*associative array of fields that have default value*/
	static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = array('department' => array('department_id', 'ID')
	, 'programme' => array('programme_id', 'ID')
	);
	static $tableAction = array('delete' => 'delete/matric_number_generated', 'edit' => 'edit/matric_number_generated');

	function __construct($array = array())
	{
		parent::__construct($array);
	}

	function getDepartment_idFormField($value = '')
	{
		$fk = null;//change the value of this variable to array('table'=>'department','display'=>'department_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

		if (is_null($fk)) {
			return $result = "<input type='hidden' value='$value' name='department_id' id='department_id' class='form-control' />
			";
		}
		if (is_array($fk)) {
			$result = "<div class='form-group'>
		<label for='department_id'>Department Id</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='department_id' id='department_id' class='form-control'>
			$option
		</select>";
		}
		$result .= "</div>";
		return $result;

	}

	function getProgramme_idFormField($value = '')
	{
		$fk = null;//change the value of this variable to array('table'=>'programme','display'=>'programme_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

		if (is_null($fk)) {
			return $result = "<input type='hidden' value='$value' name='programme_id' id='programme_id' class='form-control' />
			";
		}
		if (is_array($fk)) {
			$result = "<div class='form-group'>
		<label for='programme_id'>Programme Id</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='programme_id' id='programme_id' class='form-control'>
			$option
		</select>";
		}
		$result .= "</div>";
		return $result;

	}

	function getLast_generated_numberFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='last_generated_number' >Last Generated Number</label>
		<input type='text' name='last_generated_number' id='last_generated_number' value='$value' class='form-control' required />
</div> ";

	}

	function getDate_first_insertedFormField($value = '')
	{

		return " ";

	}

	function getDate_updatedFormField($value = '')
	{

		return " ";

	}


	protected function getDepartment()
	{
		$query = 'SELECT * FROM department WHERE id=?';
		if (!isset($this->array['id'])) {
			return null;
		}
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		return new \App\Entities\Department($result[0]);
	}

	protected function getProgramme()
	{
		$query = 'SELECT * FROM programme WHERE id=?';
		if (!isset($this->array['id'])) {
			return null;
		}
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		return new \App\Entities\Programme($result[0]);
	}
}

