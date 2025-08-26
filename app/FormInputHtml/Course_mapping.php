<?php

namespace App\FormInputHtml;

class Course_mapping
{
    function getCourse_idFormField($value = '')
    {
        $fk = null; //change the value of this variable to array('table'=>'course','display'=>'course_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

        if (is_null($fk)) {
            return $result = "<input type='hidden' value='$value' name='course_id' id='course_id' class='form-control' />
			";
        }
        if (is_array($fk)) {
            $result = "<div class='form-group'>
		<label for='course_id'>Course Id</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='course_id' id='course_id' class='form-control'>
			$option
		</select>";
        }
        $result .= "</div>";
        return $result;

    }

    function getProgramme_idFormField($value = '')
    {
        $fk = null; //change the value of this variable to array('table'=>'programme','display'=>'programme_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

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

    function getSemesterFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='semester' >Semester</label><input type='number' name='semester' id='semester' value='$value' class='form-control' required />
</div> ";

    }

    function getCourse_unitFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='course_unit' >Course Unit</label><input type='number' name='course_unit' id='course_unit' value='$value' class='form-control' required />
</div> ";

    }

    function getCourse_statusFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='course_status' >Course Status</label>
		<input type='text' name='course_status' id='course_status' value='$value' class='form-control' required />
</div> ";

    }

    function getLevelFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='level' >Level</label>
<textarea id='level' name='level' class='form-control' required>$value</textarea>
</div> ";

    }

    function getMode_of_entryFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='mode_of_entry' >Mode Of Entry</label>
<textarea id='mode_of_entry' name='mode_of_entry' class='form-control' required>$value</textarea>
</div> ";

    }

    function getPass_scoreFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='pass_score' >Pass Score</label><input type='number' name='pass_score' id='pass_score' value='$value' class='form-control' required />
</div> ";

    }

    function getPre_selectFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Pre Select</label>
	<select class='form-control' id='pre_select' name='pre_select' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

    }

    protected function getCourse()
    {
        $query = 'SELECT * FROM course WHERE id=?';
        if (!isset($this->array['id'])) {
            return null;
        }
        $id = $this->array['id'];
        $result = $this->db->query($query, array($id));
        $result = $result->getResultArray();
        if (empty($result)) {
            return false;
        }
        return new \App\Entities\Course($result[0]);
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
}