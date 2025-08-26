<?php

namespace App\FormInputHtml;

class Course_configuration
{
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

    function getSemesterFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='semester' >Semester</label><input type='number' name='semester' id='semester' value='$value' class='form-control' required />
</div> ";

    }

    function getLevelFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='level' >Level</label><input type='number' name='level' id='level' value='$value' class='form-control' required />
</div> ";

    }

    function getEntry_modeFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='entry_mode' >Entry Mode</label>
		<input type='text' name='entry_mode' id='entry_mode' value='$value' class='form-control' required />
</div> ";

    }

    function getMin_unitFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='min_unit' >Min Unit</label><input type='number' name='min_unit' id='min_unit' value='$value' class='form-control' required />
</div> ";

    }

    function getMax_unitFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='max_unit' >Max Unit</label><input type='number' name='max_unit' id='max_unit' value='$value' class='form-control' required />
</div> ";

    }

    function getEnable_regFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Enable Reg</label>
	<select class='form-control' id='enable_reg' name='enable_reg' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

    }

    function getDate_createdFormField($value = '')
    {

        return " ";

    }
}