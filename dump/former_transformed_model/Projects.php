<?php

namespace App\Entities;

use App\Models\Crud;
use App\Models\WebSessionManager;

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the projects table
 */
class Projects extends Crud
{

    /**
     * This is the entity name equivalent to the table name
     * @var string
     */
    protected static $tablename = "Projects";

    /**
     * This array contains the field that can be null
     * @var array
     */
    public static $nullArray = ['project_status', 'date_initiated', 'date_completed', 'updated_at', 'created_at'];

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
    public static $displayField = 'project_status';

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
    public static $typeArray = ['users_id' => 'int', 'title' => 'varchar', 'description' => 'text', 'project_status' => 'enum', 'date_initiated' => 'timestamp', 'date_completed' => 'timestamp', 'created_at' => 'timestamp', 'updated_at' => 'timestamp'];

    /**
     * This is a dictionary that map a field name with the label name that
     * will be shown in a form
     * @var array
     */
    public static $labelArray = ['id' => '', 'users_id' => '', 'title' => '', 'description' => '', 'project_status' => '', 'date_initiated' => '', 'date_completed' => '', 'created_at' => '', 'updated_at' => ''];

    /**
     * Associative array of fields in the table that have default value
     * @var array
     */
    public static $defaultArray = ['project_status' => 'pending', 'created_at' => 'current_timestamp()'];

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
    public static $relation = ['users' => array('users_id', 'id')
    ];

    /**
     * This are the action allowed to be performed on the entity and this can
     * be changed in the formConfig model file for flexibility
     * @var array
     */
    public static $tableAction = ['delete' => 'delete/projects', 'edit' => 'edit/projects'];

    public static $apiSelectClause = ['id', 'title', 'description', 'project_status', 'date_initiated', 'date_completed',
        'created_at', 'updated_at'];

    public function __construct(array $array = [])
    {
        parent::__construct($array);
    }

    public function getUsers_idFormField($value = '')
    {
        $fk = null;
        //change the value of this variable to array('table'=>'users','display'=>'users_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'users_name' as value from 'users' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('users', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

        if (is_null($fk)) {
            return $result = "<input type='hidden' name='users_id' id='users_id' value='$value' class='form-control' />";
        }

        if (is_array($fk)) {

            $result = "<div class='form-group'>
		<label for='users_id'>Users</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='users_id' id='users_id' class='form-control'>
					$option
				</select>";
            $result .= "</div>";
            return $result;
        }

    }

    public function getTitleFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='title'>Title</label>
		<input type='text' name='title' id='title' value='$value' class='form-control' required />
	</div>";
    }

    public function getDescriptionFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='description'>Description</label>
		<input type='text' name='description' id='description' value='$value' class='form-control' required />
	</div>";
    }

    public function getProject_statusFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='project_status'>Project Status</label>
		<input type='text' name='project_status' id='project_status' value='$value' class='form-control' required />
	</div>";
    }

    public function getDate_initiatedFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='date_initiated'>Date Initiated</label>
		<input type='text' name='date_initiated' id='date_initiated' value='$value' class='form-control' required />
	</div>";
    }

    public function getDate_completedFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='date_completed'>Date Completed</label>
		<input type='text' name='date_completed' id='date_completed' value='$value' class='form-control' required />
	</div>";
    }

    public function getCreated_atFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='created_at'>Created At</label>
		<input type='text' name='created_at' id='created_at' value='$value' class='form-control' required />
	</div>";
    }

    public function getUpdated_atFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='updated_at'>Updated At</label>
		<input type='text' name='updated_at' id='updated_at' value='$value' class='form-control' required />
	</div>";
    }


    protected function getUsers()
    {
        $query = 'SELECT * FROM users_news WHERE id=?';
        if (!isset($this->array['id'])) {
            return null;
        }
        $id = $this->array['id'];
        $result = $this->query($query, [$id]);
        if (!$result) {
            return false;
        }
        return new \App\Entities\Users_new($result[0]);
    }

    public function APIList($filterList, $queryString, $start, $len, $orderBy): array
    {
        $temp = getFilterQueryFromDict($filterList);
        $filterQuery = buildCustomWhereString($temp[0], $queryString, false);
        $filterValues = $temp[1];
        $currentUser = WebSessionManager::currentAPIUser();
        $proceed = false;

        if (isset($_GET['project_type']) && $_GET['project_type'] === 'contractors') {
            $proceed = true;
            $filterQuery .= ($filterQuery ? " and " : " where ") . " a.users_id='{$currentUser->id}' ";
        } else if (isset($_GET['project_type']) && $_GET['project_type'] === 'admin') {
            $proceed = true;
        } else if (isset($_GET['project_type']) && $_GET['project_type'] === 'directors') {
            $proceed = true;
        }

        if (isset($_GET['sortBy']) && $orderBy) {
            $filterQuery .= " order by $orderBy ";
        } else {
            $filterQuery .= " order by created_at desc ";
        }

        if (isset($_GET['start']) && $len) {
            $start = $this->db->escapeString($start);
            $len = $this->db->escapeString($len);
            $filterQuery .= " limit $start, $len";
        }
        if (!$filterValues) {
            $filterValues = [];
        }

        if ($proceed) {
            return $this->apiListQuery($filterQuery, $filterValues, 'project');
        }
        return [];
    }

    public function APIListProjects($filterList, $queryString, $start, $len, $orderBy): array
    {
        $temp = getFilterQueryFromDict($filterList);
        $filterQuery = buildCustomWhereString($temp[0], $queryString, false);
        $filterValues = $temp[1];

        if (isset($_GET['sortBy']) && $orderBy) {
            $filterQuery .= " order by $orderBy ";
        } else {
            $filterQuery .= " order by a.created_at asc ";
        }

        if (isset($_GET['start']) && $len) {
            $start = $this->db->escapeString($start);
            $len = $this->db->escapeString($len);
            $filterQuery .= " limit $start, $len";
        }
        if (!$filterValues) {
            $filterValues = [];
        }

        return $this->apiListQuery($filterQuery, $filterValues, 'project-title');
    }

    private function apiListQuery(string $filterQuery, ?array $filterValues, string $queryType = 'project'): array
    {
        $tablename = strtolower(self::$tablename);
        if ($queryType === 'project') {
            $query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . " from $tablename a $filterQuery";
        } else if ($queryType === 'project-title') {
            $query = "SELECT a.id, a.title,a.description from $tablename a join project_tasks b on a.id = b.project_id 
			join users_new c on c.id = b.assign_to join contractors d on d.id = c.user_table_id $filterQuery";
        }

        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query, $filterValues);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        return [$res, $res2];
    }


}
