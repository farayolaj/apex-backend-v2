<?php

namespace App\Entities;

use App\Models\Crud;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the academic_record table.
 */
class Academic_record extends Crud
{
    protected static $tablename = 'Academic_record';
    /* this array contains the field that can be null*/
    static $nullArray = array('jamb_details', 'olevel_details', 'alevel_details', 'nce_nd_hnd', 'institutions_attended', 'has_matric_number', 'has_institution_email', 'year_of_entry', 'mode_of_study', 'interactive_center', 'exam_center', 'teaching_subject', 'application_number');
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('student_id' => 'int', 'jamb_details' => 'text', 'olevel_details' => 'text', 'alevel_details' => 'text', 'nce_nd_hnd' => 'text', 'institutions_attended' => 'text', 'programme_id' => 'int', 'matric_number' => 'varchar', 'has_matric_number' => 'tinyint', 'has_institution_email' => 'tinyint', 'programme_duration' => 'varchar', 'min_programme_duration' => 'int', 'max_programme_duration' => 'int', 'year_of_entry' => 'varchar', 'entry_mode' => 'varchar', 'mode_of_study' => 'varchar', 'interactive_center' => 'varchar', 'exam_center' => 'varchar', 'teaching_subject' => 'varchar', 'level_of_admission' => 'varchar', 'session_of_admission' => 'int', 'current_level' => 'int', 'current_session' => 'int', 'application_number' => 'varchar');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'student_id' => '', 'jamb_details' => '', 'olevel_details' => '', 'alevel_details' => '', 'nce_nd_hnd' => '', 'institutions_attended' => '', 'programme_id' => '', 'matric_number' => '', 'has_matric_number' => '', 'has_institution_email' => '', 'programme_duration' => '', 'min_programme_duration' => '', 'max_programme_duration' => '', 'year_of_entry' => '', 'entry_mode' => '', 'mode_of_study' => '', 'interactive_center' => '', 'exam_center' => '', 'teaching_subject' => '', 'level_of_admission' => '', 'session_of_admission' => '', 'current_level' => '', 'current_session' => '', 'application_number' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array('has_matric_number' => '0', 'has_institution_email' => '0');
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array('programme' => array('programme_id', 'ID')
    );
    static $tableAction = array('delete' => 'delete/academic_record', 'edit' => 'edit/academic_record');

    function __construct($array = array())
    {
        parent::__construct($array);
    }

    function getStudent_idFormField($value = '')
    {
        $fk = null;//change the value of this variable to array('table'=>'student','display'=>'student_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

        if (is_null($fk)) {
            return $result = "<input type='hidden' value='$value' name='student_id' id='student_id' class='form-control' />
			";
        }
        if (is_array($fk)) {
            $result = "<div class='form-group'>
		<label for='student_id'>Student Id</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='student_id' id='student_id' class='form-control'>
			$option
		</select>";
        }
        $result .= "</div>";
        return $result;

    }

    function getJamb_detailsFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='jamb_details' >Jamb Details</label>
<textarea id='jamb_details' name='jamb_details' class='form-control' >$value</textarea>
</div> ";

    }

    function getOlevel_detailsFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='olevel_details' >Olevel Details</label>
<textarea id='olevel_details' name='olevel_details' class='form-control' >$value</textarea>
</div> ";

    }

    function getAlevel_detailsFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='alevel_details' >Alevel Details</label>
<textarea id='alevel_details' name='alevel_details' class='form-control' >$value</textarea>
</div> ";

    }

    function getNce_nd_hndFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='nce_nd_hnd' >Nce Nd Hnd</label>
<textarea id='nce_nd_hnd' name='nce_nd_hnd' class='form-control' >$value</textarea>
</div> ";

    }

    function getInstitutions_attendedFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='institutions_attended' >Institutions Attended</label>
<textarea id='institutions_attended' name='institutions_attended' class='form-control' >$value</textarea>
</div> ";

    }

    function getProgramme_idFormField($value = '')
    {
        $fk = null;//change the value of this variable to array('table'=>'programme','display'=>'programme_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

        if (is_null($fk)) {
            return $result = "<input type='hidden' value='$value' name='programme_id' id='programme_id' class='form-control' />
			";
        }
        if (is_array($fk)) {
            $result = "<div class='form-group'>
		<label for='programme_id'>Programme Id</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='programme_id' id='programme_id' class='form-control'>
			$option
		</select>";
        }
        $result .= "</div>";
        return $result;

    }

    function getMatric_numberFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='matric_number' >Matric Number</label>
		<input type='text' name='matric_number' id='matric_number' value='$value' class='form-control' required />
</div> ";

    }

    function getHas_matric_numberFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Has Matric Number</label>
	<select class='form-control' id='has_matric_number' name='has_matric_number' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

    }

    function getHas_institution_emailFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Has Institution Email</label>
	<select class='form-control' id='has_institution_email' name='has_institution_email' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

    }

    function getProgramme_durationFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='programme_duration' >Programme Duration</label>
		<input type='text' name='programme_duration' id='programme_duration' value='$value' class='form-control' required />
</div> ";

    }

    function getMin_programme_durationFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='min_programme_duration' >Min Programme Duration</label><input type='number' name='min_programme_duration' id='min_programme_duration' value='$value' class='form-control' required />
</div> ";

    }

    function getMax_programme_durationFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='max_programme_duration' >Max Programme Duration</label><input type='number' name='max_programme_duration' id='max_programme_duration' value='$value' class='form-control' required />
</div> ";

    }

    function getYear_of_entryFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='year_of_entry' >Year Of Entry</label>
		<input type='text' name='year_of_entry' id='year_of_entry' value='$value' class='form-control'  />
</div> ";

    }

    function getEntry_modeFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='entry_mode' >Entry Mode</label>
		<input type='text' name='entry_mode' id='entry_mode' value='$value' class='form-control' required />
</div> ";

    }

    function getMode_of_studyFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='mode_of_study' >Mode Of Study</label>
		<input type='text' name='mode_of_study' id='mode_of_study' value='$value' class='form-control'  />
</div> ";

    }

    function getInteractive_centerFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='interactive_center' >Interactive Center</label>
		<input type='text' name='interactive_center' id='interactive_center' value='$value' class='form-control'  />
</div> ";

    }

    function getExam_centerFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='exam_center' >Exam Center</label>
		<input type='text' name='exam_center' id='exam_center' value='$value' class='form-control'  />
</div> ";

    }

    function getTeaching_subjectFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='teaching_subject' >Teaching Subject</label>
		<input type='text' name='teaching_subject' id='teaching_subject' value='$value' class='form-control'  />
</div> ";

    }

    function getLevel_of_admissionFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='level_of_admission' >Level Of Admission</label>
		<input type='text' name='level_of_admission' id='level_of_admission' value='$value' class='form-control' required />
</div> ";

    }

    function getSession_of_admissionFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='session_of_admission' >Session Of Admission</label><input type='number' name='session_of_admission' id='session_of_admission' value='$value' class='form-control' required />
</div> ";

    }

    function getCurrent_levelFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='current_level' >Current Level</label><input type='number' name='current_level' id='current_level' value='$value' class='form-control' required />
</div> ";

    }

    function getCurrent_sessionFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='current_session' >Current Session</label><input type='number' name='current_session' id='current_session' value='$value' class='form-control' required />
</div> ";

    }

    function getApplication_numberFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='application_number' >Application Number</label>
		<input type='text' name='application_number' id='application_number' value='$value' class='form-control'  />
</div> ";

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

    /**
     * @param  [type] $semester [description]
     * @return [type]           [description]
     */
    public function getTotalRegisteredCourse($semester)
    {
        $query = "SELECT sum(course_unit) as num from course_enrollment where student_id=? and session_id =? and student_level=? and semester=?";
        $param = [
            $this->student_id,
            $this->current_session,
            $this->current_level,
            $semester
        ];
        $result = $this->query($query, $param);
        if (!$result) {
            return 0;
        }
        return $result[0]['num'];
    }

    public function getTotalRegisteredCourseUnit(?string $session, ?string $semester = null): int
    {
        $baseQuery = "SELECT sum(course_unit) as num FROM course_enrollment WHERE student_id = ? AND session_id = ?";
        $params = [$this->student_id, $session];

        if ($semester !== null) {
            $baseQuery .= " AND semester = ?";
            $params[] = $semester;
        }

        $result = $this->query($baseQuery, $params);
        return (int) ($result[0]['num'] ?? 0);

    }

    public function getTotalRegisteredCourses(string $session, ?string $semester = null): int
    {
        $baseQuery = "SELECT COUNT(*) as num FROM course_enrollment WHERE student_id = ? AND session_id = ?";
        $params = [$this->student_id, $session];

        if ($semester !== null) {
            $baseQuery .= " AND semester = ?";
            $params[] = $semester;
        }

        $result = $this->query($baseQuery, $params);

        return (int) ($result[0]['num'] ?? 0);
    }


    public function getMinMaxUnit(?string $semester = null): ?array
    {
        $params = [
            $this->programme_id,
            $this->current_level,
            $this->entry_mode
        ];

        if ($semester === null) {
            $query = "SELECT SUM(min_unit) as min_unit, SUM(max_unit) as max_unit 
                 FROM course_configuration 
                 WHERE programme_id = ? 
                 AND level = ? 
                 AND entry_mode = ?";
        } else {
            $query = "SELECT min_unit, max_unit 
                 FROM course_configuration 
                 WHERE programme_id = ? 
                 AND level = ? 
                 AND entry_mode = ?
                 AND semester = ? ";
            $params[] = $semester;
        }

        $result = $this->query($query, $params);
        return $result ? $result[0] : null;
    }



}


