<?php

namespace App\Entities;

use App\Models\Crud;
use App\Support\DTO\ApiListParams;
use CodeIgniter\Database\BaseBuilder;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the course_mapping table.
 */
class Course_mapping extends Crud
{
    protected static $tablename = 'Course_mapping';
    /* this array contains the field that can be null*/
    static $nullArray = array();
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('course_id' => 'int', 'programme_id' => 'int', 'semester' => 'int', 'course_unit' => 'int',
        'course_status' => 'varchar', 'level' => 'text', 'mode_of_entry' => 'text', 'pass_score' => 'int',
        'pre_select' => 'tinyint');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'course_id' => '', 'programme_id' => '', 'semester' => '', 'course_unit' => '',
        'course_status' => '', 'level' => '', 'mode_of_entry' => '', 'pass_score' => '', 'pre_select' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array(); //array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array('course' => array('course_id', 'ID')
    , 'programme' => array('programme_id', 'ID'),
    );
    static $tableAction = array('delete' => 'delete/course_mapping', 'edit' => 'edit/course_mapping');

    protected ?string $hooksEntity = 'Course_mapping';

    protected bool $useTimestamps = false;

    protected array $searchable = ['c.name', 'b.code'];

    function __construct($array = array())
    {
        parent::__construct($array);
    }

    /**
     * This load courses based on programme and semester in which the
     * result is filtered by student level according to mapped level
     * @param $programme_id
     * @param $level
     * @param $entryMode
     * @param string $semester [description]
     * @return array [type] [description]
     */
    public function getCourseLists($programme_id, $level, $entryMode, $semester): array
    {
        $query = "SELECT courses.id as main_course_id, courses.code, courses.title, courses.description, courses.course_guide_url, courses.active,
        course_mapping.id as course_mapping_id, course_mapping.programme_id, course_mapping.semester, course_mapping.level, course_mapping.course_unit, 
        course_mapping.course_status,course_manager.course_lecturer_id, staffs.firstname, staffs.othernames, staffs.lastname, course_mapping.pre_select 
        from courses left join course_mapping on course_mapping.course_id = courses.id  left join course_manager on course_manager.course_id = courses.id 
        left join users_new on users_new.id = course_manager.course_lecturer_id left join staffs on staffs.id=users_new.user_table_id 
        where course_mapping.programme_id = ? and course_mapping.semester = ? and 
        course_mapping.mode_of_entry = ? and courses.active = '1' order by courses.code asc ";
        $courses = $this->query($query, [$programme_id, $semester, $entryMode]);
        $result = [];
        if (!$courses) {
            return $result;
        }

        foreach ($courses as $courseData) {
            $mappedLevel = json_decode($courseData['level'], true);
            if (is_array($mappedLevel) && in_array($level, $mappedLevel)) {
                unset($courseData['level']);
                $courseData['semester'] = (int)$courseData['semester'];
                $courseData['course_unit'] = (int)$courseData['course_unit'];
                $courseData['pre_select'] = (int)$courseData['pre_select'];
                $result[] = $courseData;
            }
        }
        return $result;
    }

    public function searchCourseLists($course, $level, $semester, $programme = null): array
    {
        $query = "SELECT courses.id as main_course_id, courses.code, courses.title, courses.description, courses.course_guide_url, 
       courses.active,course_mapping.id as course_mapping_id, course_mapping.programme_id, course_mapping.semester, course_mapping.level, 
       course_mapping.course_unit, course_mapping.course_status,course_manager.course_lecturer_id, staffs.firstname, staffs.othernames, 
       staffs.lastname, course_mapping.pre_select from courses left join course_mapping on course_mapping.course_id = courses.id 
       left join course_manager on course_manager.course_id = courses.id left join users_new on users_new.id = course_manager.course_lecturer_id 
       left join staffs on staffs.id=users_new.user_table_id where 
    	(courses.code like '%$course%' or courses.title like '%$course%') and 
    		course_mapping.semester = ? and courses.active = '1' ";
        if ($programme) {
            $query .= " and course_mapping.programme_id = '$programme'";
        }
        $query .= " order by courses.code asc";
        $courses = $this->query($query, [$semester]);
        $result = [];
        if (!$courses) {
            return $result;
        }

        foreach ($courses as $courseData) {
            $mappedLevel = json_decode($courseData['level'], true);
            foreach ($mappedLevel as $key => $levelMapped) {
                if ($levelMapped <= $level) {
                    unset($courseData['level']);
                    $courseData['semester'] = (int)$courseData['semester'];
                    $courseData['course_unit'] = (int)$courseData['course_unit'];
                    $courseData['pre_select'] = (int)$courseData['pre_select'];
                    $result[] = $courseData;
                }
            }

        }
        return uniqueMultidimensionalArray($result, 'main_course_id');
    }

    public function delete($id = null, &$dbObject = null, $type = null): bool
    {
        permissionAccess('course_delete', 'delete');
        $currentUser = WebSessionManager::currentAPIUser();
        $db = $dbObject ?? $this->db;
        if (parent::delete($id, $db)) {
            logAction($this, 'course_mapping_delete', $currentUser->user_login);
            return true;
        }
        return false;
    }

    protected function baseBuilder(): BaseBuilder
    {
        return $this->db->table('course_mapping a')
            ->join('courses b', 'b.id = a.course_id')
            ->join('programme c', 'c.id = a.programme_id');
    }

    protected function defaultSelect(): string|array
    {
        return 'a.*, c.name, b.code as course_code';
    }

    protected function applyDefaultOrder(BaseBuilder $builder): void
    {
        $builder->orderBy('b.code', 'asc')->orderBy('name', 'asc');
    }

    protected function postProcess(array $rows): array
    {
        return $this->processList($rows);
    }

    protected function postProcessOne(array $row): array
    {
        return $this->loadExtras($row);
    }

    public function APIList($request, $filterList)
    {
        $params = ApiListParams::fromArray($request, [
            'start' => 25,
            'len' => 100,
        ]);
        $params->filters = $filterList;

        return $this->listApi(null,
            $params
        );
    }

    public function APIListOld($filterList, $queryString, $start, $len, $orderBy)
    {
        $temp = getFilterQueryFromDict($filterList);
        $filterQuery = buildCustomWhereString($temp[0], $queryString, false);
        $filterValues = $temp[1];

        if (isset($_GET['sortBy']) && $orderBy) {
            $filterQuery .= " order by $orderBy ";
        } else {
            $filterQuery .= " order by b.code asc, name asc ";
        }

        if (isset($_GET['start']) && $len) {
            $start = $this->db->escapeString($start);
            $len = $this->db->escapeString($len);
            $filterQuery .= " limit $start, $len";
        }
        if (!$filterValues) {
            $filterValues = [];
        }

        $query = "SELECT SQL_CALC_FOUND_ROWS a.*, c.name, b.code as course_code from course_mapping a 
                join courses b on b.id = a.course_id join programme c on c.id = a.programme_id $filterQuery";
        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query, $filterValues);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();

        return [$this->processList($res), $res2];
    }

    private function processList(array $items): array
    {
        $generator = useGenerators($items);
        $payload = [];
        foreach ($generator as $item) {
            $payload[] = $this->loadExtras($item);
        }
        return $payload;
    }

    public function loadExtras(array $item): array
    {
        if ($item['level']) {
            $levels = json_decode($item['level'], true);
            if ($levels) {
                $item['level'] = $levels;
            } else {
                $item['level'] = '';
            }
        } else {
            $item['level'] = '';
        }

        return $item;
    }

}
