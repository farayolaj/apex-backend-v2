<?php

namespace App\Entities;

use App\Libraries\EntityLoader;
use App\Models\Crud;
use App\Models\WebSessionManager;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the roles_permission table.
 */
class Roles_permission extends Crud
{
    protected static $tablename = 'Roles_permission';
    /* this array contains the field that can be null*/
    static $nullArray = array();
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('role_id' => 'text', 'permission' => 'varchar');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'role_id' => '', 'permission' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array();
    static $displayField = 'permission';
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array();
    static $tableAction = array('delete' => 'delete/roles_permission', 'edit' => 'edit/roles_permission');

    public static $apiSelectClause = array('id' => 'id', 'role_id' => 'role_id', 'permission' => 'permission');

    function __construct($array = array())
    {
        parent::__construct($array);
    }

    function getRole_idFormField($value = '')
    {
        $fk = null;//change the value of this variable to array('table'=>'role','display'=>'role_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

        if (is_null($fk)) {
            return $result = "<input type='hidden' value='$value' name='role_id' id='role_id' class='form-control' />
			";
        }
        if (is_array($fk)) {
            $result = "<div class='form-group'>
		<label for='role_id'>Role Id</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='role_id' id='role_id' class='form-control'>
			$option
		</select>";
        }
        $result .= "</div>";
        return $result;

    }

    function getPermissionFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='permission' >Permission</label>
		<input type='text' name='permission' id='permission' value='$value' class='form-control' required />
</div> ";

    }

    private function processPermissionData(string $type, int $userId): array
    {
        $role = $this->getUserRoleId($userId);
        if (!$role) {
            return [];
        }
        $role = array_column($role, 'role_id');

        $query = "SELECT * from roles_permission";
        $query = $this->db->query($query);
        if ($query->getNumRows() <= 0) {
            return [];
        }
        $temp = $query->getResultArray();
        $result = [];
        foreach ($temp as $res) {
            $roles = json_decode($res['role_id'], true);
            if (array_intersect($role, $roles)) {
                $content = [
                    'name' => $res['permission'],
                    'value' => 'r'
                ];
                $result[] = $content;
            }
        }
        return $result;
    }

    public function permissionApexQuery($userId): array
    {
        return $this->processPermissionData('apex', $userId);
    }

    public function permissionQuery($userId): array
    {
        return $this->processPermissionData('admin', $userId);
    }

    /**
     * Check if users has a role permission assigned to them *
     * @param  [type] $userID [description]
     * @return false [type]         [description]
     */
    private function getUserRoleId($userID)
    {
        $query = $this->db->table('roles_user')
            ->where('user_id', $userID)
            ->get();
        if ($query->getNumRows() > 0) {
            return $query->getResultArray();
        }
        return false;
    }

    public function delete($id = null, &$dbObject = null, $type = null): bool
    {
        permissionAccess('role_assign_permission_delete', 'delete');
        $currentUser = WebSessionManager::currentAPIUser();
        $db = $dbObject ?? $this->db;
        if (parent::delete($id, $db)) {
            logAction( 'role_assign_permission_deletion', $currentUser->id, $id);
            return true;
        }
        return false;
    }

    public function APIList($filterList, $queryString, $start, $len, $orderBy): array
    {
        $temp = getFilterQueryFromDict($filterList);
        $filterQuery = buildCustomWhereString($temp[0], $queryString, false);
        $filterValues = $temp[1];

        if (isset($_GET['sortBy']) && $orderBy) {
            $filterQuery .= " order by $orderBy ";
        } else {
            $filterQuery .= " order by a.id desc ";
        }

        if (isset($_GET['start']) && $len) {
            $start = $this->db->escapeString($start);
            $len = $this->db->escapeString($len);
            $filterQuery .= " limit $start, $len";
        }
        if (!$filterValues) {
            $filterValues = [];
        }
        $tablename = strtolower(self::$tablename);
        $query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . " from $tablename a $filterQuery";

        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query, $filterValues);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        $res = $this->processList($res);

        return [$res, $res2];
    }

    private function processList($items)
    {
        EntityLoader::loadClass($this, 'roles');
        for ($i = 0; $i < count($items); $i++) {
            $items[$i] = $this->loadExtras($items[$i]);
        }
        return $items;
    }

    public function loadExtras(array $item, bool $ignoreRole = true): array
    {
        if ($item['role_id']) {
            $roles = json_decode($item['role_id'], true);
            $rolesArr = [];
            foreach ($roles as $role) {
                $temp = $this->roles->getWhere(['id' => $role], $count, 0, null, false);
                if ($temp) {
                    $temp = $temp[0];
                    $rolesArr[] = $temp->name;
                }
            }
            if ($ignoreRole) {
                unset($item['role_id']);
            }
            $item['roles'] = $rolesArr;
        }
        return $item;
    }


}

