<?php

namespace App\FormInputHtml;

class Templates
{
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
}