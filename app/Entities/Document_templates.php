<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the document_templates table.
 */
class Document_templates extends Crud
{
	protected static $tablename = 'Document_templates';
	/* this array contains the field that can be null*/
	static $nullArray = array();
	static $compositePrimaryKey = array();
	static $uploadDependency = array();
	/*this array contains the fields that are unique*/
	static $uniqueArray = array();
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('name' => 'varchar', 'slug' => 'varchar', 'category' => 'varchar', 'printable' => 'varchar', 'session' => 'smallint', 'prerequisite_fee' => 'int', 'barcode_content' => 'text', 'content' => 'longtext', 'active' => 'tinyint', 'date_added' => 'datetime');
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'name' => '', 'slug' => '', 'category' => '', 'printable' => '', 'session' => '', 'prerequisite_fee' => '', 'barcode_content' => '', 'content' => '', 'active' => '', 'date_added' => '');
	/*associative array of fields that have default value*/
	static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = array(); //array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = array();
	static $tableAction = array('delete' => 'delete/document_templates', 'edit' => 'edit/document_templates');

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

	public function getDocuments($entryYear, $session)
	{
		$query = "SELECT * from document_templates where (printable = 'year_of_entry' and session = ?) or 
         (printable = 'session' and session = ?) or (category = 'misc') and active = '1'";
		$result = $this->query($query, [$entryYear, $session]);
		if (!$result) {
			return false;
		}
		return $result;
	}

	public function getSingleDocument($session, $dbColumn, $dbValue)
	{
		$query = "SELECT * from document_templates where (printable = 'session' and session = ?) and active = '1' 
        and $dbColumn = ?";
		$result = $this->query($query, [$session, $dbValue]);
		if (!$result) {
			return false;
		}
		return $result;
	}

	public function getDocumentTemplates($slug, $variables, $yearOfEntry = '', $currentSession = '')
	{
		$this->load->library('parser');
		$result = $this->getWhere(['slug' => $slug], $count, 0, null, false);
		if ($result) {
			$documentContent = '';
			foreach ($result as $row) {
				if ($row->category == 'general' && $row->printable == 'year_of_entry') {
					$temp = $this->getWhere(array('slug' => $slug, 'session' => $yearOfEntry), $count, 0, null, false);
					if (!$temp) {
						return null;
					}
					$temp = $temp[0];
					$documentContent = $temp->content;
				} elseif ($row->category == 'general' && $row->printable == 'session') {
					$temp = $this->getWhere(array('slug' => $slug, 'session' => $currentSession), $count, 0, null, false);
					if (!$temp) {
						return null;
					}
					$temp = $temp[0];
					$documentContent = $temp->content;
				} else {
					$documentContent = $row->content;
				}

				$message = base64_decode($documentContent);
				$message = str_replace("{current_level}00", "{current_level}", $message);
				return $this->parser->parse_string($message, $variables, true);

			}
		}
	}

	public function getDocumentBarcodeContentBySlug($slug)
	{
		$result = $this->getWhere(['slug' => $slug], $count, 0, null, false);
		if (!$result) {
			return null;
		}
		return $result[0]->barcode_content;
	}

}

