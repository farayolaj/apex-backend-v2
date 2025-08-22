<?php

namespace App\Entities;

use App\Enums\UserOutflowTypeEnum as UserOutflowType;
use App\Models\Crud;
use App\Models\WebSessionManager;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the roles table.
 */
class Roles extends Crud
{
    protected static $tablename = 'Roles';
    /* this array contains the field that can be null*/
    static $nullArray = array();
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('name' => 'varchar', 'active' => 'tinyint');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'name' => '', 'active' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array(); //array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array();
    static $tableAction = array('delete' => 'delete/roles', 'edit' => 'edit/roles');

    public static $apiSelectClause = array('id' => 'id', 'name' => 'name', 'active' => 'active');

    function __construct($array = array())
    {
        parent::__construct($array);
    }

    function getNameFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='name' >Name</label>
		<input type='text' name='name' id='name' value='$value' class='form-control' required />
</div> ";

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

    public function getUserRole($user_id)
    {
        $query = "SELECT b.* FROM `roles_user` a join roles b on b.id = a.role_id WHERE a.user_id = ?";
        $result = $this->query($query, array($user_id));
        if (!$result) {
            return false;
        }
        return $result[0];
    }

    public function getDbUser(): array
    {
        $query = "SELECT d.title,d.firstname,d.lastname,d.othernames,d.staff_id,a.id as user_id,d.avatar FROM `users_new` a 
		join staffs d on d.id = a.user_table_id WHERE a.user_type = ? and d.outflow_slug = ? and d.active = ?";
        $result = $this->query($query, ['staff', UserOutflowType::DB_STAFF->value, '1']);
        if (!$result) {
            return [];
        }
        $payload = [];
        foreach ($result as $row) {
            $temp = $row;
            $temp['avatar'] = $row['avatar'] ? site_url($this->config->item('user_passport_path') . $row['avatar']) : null;
            $payload[] = $temp;
        }
        return $payload;
    }

    public function delete($id = null, &$dbObject = null, $type = null): bool
    {
        permissionAccess('role_delete', 'delete');
        $currentUser = WebSessionManager::currentAPIUser();
        $db = $dbObject ?? $this->db;
        if (parent::delete($id, $db)) {
            logAction( 'role_deletion', $currentUser->id, $id);
            return true;
        }
        return false;
    }

    public function APIList($filterList, $queryString, $start, $len): array
    {
        permissionAccess('role_listing', 'view');
        $selectData = static::$apiSelectClause;
        return $this->apiQueryListFiltered($selectData, $filterList, $queryString, $start, $len);
    }

}
