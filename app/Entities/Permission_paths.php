<?php

namespace App\Entities;

use App\Models\Crud;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the permission_paths table.
 */
class Permission_paths extends Crud
{
    protected static $tablename = 'Permission_paths';
    /* this array contains the field that can be null*/
    static $nullArray = array('time_created', 'active');
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('path' => 'text', 'roles_permission_id' => 'int', 'time_created' => 'timestamp', 'active' => 'tinyint');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'path' => '', 'roles_permission_id' => '', 'time_created' => '', 'active' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array('time_created' => 'current_timestamp()', 'active' => '1');
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array('roles_permission' => array('roles_permission_id', 'ID')
    );
    static $tableAction = array('delete' => 'delete/permission_paths', 'edit' => 'edit/permission_paths');

    function __construct($array = array())
    {
        parent::__construct($array);
    }

    function getPathFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='path' >Path</label>
		<input type='text' name='path' id='path' value='$value' class='form-control' required />
</div> ";

    }

    function getRoles_permission_idFormField($value = '')
    {
        $fk = null;//change the value of this variable to array('table'=>'roles_permission','display'=>'roles_permission_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

        if (is_null($fk)) {
            return $result = "<input type='hidden' value='$value' name='roles_permission_id' id='roles_permission_id' class='form-control' />
			";
        }
        if (is_array($fk)) {
            $result = "<div class='form-group'>
		<label for='roles_permission_id'>Roles Permission Id</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='roles_permission_id' id='roles_permission_id' class='form-control'>
			$option
		</select>";
        }
        $result .= "</div>";
        return $result;

    }

    function getTime_createdFormField($value = '')
    {

        return " ";

    }

    function getActiveFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Active</label>
	<select class='form-control' id='active' name='active' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

    }


    protected function getRoles_permission()
    {
        $query = 'SELECT * FROM roles_permission WHERE id=?';
        if (!isset($this->array['id'])) {
            return null;
        }
        $id = $this->array['id'];
        $result = $this->db->query($query, array($id));
        $result = $result->getResultArray();
        if (empty($result)) {
            return false;
        }
        return new \App\Entities\Roles_permission($result[0]);
    }
}
		