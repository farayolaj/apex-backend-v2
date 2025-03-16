<?php

namespace App\Entities;

use App\Models\Crud;

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the voters table
 */
class Voters extends Crud
{

    /**
     * This is the entity name equivalent to the table name
     * @var string
     */
    protected static string $tablename = "Voters";

    /**
     * This array contains the field that can be null
     * @var array
     */
    public static array $nullArray = ['middlename'];

    /**
     * This are fields that must be unique across a row in a table.
     * Similar to composite primary key in sql(oracle,mysql)
     * @var array
     */
    public static array $compositePrimaryKey = [];

    /**
     * This is to provided an array of fields that can be used for building a
     * template header for batch upload using csv format
     * @var array
     */
    public static array $uploadDependency = [];

    /**
     * If there is a relationship between this table and another table, this display field properties is used as a column in the query.
     * A field in the other table that displays the connection between this name and this table's name,something along these lines
     * table_id. We cannot use a name similar to table id in the table that is displayed to the user, so the display field is used in
     * place of it. To ensure that the other model queries use that field name as a column to be fetched with the query rather than the
     * table id alone, the display field name provided must be a column in the table to replace the table id shown to the user.
     * @var array|string
     */
    public static string|array $displayField = 'email';

    /**
     * This array contains the fields that are unique
     * @var array
     */
    public static array $uniqueArray = ['email', 'matric_number'];

    /**
     * This is an associative array containing the fieldname and the datatype
     * of the field
     * @var array
     */
    public static array $typeArray = ['firstname' => 'varchar', 'lastname' => 'varchar', 'middlename' => 'varchar',
        'email' => 'varchar', 'matric_number' => 'varchar', 'status' => 'tinyint', 'created_at' => 'timestamp',
        'updated_at' => 'timestamp','voters_path'=>'varchar'];

    /**
     * This is a dictionary that map a field name with the label name that
     * will be shown in a form
     * @var array
     */
    public static array $labelArray = ['id' => '', 'firstname' => '', 'lastname' => '', 'middlename' => '',
        'email' => '', 'matric_number' => '', 'status' => '', 'created_at' => '', 'updated_at' => '','voters_path'=>''];

    /**
     * Associative array of fields in the table that have default value
     * @var array
     */
    public static array $defaultArray = ['status' => '1', 'created_at' => 'current_timestamp()', 'updated_at' => 'current_timestamp()'];

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
    public static array $documentField = [];

    /**
     * This is an associative array of fields showing relationship between
     * entities
     * @var array
     */
    public static array $relation = [];

    /**
     * This are the action allowed to be performed on the entity and this can
     * be changed in the formConfig model file for flexibility
     * @var array
     */
    public static array $tableAction = ['delete' => 'delete/voters', 'edit' => 'edit/voters'];

    public static array $apiSelectClause = ['id', 'firstname', 'lastname', 'middlename', 'email',
        'matric_number', 'status','voters_path', 'created_at'];

    public function __construct(array $array = [])
    {
        parent::__construct($array);
    }

    public function getFirstnameFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='firstname'>Firstname</label>
		<input type='text' name='firstname' id='firstname' value='$value' class='form-control' required />
	</div>";
    }

    public function getLastnameFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='lastname'>Lastname</label>
		<input type='text' name='lastname' id='lastname' value='$value' class='form-control' required />
	</div>";
    }

    public function getMiddlenameFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='middlename'>Middlename</label>
		<input type='text' name='middlename' id='middlename' value='$value' class='form-control' required />
	</div>";
    }

    public function getEmailFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='email'>Email</label>
		<input type='text' name='email' id='email' value='$value' class='form-control' required />
	</div>";
    }

    public function getMatric_numberFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='matric_number'>Matric Number</label>
		<input type='text' name='matric_number' id='matric_number' value='$value' class='form-control' required />
	</div>";
    }

    public function getStatusFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='status'>Status</label>
		<input type='text' name='status' id='status' value='$value' class='form-control' required />
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

    public function APIList($filterList, $queryString, $start, $len, $orderBy): array
    {
        $selectData = static::$apiSelectClause;
        $temp = $this->apiQueryListFiltered($selectData, $filterList, $queryString, $start, $len, $orderBy);
        $res = $this->processList($temp[0]);
        return [$res, $temp[1]];
    }

    private function processList($items) {
        for ($i = 0; $i < count($items); $i++) {
            $items[$i] = $this->loadExtras($items[$i]);
        }
        return $items;
    }

    private function loadExtras($item): array
    {
        if($item['voters_path'] != null){
            $item['voters_path'] = base_url($item['voters_path']);
        }

        return $item;
    }


}

