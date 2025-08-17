<?php

namespace App\Entities;

use App\Models\Crud;
use Config\Services;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the templates table.
 */
class Templates extends Crud
{
    protected static $tablename = 'Templates';
    /* this array contains the field that can be null*/
    static $nullArray = array('date_added');
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('name' => 'varchar', 'slug' => 'varchar', 'type' => 'varchar', 'content' => 'longtext', 'active' => 'tinyint', 'date_added' => 'datetime');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'name' => '', 'slug' => '', 'type' => '', 'content' => '', 'active' => '', 'date_added' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array();
    static $tableAction = array('delete' => 'delete/templates', 'edit' => 'edit/templates');

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

    function getSlugFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='slug' >Slug</label>
		<input type='text' name='slug' id='slug' value='$value' class='form-control' required />
</div> ";

    }

    function getTypeFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='type' >Type</label>
		<input type='text' name='type' id='type' value='$value' class='form-control' required />
</div> ";

    }

    function getContentFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='content' >Content</label>
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

    function getDate_addedFormField($value = '')
    {

        return " ";

    }

    public function getMessagingTemplate(string $template, array $variables)
    {
        $parser = Services::parser();
        $temp = $this->getWhere(array('slug' => $template, 'active' => '1'), $count, 0, null, false);
        if ($temp) {
            foreach ($temp as $tmp) {
                $message = base64_decode($tmp->content);
                $message = $parser->setData($variables)->renderString($message);
                return $message;
            }
        }
    }


}
	