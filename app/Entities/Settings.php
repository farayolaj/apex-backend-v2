<?php

namespace App\Entities;

use App\Models\Crud;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the settings table.
 */
class Settings extends Crud
{
    protected static $tablename = 'Settings';
    /* this array contains the field that can be null*/
    static $nullArray = array('settings_value');
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array('settings_name');
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('settings_name' => 'varchar', 'settings_value' => 'longtext', 'active' => 'tinyint');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'settings_name' => '', 'settings_value' => '', 'active' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array();
    static $tableAction = array('delete' => 'delete/settings', 'edit' => 'edit/settings');
    static $apiSelectClause = ['id', 'settings_name', 'settings_value'];

    function __construct($array = array())
    {
        parent::__construct($array);
    }

    function getSettings_nameFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='settings_name' >Settings Name</label>
		<input type='text' name='settings_name' id='settings_name' value='$value' class='form-control' required />
</div> ";

    }

    function getSettings_valueFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='settings_value' >Settings Value</label>
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


    public function registerSettings($settings)
    {
        foreach ($settings as $settingsKey => $settingsValue) {
            $insertValue = array('settings_name' => $settingsKey, 'settings_value' => $settingsValue);

            if ($this->getSetting($settingsKey)) {
                $this->db->table('settings_name')
                ->where('settings_name', $settingsKey)
                ->update($insertValue);
            } else {
                $this->db->table('settings_name')->insert($insertValue);
            }
        }
    }

    public function getSetting($check_field)
    {
        $query = $this->db->table('settings')
            ->where('settings_name', $check_field)
            ->get();

        foreach ($query->getResultArray() as $row) {
            if ($row['settings_name'] == $check_field) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    public function getInstitiutionLogo($logo)
    {
        $query = $this->db->table('settings')
            ->where('settings_name', $logo)
            ->get();

        foreach ($query->getResultArray() as $row) {
            if ($row['settings_value'] != '') {
                return $row['settings_value'];
            } else {
                return '';
            }
        }
    }


    public function APIList($filterList, $queryString, $start, $len)
    {
        $temp = getFilterQueryFromDict($filterList);
        $filterQuery = $temp[0];
        $filterValues = $temp[1];
        if ($filterQuery || $queryString) {
            $filterQuery .= ($filterQuery ? ' and ' : ' where ') . $queryString;
        }
        $filterQuery .= " order by id desc ";

        if ($len && isset($_GET['start'])) {
            $start = $this->db->escapeString($start);
            $len = $this->db->escapeString($len);
            $filterQuery .= " limit $start, $len";
        }

        if (!$filterValues) {
            $filterValues = [];
        }

        $tablename = strtolower(self::$tablename);
        $query = "SELECT " . buildApiClause(static::$apiSelectClause, $tablename) . " from $tablename $filterQuery";
        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query, $filterValues);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        $res = $this->processList($res);

        return [$res, count($res)];
    }

    private function processList($items)
    {
        $result = [];
        for ($i = 0; $i < count($items); $i++) {
            if ($items[$i]['settings_name'] == 'remita_merchant_id' || $items[$i]['settings_name'] == 'remita_api_key' || $items[$i]['settings_name'] == 'remita_public_key' || $items[$i]['settings_name'] == 'remita_secret_key') {
                continue;
            }
            $result[] = $this->loadExtras($items[$i]);
        }
        return $result;
    }

    private function loadExtras($item)
    {
        if ($item['settings_name'] == 'matric_level_filter') {
            $item['settings_value'] = json_decode($item['settings_value'], true);
        }

        if ($item['settings_name'] == 'matric_entry_mode_filter') {
            $item['settings_value'] = json_decode($item['settings_value'], true);
        }

        if ($item['settings_name'] == 'matric_level_to_include') {
            $item['settings_value'] = json_decode($item['settings_value'], true);
        }

        if ($item['settings_name'] == 'matric_entry_mode_to_include') {
            $item['settings_value'] = json_decode($item['settings_value'], true);
        }

        if ($item['settings_name'] == 'institution_logo') {
            $item['settings_value'] = (strpos($item['settings_value'], 'localhost:8081') !== false) ? base_url('assets/images/' . $item['settings_value']) : 'https://apex.ui.edu.ng/assets/images/' . $item['settings_value'];
        }

        return $item;
    }


}