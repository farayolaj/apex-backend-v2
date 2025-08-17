<?php

namespace App\Entities;

use App\Models\Crud;

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the student_payment_bookstore_items table
 */
class Student_payment_bookstore_items extends Crud
{

    /**
     * This is the entity name equivalent to the table name
     * @var string
     */
    protected static $tablename = "Student_payment_bookstore_items";

    /**
     * This array contains the field that can be null
     * @var array
     */
    public static $nullArray = ['course_id', 'amount', 'mainaccount_amount', 'subaccount_amount', 'created_at', 'price'];

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
    public static $displayField = 'course_id';

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
    public static $typeArray = ['student_payment_bookstore_id' => 'int', 'payment_bookstore_id' => 'int', 'title' => 'varchar', 'course_id' => 'int', 'quantity' => 'int', 'amount' => 'decimal', 'payment_id' => 'int', 'mainaccount_amount' => 'decimal', 'subaccount_amount' => 'decimal', 'created_at' => 'timestamp', 'price' => 'decimal'];

    /**
     * This is a dictionary that map a field name with the label name that
     * will be shown in a form
     * @var array
     */
    public static $labelArray = ['id' => '', 'student_payment_bookstore_id' => '', 'payment_bookstore_id' => '', 'title' => '', 'course_id' => '', 'quantity' => '', 'amount' => '', 'payment_id' => '', 'mainaccount_amount' => '', 'subaccount_amount' => '', 'created_at' => '', 'price' => ''];

    /**
     * Associative array of fields in the table that have default value
     * @var array
     */
    public static $defaultArray = ['quantity' => '0', 'created_at' => 'current_timestamp()'];

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
    public static $relation = ['student_payment_bookstore' => array('student_payment_bookstore_id', 'id')
        , 'payment_bookstore' => array('payment_bookstore_id', 'id')
        , 'course' => array('course_id', 'id')
        , 'payment' => array('payment_id', 'id')
    ];

    /**
     * This are the action allowed to be performed on the entity and this can
     * be changed in the formConfig model file for flexibility
     * @var array
     */
    public static $tableAction = ['delete' => 'delete/student_payment_bookstore_items', 'edit' => 'edit/student_payment_bookstore_items'];

    public function __construct(array $array = [])
    {
        parent::__construct($array);
    }

    public function getStudent_payment_bookstore_idFormField($value = '')
    {
        $fk = null;
        //change the value of this variable to array('table'=>'student_payment_bookstore','display'=>'student_payment_bookstore_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'student_payment_bookstore_name' as value from 'student_payment_bookstore' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('student_payment_bookstore', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

        if (is_null($fk)) {
            return $result = "<input type='hidden' name='student_payment_bookstore_id' id='student_payment_bookstore_id' value='$value' class='form-control' />";
        }

        if (is_array($fk)) {

            $result = "<div class='form-group'>
		<label for='student_payment_bookstore_id'>Student Payment Bookstore</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='student_payment_bookstore_id' id='student_payment_bookstore_id' class='form-control'>
					$option
				</select>";
            $result .= "</div>";
            return $result;
        }

    }

    public function getPayment_bookstore_idFormField($value = '')
    {
        $fk = null;
        //change the value of this variable to array('table'=>'payment_bookstore','display'=>'payment_bookstore_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'payment_bookstore_name' as value from 'payment_bookstore' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('payment_bookstore', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

        if (is_null($fk)) {
            return $result = "<input type='hidden' name='payment_bookstore_id' id='payment_bookstore_id' value='$value' class='form-control' />";
        }

        if (is_array($fk)) {

            $result = "<div class='form-group'>
		<label for='payment_bookstore_id'>Payment Bookstore</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='payment_bookstore_id' id='payment_bookstore_id' class='form-control'>
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

    public function getCourse_idFormField($value = '')
    {
        $fk = null;
        //change the value of this variable to array('table'=>'course','display'=>'course_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'course_name' as value from 'course' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('course', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

        if (is_null($fk)) {
            return $result = "<input type='hidden' name='course_id' id='course_id' value='$value' class='form-control' />";
        }

        if (is_array($fk)) {

            $result = "<div class='form-group'>
		<label for='course_id'>Course</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='course_id' id='course_id' class='form-control'>
					$option
				</select>";
            $result .= "</div>";
            return $result;
        }

    }

    public function getQuantityFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='quantity'>Quantity</label>
		<input type='text' name='quantity' id='quantity' value='$value' class='form-control' required />
	</div>";
    }

    public function getAmountFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='amount'>Amount</label>
		<input type='text' name='amount' id='amount' value='$value' class='form-control' required />
	</div>";
    }

    public function getPayment_idFormField($value = '')
    {
        $fk = null;
        //change the value of this variable to array('table'=>'payment','display'=>'payment_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'payment_name' as value from 'payment' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('payment', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

        if (is_null($fk)) {
            return $result = "<input type='hidden' name='payment_id' id='payment_id' value='$value' class='form-control' />";
        }

        if (is_array($fk)) {

            $result = "<div class='form-group'>
		<label for='payment_id'>Payment</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='payment_id' id='payment_id' class='form-control'>
					$option
				</select>";
            $result .= "</div>";
            return $result;
        }

    }

    public function getMainaccount_amountFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='mainaccount_amount'>Mainaccount Amount</label>
		<input type='text' name='mainaccount_amount' id='mainaccount_amount' value='$value' class='form-control' required />
	</div>";
    }

    public function getSubaccount_amountFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='subaccount_amount'>Subaccount Amount</label>
		<input type='text' name='subaccount_amount' id='subaccount_amount' value='$value' class='form-control' required />
	</div>";
    }

    public function getCreated_atFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='created_at'>Created At</label>
		<input type='text' name='created_at' id='created_at' value='$value' class='form-control' required />
	</div>";
    }

    public function getPriceFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='price'>Price</label>
		<input type='text' name='price' id='price' value='$value' class='form-control' required />
	</div>";
    }


    protected function getStudent_payment_bookstore()
    {
        $query = 'SELECT * FROM student_payment_bookstore WHERE id=?';
        if (!isset($this->array['id'])) {
            return null;
        }
        $id = $this->array['id'];
        $result = $this->query($query, [$id]);
        if (!$result) {
            return false;
        }
        $resultObject = new Student_payment_bookstore($result[0]);
        return $resultObject;
    }

    protected function getPayment_bookstore()
    {
        $query = 'SELECT * FROM payment_bookstore WHERE id=?';
        if (!isset($this->array['id'])) {
            return null;
        }
        $id = $this->array['id'];
        $result = $this->query($query, [$id]);
        if (!$result) {
            return false;
        }
        $resultObject = new Payment_bookstore($result[0]);
        return $resultObject;
    }

    protected function getCourse()
    {
        $query = 'SELECT * FROM course WHERE id=?';
        if (!isset($this->array['id'])) {
            return null;
        }
        $id = $this->array['id'];
        $result = $this->query($query, [$id]);
        if (!$result) {
            return false;
        }
        $resultObject = new Course($result[0]);
        return $resultObject;
    }


}
