<?php

namespace App\Entities;

use App\Models\Crud;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the transaction_status_codes table.
 */
class Transaction_status_codes extends Crud
{
    protected static $tablename = 'Transaction_status_codes';
    /* this array contains the field that can be null*/
    static $nullArray = array();
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('status_code' => 'varchar', 'status_code_description_text' => 'varchar', 'status_code_description_html' => 'varchar');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'status_code' => '', 'status_code_description_text' => '', 'status_code_description_html' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array();
    static $tableAction = array('delete' => 'delete/transaction_status_codes', 'edit' => 'edit/transaction_status_codes');

    function __construct($array = array())
    {
        parent::__construct($array);
    }

    function getStatus_codeFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='status_code' >Status Code</label>
		<input type='text' name='status_code' id='status_code' value='$value' class='form-control' required />
</div> ";

    }

    function getStatus_code_description_textFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='status_code_description_text' >Status Code Description Text</label>
		<input type='text' name='status_code_description_text' id='status_code_description_text' value='$value' class='form-control' required />
</div> ";

    }

    function getStatus_code_description_htmlFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='status_code_description_html' >Status Code Description Html</label>
		<input type='text' name='status_code_description_html' id='status_code_description_html' value='$value' class='form-control' required />
</div> ";

    }


}
		