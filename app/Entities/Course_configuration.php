<?php

namespace App\Entities;

use App\Models\Crud;
use App\Models\WebSessionManager;
use App\Support\DTO\ApiListParams;
use CodeIgniter\Database\BaseBuilder;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the course_configuration table.
 */
class Course_configuration extends Crud
{
    protected static $tablename = 'Course_configuration';
    /* this array contains the field that can be null*/
    static $nullArray = array();
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('programme_id' => 'int', 'semester' => 'int', 'level' => 'int', 'entry_mode' => 'varchar', 'min_unit' => 'int', 'max_unit' => 'int', 'enable_reg' => 'tinyint', 'date_created' => 'datetime');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'programme_id' => '', 'semester' => '', 'level' => '', 'entry_mode' => '', 'min_unit' => '', 'max_unit' => '', 'enable_reg' => '', 'date_created' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array('programme' => array('programme_id', 'ID')
    );
    static $tableAction = array('delete' => 'delete/course_configuration', 'edit' => 'edit/course_configuration');

    protected ?string $hooksEntity = 'Course_configuration';

    protected ?string $updatedField = null;

    protected array $searchable = ['b.name'];

    function __construct($array = array())
    {
        parent::__construct($array);
    }

    protected function getSemesters()
    {
        $query = 'SELECT * FROM semesters WHERE id=?';
        if (!isset($this->array['semester'])) {
            return null;
        }
        $id = $this->array['semester'];
        $result = $this->db->query($query, array($id));
        $result = $result->getResultArray();
        if (empty($result)) {
            return false;
        }
        return new \App\Entities\Semesters($result[0]);
    }

    protected function getProgramme()
    {
        $query = 'SELECT * FROM programme WHERE id=?';
        if (!isset($this->array['id'])) {
            return null;
        }
        $id = $this->array['id'];
        $result = $this->db->query($query, array($id));
        $result = $result->getResultArray();
        if (empty($result)) {
            return false;
        }
        return new \App\Entities\Programme($result[0]);
    }

    public function registrationIsOpened($programme, $level, $entry_mode, $semester = false)
    {
        $courseSemester = ($semester && $semester == 'first') ? 1 : 2;
        if ($semester && $courseSemester != get_setting('active_semester')) {
            return false;
        }
        $param = [
            'programme_id' => $programme,
            'level' => $level
        ];
        if ($semester) {
            $param['semester'] = $courseSemester;
        }
        $item = $this->getWhere($param, $c, 0, null, false);
        if (!$item) {
            return true;
        }
        $item = $item[0];
        return $item->enable_reg;
    }

    // TODO: REMEMBER TO REMOVE ALWAYS TRUE VALUE RETURN
    // WHILE WE WAIT TO IMPLEMENT PROFILE UPDATE
    public function isPassportCheckValid($student)
    {
        return true;
        $passportPath = 'assets/images/students/passports/';
        $setting = trim(get_setting('force_course_reg_image_upload'));
        if (!$setting) {
            return true;
        }

        if (!trim($student->passport)) {
            return false;
        }
        $path = $passportPath . $student->passport;
        if (file_exists($path)) {
            return true;
        }
        return false;
    }

    public function delete($id = null, &$dbObject = null, $type = null): bool
    {
        permissionAccess('course_config_delete', 'delete');
        $currentUser = WebSessionManager::currentAPIUser();
        $db = $dbObject ?? $this->db;
        if (parent::delete($id, $db)) {
            logAction( 'course_config_delete', $currentUser->user_login);
            return true;
        }
        return false;
    }

    protected function baseBuilder(): BaseBuilder
    {
        return $this->db->table('course_configuration a')
            ->join('programme b', 'b.id = a.programme_id');
    }

    public function defaultSelect(): string|array
    {
        return "a.*, b.name as programme_name";
    }

    protected function applyDefaultOrder(BaseBuilder $builder): void
    {
        $builder->orderBy('b.name', 'asc');
    }

}
