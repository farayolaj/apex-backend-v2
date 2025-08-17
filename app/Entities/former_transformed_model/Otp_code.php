<?php

namespace App\Entities;

use App\Models\Crud;
use DateInterval;
use DateTime;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the otp_code table.
 */
class Otp_code extends Crud
{
    protected static $tablename = 'Otp_code';
    /* this array contains the field that can be null*/
    static $nullArray = array('time_generated', 'time_verified', 'status');
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('code' => 'varchar', 'username' => 'varchar', 'time_generated' => 'timestamp', 'time_verified' => 'timestamp', 'status' => 'tinyint');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'code' => '', 'username' => '', 'time_generated' => '', 'time_verified' => '', 'status' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array('time_generated' => 'current_timestamp()', 'status' => '1');
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array();
    static $tableAction = array('enable' => 'getEnabled', 'delete' => 'delete/otp_code', 'edit' => 'edit/otp_code');

    function __construct($array = array())
    {
        parent::__construct($array);
    }

    function getCodeFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='code' >Code</label>
		<input type='text' name='code' id='code' value='$value' class='form-control' required />
</div> ";

    }

    function getUsernameFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='username' >Username</label>
		<input type='text' name='username' id='username' value='$value' class='form-control' required />
</div> ";

    }

    function getTime_generatedFormField($value = '')
    {

        return " ";

    }

    function getTime_verifiedFormField($value = '')
    {

        return " ";

    }

    function getStatusFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Status</label>
	<select class='form-control' id='status' name='status' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

    }

    public function validateToken($username, $token)
    {
        $expiry = new DateInterval('PT2H'); //expires in 30 minutes
        $query = "select *,now() as now_time from otp_code where username=? and code=? and status=1";
        $result = $this->query($query, array($username, $token));
        # check if the token is present
        if (!$result) {
            return false;
        }
        # get the date from the token
        $time_generated = new DateTime($result[0]->time_generated);
        $timeNow = new DateTime($result[0]->now_time);
        $adjustedTime = $time_generated->add($expiry);
        if ($adjustedTime >= $timeNow) {
            $this->updateTokenValidation($token, $username, 0);
            return false;
        }
        if (!$this->updateTokenValidation($token, $username, 0)) {
            return false;
        }
        return true;
    }

    private function updateTokenValidation($token, $username, $status)
    {
        $this->db->escapeString($status);
        $query = "update otp_code set time_verified=current_timestamp,status=$status where code=? and username=?";
        return $this->db->query($query, array($token, $username));
    }


}
