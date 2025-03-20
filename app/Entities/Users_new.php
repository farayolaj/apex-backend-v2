<?php

namespace App\Entities;
use App\Models\Crud;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the users table.
 */
class Users_new extends Crud
{
    /**
     * This is the entity name equivalent to the table name
     * @var string
     */
    protected static $tablename = "Users_new";

    /**
     * This array contains the field that can be null
     * @var array
     */
    public static $nullArray = ['user_type'];

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
    public static $displayField = 'user_type';

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
    public static $typeArray = ['user_login' => 'varchar', 'user_pass' => 'text', 'active' => 'tinyint', 'date_registered' => 'timestamp', 'password' => 'varchar', 'user_table_id' => 'int', 'user_type' => 'enum'];

    /**
     * This is a dictionary that map a field name with the label name that
     * will be shown in a form
     * @var array
     */
    public static $labelArray = ['id' => '', 'user_login' => '', 'user_pass' => '', 'active' => '', 'date_registered' => '', 'password' => '', 'user_table_id' => '', 'user_type' => ''];

    /**
     * Associative array of fields in the table that have default value
     * @var array
     */
    public static $defaultArray = ['date_registered' => 'current_timestamp()', 'user_type' => 'other'];

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
    public static $relation = ['user_table' => ['user_table_id', 'id'],
    ];

    /**
     * This are the action allowed to be performed on the entity and this can
     * be changed in the formConfig model file for flexibility
     * @var array
     */
    public static $tableAction = ['delete' => 'delete/users_new', 'edit' => 'edit/users_new'];

    public function __construct(array $array = [])
    {
        parent::__construct($array);
    }

    public function getTitleFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='title' >Title</label>
		<input type='text' name='title' id='title' value='$value' class='form-control' required />
</div> ";

    }

    public function getStaff_idFormField($value = '')
    {
        $fk = null;

        if (is_null($fk)) {
            return $result = "<input type='hidden' value='$value' name='staff_id' id='staff_id' class='form-control' />
			";
        }
        if (is_array($fk)) {
            $result = "<div class='form-group'>
		<label for='staff_id'>Staff Id</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='staff_id' id='staff_id' class='form-control'>
			$option
		</select>";
        }
        $result .= "</div>";
        return $result;

    }

    public function getFullname()
    {
        return $this->firstname . ' ' . $this->lastname . ' ' . $this->othernames;
    }

    public function getAbbr()
    {
        $first  = $this->firstname[0];
        $second = $this->lastname[0];
        return strtoupper($first . $second);
    }

    public function APIList($filterList, $queryString, $start, $len)
    {
        $temp         = getFilterQueryFromDict($filterList);
        $filterQuery  = $temp[0];
        $filterValues = $temp[1];
        if ($filterQuery || $queryString) {
            $filterQuery .= ($filterQuery ? ' and ' : ' where ') . $queryString;
        }
        $filterQuery .= " order by id desc ";

        if ($len) {
            $start = $this->db->escape($start);
            $len   = $this->db->escape($len);
            $filterQuery .= " limit $start, $len";
        }
        if (! $filterValues) {
            $filterValues = [];
        }

        $query  = "SELECT SQL_CALC_FOUND_ROWS id,title,lastname,firstname,othernames,dob,gender,marital_status,user_phone ,user_email,user_login ,is_lecturer, address, active from users_new join  $filterQuery";
        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res    = $this->db->query($query, $filterValues);
        $res    = $res->getResultArray();
        $res2   = $this->db->query($query2);
        $res2   = $res2->getResultArray();
        return [$res, $res2];
    }

    public function getUserByID($userID, $name = false)
    {
        $query  = "SELECT * from users_new where id = ?";
        $result = $this->query($query, [$userID]);
        if (! $result) {
            return false;
        }

        if ($name) {
            return $result[0]['user_login'];
        }
        return $result[0];
    }

    public function getRealUserInfo($userID, string $table, string $userType)
    {
        $query = "SELECT b.*,a.id as user_id,a.user_login as username from users_new a join {$table} b on b.id = a.user_table_id
    	where a.id = ? and a.user_type = ?";
        $result = $this->query($query, [$userID, $userType]);
        if (! $result) {
            return false;
        }
        return $result[0];
    }

    public function getUserInfo(string $table, string $userType, int $userTableID)
    {
        $query = "SELECT b.*,a.id as user_id,a.user_login as username from users_new a join {$table} b on b.id = a.user_table_id
        where a.user_table_id = ? and a.user_type = ?";
        $result = $this->query($query, [$userTableID, $userType]);
        if (! $result) {
            return false;
        }
        return $result[0];
    }

    public function getUserLog(string $username, bool $few = false)
    {
        if (! $few) {
            $query = "SELECT * from users_log where username = ? order by date_performed desc limit 20";
        } else {
            $query = "SELECT id,username,action_performed,user_agent,user_ip,date_performed from users_log where username = ? order by date_performed desc limit 10";
        }
        return $this->query($query, [$username]);
    }

    public function performed_action(string $username, string $action)
    {
        $query = "SELECT id,username,action_performed,user_agent,user_ip,date_performed from users_log where username = ? and action_performed = ? order by date_performed desc limit 10";
        return $this->query($query, [$username, $action]);
    }

    public function getRequestUserInfo($userID)
    {
        $query = "SELECT A.id, A.user_type,
			CASE
			   WHEN A.user_type = 'staff' THEN B.firstname
			   WHEN A.user_type = 'contractor' THEN C.registered_name
			   ELSE NULL
		   END AS firstname,
		   CASE
			   WHEN A.user_type = 'staff' THEN B.lastname
			   WHEN A.user_type = 'contractor' THEN C.cac_number
			   ELSE NULL
		   END AS lastname FROM users_new A LEFT JOIN staffs B ON A.user_table_id = B.id AND A.user_type = 'staff'
		LEFT JOIN contractors C ON A.user_table_id = C.id AND A.user_type = 'contractor' where A.id = ?";
        return $this->query($query, [$userID]);
    }

    public function getAllUsers()
    {
        $query  = "SELECT b.*,a.id as orig_user_id,a.user_login as username from users_new a join staffs b on b.id = a.user_table_id where a.user_type = 'staff' and a.active = '1'";
        $result = $this->query($query);
        if (! $result) {
            return false;
        }
        return $result;
    }

    public function getUserDetails(object $user)
    {
        $content = [
            'staff' => 'staffs',
            'contractor' => 'contractors',
        ];
        $entity = $user->user_type ?? 'staff';
        $entity = strtolower($entity);
        $entityModel = $content[$entity] ?? null;
        if ($entityModel) {
            $entityModel = loadClass($entityModel);
            $entityModel = $entityModel->getWhere(['id' => $user->user_table_id], $c, 0, null, false);
            if ($entityModel) {
                $entityModel = $entityModel[0];
            }
        }
        return $entityModel;
    }

}
