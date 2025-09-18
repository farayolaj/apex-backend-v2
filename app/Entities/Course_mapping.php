<?php

namespace App\Entities;

use App\Models\Crud;
use App\Models\WebSessionManager;
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

    protected array $searchable = ['b.code', 'c.name'];

    function __construct($array = array())
    {
        parent::__construct($array);
    }

    /**
     * This load courses based on programme and semester in which the
     * result is filtered by student level according to mapped level
     * TODO - Need to look into how to load course lecturers in this query properly as this will give false positive
     * @param int $programme_id
     * @param int $level
     * @param string $entryMode
     * @param int $semester [description]
     * @return array [type] [description]
     */
    public function getCourseLists(int $programme_id, int $level, string $entryMode, int $semester): array
    {
        $query = "SELECT c.id as main_course_id, c.code, c.title, c.description, c.course_guide_url,
            c.active, cm.id as course_mapping_id, cm.programme_id, cm.semester,
            cm.level, cm.course_unit, cm.course_status, cm.pre_select,
            cmgr.course_lecturer_id, s.firstname, s.othernames, s.lastname
            FROM courses c
            LEFT JOIN course_mapping cm ON cm.course_id = c.id
            LEFT JOIN course_manager cmgr ON cmgr.course_id = c.id
            LEFT JOIN users_new un ON un.id = cmgr.course_lecturer_id and un.user_type = 'staff'
            LEFT JOIN staffs s ON s.id = un.user_table_id
            WHERE cm.programme_id = ?
                AND cm.semester = ?
                AND cm.mode_of_entry = ?
                AND c.active = '1'
            ORDER BY c.code ASC
        ";

        $courses = $this->query($query, [$programme_id, $semester, $entryMode]);

        if (!$courses) {
            return [];
        }

        return array_values(array_filter(
            array_map(
                function (array $courseData) use ($level): ?array {
                    $mappedLevel = json_decode($courseData['level'], true);
                    if (!is_array($mappedLevel) || !in_array($level, $mappedLevel)) {
                        return null;
                    }

                    unset($courseData['level']);
                    return array_merge($courseData, [
                        'semester' => $courseData['semester'],
                        'course_unit' => $courseData['course_unit'],
                        'pre_select' => $courseData['pre_select']
                    ]);
                },
                $courses
            )
        ));
    }

    public function searchCourseLists(string $course, int $level, int $semester, ?int $programme = null): array
    {
        $query ="SELECT c.id as main_course_id, c.code, c.title, c.description, c.course_guide_url,
            c.active, cm.id as course_mapping_id, cm.programme_id,
            cm.semester, cm.level, cm.course_unit, cm.course_status,
            cm.pre_select, cmgr.course_lecturer_id, s.firstname, s.othernames, s.lastname
            FROM courses c
            LEFT JOIN course_mapping cm ON cm.course_id = c.id
            LEFT JOIN course_manager cmgr ON cmgr.course_id = c.id
            LEFT JOIN users_new un ON un.id = cmgr.course_lecturer_id
            LEFT JOIN staffs s ON s.id = un.user_table_id
            WHERE (c.code LIKE ? OR c.title LIKE ?)
                AND cm.semester = ?
                AND c.active = '1'
        ";

        $params = [
            "%{$course}%",
            "%{$course}%",
            $semester
        ];

        if ($programme !== null) {
            $query .= " AND cm.programme_id = ?";
            $params[] = $programme;
        }

        $query .= " ORDER BY c.code ASC";
        $courses = $this->query($query, $params);

        if (!$courses) {
            return [];
        }

        $result = [];
        foreach ($courses as $courseData) {
            $mappedLevel = json_decode($courseData['level'], true);
            if (!is_array($mappedLevel)) {
                continue;
            }

            $hasValidLevel = false;
            foreach ($mappedLevel as $levelMapped) {
                if ($levelMapped <= $level) {
                    $hasValidLevel = true;
                    break;
                }
            }

            if (!$hasValidLevel) {
                continue;
            }

            unset($courseData['level']);
            $result[] = [
                ...$courseData,
                'semester' => $courseData['semester'],
                'course_unit' => $courseData['course_unit'],
                'pre_select' => $courseData['pre_select']
            ];
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

    public function defaultSelect(): string|array
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
            'start' => 1,
            'len' => 20,
        ]);
        $params->filters = $filterList;

        return $this->listApi(null,
            $params
        );
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