<?php

namespace App\FormInputHtml;

class Courses
{
    function getCodeFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='code' >Code</label>
		<input type='text' name='code' id='code' value='$value' class='form-control' required />
</div> ";

    }

    function getTitleFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='title' >Title</label>
<textarea id='title' name='title' class='form-control' required>$value</textarea>
</div> ";

    }

    function getDescriptionFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='description' >Description</label>
<textarea id='description' name='description' class='form-control' required>$value</textarea>
</div> ";

    }

    function getCourse_guide_urlFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='course_guide_url' >Course Guide Url</label>
<textarea id='course_guide_url' name='course_guide_url' class='form-control' >$value</textarea>
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

    function getDate_createdFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='date_created' >Date Created</label>
		<input type='text' name='date_created' id='date_created' value='$value' class='form-control'  />
</div> ";

    }
}