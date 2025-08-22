<?php
require_once('application/models/Crud.php');

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the request_charges table
 */
class Request_charges extends Crud
{

	/**
	 * This is the entity name equivalent to the table name
	 * @var string
	 */
	protected static $tablename = "Request_charges";

	/**
	 * This array contains the field that can be null
	 * @var array
	 */
	public static $nullArray = ['name', 'slug', 'amount', 'updated_at'];

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
	public static $displayField = 'name';

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
	public static $typeArray = ['name' => 'varchar', 'slug' => 'varchar', 'amount' => 'decimal', 'active' => 'tinyint', 'created_at' => 'timestamp', 'updated_at' => 'timestamp'];

	/**
	 * This is a dictionary that map a field name with the label name that
	 * will be shown in a form
	 * @var array
	 */
	public static $labelArray = ['id' => '', 'name' => '', 'slug' => '', 'amount' => '', 'active' => '', 'created_at' => '', 'updated_at' => ''];

	/**
	 * Associative array of fields in the table that have default value
	 * @var array
	 */
	public static $defaultArray = ['active' => '1', 'created_at' => 'current_timestamp()'];

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
	public static $relation = [];

	/**
	 * This are the action allowed to be performed on the entity and this can
	 * be changed in the formConfig model file for flexibility
	 * @var array
	 */
	public static $tableAction = ['delete' => 'delete/request_charges', 'edit' => 'edit/request_charges'];

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

	public function getSlugFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='slug'>Slug</label>
		<input type='text' name='slug' id='slug' value='$value' class='form-control' required />
	</div>";
	}

	public function getAmountFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='amount'>Amount</label>
		<input type='text' name='amount' id='amount' value='$value' class='form-control' required />
	</div>";
	}

	public function getActiveFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='active'>Active</label>
		<input type='text' name='active' id='active' value='$value' class='form-control' required />
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


}

