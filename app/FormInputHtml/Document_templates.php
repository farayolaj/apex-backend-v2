<?php

namespace App\FormInputHtml;

class Document_templates
{
    function __construct($array = array())
    {
        parent::__construct($array);
    }

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

    function getCategoryFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='category' >Category</label>
		<input type='text' name='category' id='category' value='$value' class='form-control' required />
</div> ";

    }

    function getPrintableFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='printable' >Printable</label>
		<input type='text' name='printable' id='printable' value='$value' class='form-control' required />
</div> ";

    }

    function getSessionFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='session' >Session</label>
</div> ";

    }

    function getPrerequisite_feeFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='prerequisite_fee' >Prerequisite Fee</label><input type='number' name='prerequisite_fee' id='prerequisite_fee' value='$value' class='form-control' required />
</div> ";

    }

    function getBarcode_contentFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='barcode_content' >Barcode Content</label>
<textarea id='barcode_content' name='barcode_content' class='form-control' required>$value</textarea>
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