<?php

namespace App\Entities;

use App\Models\Crud;

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the users_custom table
 */
class Users_custom extends Crud
{

    /**
     * This is the entity name equivalent to the table name
     * @var string
     */
    protected static $tablename = "Users_custom";

    /**
     * This array contains the field that can be null
     * @var array
     */
    public static $nullArray = ['date_registered'];

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
    public static $displayField = 'email';

    /**
     * This array contains the fields that are unique
     * @var array
     */
    public static $uniqueArray = ['email'];

    /**
     * This is an associative array containing the fieldname and the datatype
     * of the field
     * @var array
     */
    public static $typeArray = ['name' => 'varchar', 'email' => 'varchar', 'phone_number' => 'varchar', 'address' => 'varchar', 'contact_person' => 'varchar', 'active' => 'tinyint', 'date_registered' => 'timestamp'];

    /**
     * This is a dictionary that map a field name with the label name that
     * will be shown in a form
     * @var array
     */
    public static $labelArray = ['id' => '', 'name' => '', 'email' => '', 'phone_number' => '', 'address' => '', 'contact_person' => '', 'active' => '', 'date_registered' => ''];

    /**
     * Associative array of fields in the table that have default value
     * @var array
     */
    public static $defaultArray = ['active' => '1', 'date_registered' => 'current_timestamp()'];

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
    public static $relation = [
    ];

    /**
     * This are the action allowed to be performed on the entity and this can
     * be changed in the formConfig model file for flexibility
     * @var array
     */
    public static $tableAction = ['delete' => 'delete/users_custom', 'edit' => 'edit/users_custom'];

    public function __construct(array $array = [])
    {
        parent::__construct($array);
    }

    public function getNameFormField($value = '')
    {
        return "<div class='form-group'>
				<label for='name'>Name</label>
				<input type='text' name='name' id='name' value='$value' class='form-control' required />
			</div>";
    }

    public function getEmailFormField($value = '')
    {
        return "<div class='form-group'>
				<label for='email'>Email</label>
				<input type='text' name='email' id='email' value='$value' class='form-control' required />
			</div>";
    }

    public function getPhone_numberFormField($value = '')
    {
        return "<div class='form-group'>
				<label for='phone_number'>Phone Number</label>
				<input type='text' name='phone_number' id='phone_number' value='$value' class='form-control' required />
			</div>";
    }

    public function getAddressFormField($value = '')
    {
        return "<div class='form-group'>
				<label for='address'>Address</label>
				<input type='text' name='address' id='address' value='$value' class='form-control' required />
			</div>";
    }

    public function getContact_personFormField($value = '')
    {
        return "<div class='form-group'>
				<label for='contact_person'>Contact Person</label>
				<input type='text' name='contact_person' id='contact_person' value='$value' class='form-control' required />
			</div>";
    }

    public function getActiveFormField($value = '')
    {
        return "<div class='form-group'>
				<label for='active'>Active</label>
				<input type='text' name='active' id='active' value='$value' class='form-control' required />
			</div>";
    }

    public function getDate_registeredFormField($value = '')
    {
        return "<div class='form-group'>
				<label for='date_registered'>Date Registered</label>
				<input type='text' name='date_registered' id='date_registered' value='$value' class='form-control' required />
			</div>";
    }


    public function APIList($filterList, $queryString, $start, $len, $orderBy): array
    {
        $temp = getFilterQueryFromDict($filterList);
        $filterQuery = buildCustomWhereString($temp[0], $queryString, false);
        $filterValues = $temp[1];

        if (isset($_GET['sortBy']) && $orderBy) {
            $filterQuery .= " order by $orderBy ";
        } else {
            $filterQuery .= " order by date_registered asc ";
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
        $query = "SELECT SQL_CALC_FOUND_ROWS * from $tablename $filterQuery";

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
        $generator = useGenerators($items);
        $payload = [];
        foreach ($generator as $item) {
            $payload[] = $this->loadExtras($item);
        }
        return $payload;
    }

    public function loadExtras($item)
    {
        if (isset($item['phone_number'])) {
            $item['phone_number'] = decryptData($item['phone_number']);
        }

        return $item;
    }


}

