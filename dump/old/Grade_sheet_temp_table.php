<?php
		require_once('application/models/Crud.php');
		/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the grade_sheet_temp_table table.
		*/
		class Grade_sheet_temp_table extends Crud
		{
protected static $tablename='Grade_sheet_temp_table';
/* this array contains the field that can be null*/
static $nullArray=array();
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('programme_id'=>'int','session_id'=>'int','level'=>'varchar','semester'=>'int','course_id_1'=>'varchar','course_id_2'=>'varchar','course_id_3'=>'varchar','course_id_4'=>'varchar','course_id_5'=>'varchar','course_id_6'=>'varchar','course_id_7'=>'varchar','course_id_8'=>'varchar','course_id_9'=>'varchar','course_id_10'=>'varchar','course_id_11'=>'varchar','course_id_12'=>'varchar','course_id_13'=>'varchar','course_id_14'=>'varchar','course_id_15'=>'varchar','course_id_16'=>'varchar','course_id_17'=>'varchar','course_id_18'=>'varchar','course_id_19'=>'varchar','course_id_20'=>'varchar','course_id_21'=>'varchar','course_id_22'=>'varchar','course_id_23'=>'varchar','course_id_24'=>'varchar','course_id_25'=>'varchar','course_id_26'=>'varchar','course_id_27'=>'varchar','course_id_28'=>'varchar','course_id_29'=>'varchar','course_id_30'=>'varchar','course_id_31'=>'varchar','course_id_32'=>'varchar','course_id_33'=>'varchar','course_id_34'=>'varchar','course_id_35'=>'varchar','course_id_36'=>'varchar','course_id_37'=>'varchar','course_id_38'=>'varchar','course_id_39'=>'varchar');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','programme_id'=>'','session_id'=>'','level'=>'','semester'=>'','course_id_1'=>'','course_id_2'=>'','course_id_3'=>'','course_id_4'=>'','course_id_5'=>'','course_id_6'=>'','course_id_7'=>'','course_id_8'=>'','course_id_9'=>'','course_id_10'=>'','course_id_11'=>'','course_id_12'=>'','course_id_13'=>'','course_id_14'=>'','course_id_15'=>'','course_id_16'=>'','course_id_17'=>'','course_id_18'=>'','course_id_19'=>'','course_id_20'=>'','course_id_21'=>'','course_id_22'=>'','course_id_23'=>'','course_id_24'=>'','course_id_25'=>'','course_id_26'=>'','course_id_27'=>'','course_id_28'=>'','course_id_29'=>'','course_id_30'=>'','course_id_31'=>'','course_id_32'=>'','course_id_33'=>'','course_id_34'=>'','course_id_35'=>'','course_id_36'=>'','course_id_37'=>'','course_id_38'=>'','course_id_39'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array('programme'=>array( 'programme_id', 'ID')
);
static $tableAction=array('delete'=>'delete/grade_sheet_temp_table','edit'=>'edit/grade_sheet_temp_table');
function __construct($array=array())
{
	parent::__construct($array);
}
	 function getProgramme_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'programme','display'=>'programme_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='programme_id' id='programme_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='programme_id'>Programme Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='programme_id' id='programme_id' class='form-control'>
			$option
		</select>";
	}
	$result.="</div>";
	return  $result;

}
	 function getSession_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'session','display'=>'session_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='session_id' id='session_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='session_id'>Session Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='session_id' id='session_id' class='form-control'>
			$option
		</select>";
	}
	$result.="</div>";
	return  $result;

}
function getLevelFormField($value=''){
	
	return "<div class='form-group'>
	<label for='level' >Level</label>
		<input type='text' name='level' id='level' value='$value' class='form-control' required />
</div> ";

}
function getSemesterFormField($value=''){
	
	return "<div class='form-group'>
	<label for='semester' >Semester</label><input type='number' name='semester' id='semester' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_1FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_1' >Course Id 1</label>
		<input type='text' name='course_id_1' id='course_id_1' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_2FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_2' >Course Id 2</label>
		<input type='text' name='course_id_2' id='course_id_2' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_3FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_3' >Course Id 3</label>
		<input type='text' name='course_id_3' id='course_id_3' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_4FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_4' >Course Id 4</label>
		<input type='text' name='course_id_4' id='course_id_4' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_5FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_5' >Course Id 5</label>
		<input type='text' name='course_id_5' id='course_id_5' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_6FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_6' >Course Id 6</label>
		<input type='text' name='course_id_6' id='course_id_6' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_7FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_7' >Course Id 7</label>
		<input type='text' name='course_id_7' id='course_id_7' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_8FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_8' >Course Id 8</label>
		<input type='text' name='course_id_8' id='course_id_8' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_9FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_9' >Course Id 9</label>
		<input type='text' name='course_id_9' id='course_id_9' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_10FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_10' >Course Id 10</label>
		<input type='text' name='course_id_10' id='course_id_10' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_11FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_11' >Course Id 11</label>
		<input type='text' name='course_id_11' id='course_id_11' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_12FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_12' >Course Id 12</label>
		<input type='text' name='course_id_12' id='course_id_12' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_13FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_13' >Course Id 13</label>
		<input type='text' name='course_id_13' id='course_id_13' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_14FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_14' >Course Id 14</label>
		<input type='text' name='course_id_14' id='course_id_14' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_15FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_15' >Course Id 15</label>
		<input type='text' name='course_id_15' id='course_id_15' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_16FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_16' >Course Id 16</label>
		<input type='text' name='course_id_16' id='course_id_16' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_17FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_17' >Course Id 17</label>
		<input type='text' name='course_id_17' id='course_id_17' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_18FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_18' >Course Id 18</label>
		<input type='text' name='course_id_18' id='course_id_18' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_19FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_19' >Course Id 19</label>
		<input type='text' name='course_id_19' id='course_id_19' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_20FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_20' >Course Id 20</label>
		<input type='text' name='course_id_20' id='course_id_20' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_21FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_21' >Course Id 21</label>
		<input type='text' name='course_id_21' id='course_id_21' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_22FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_22' >Course Id 22</label>
		<input type='text' name='course_id_22' id='course_id_22' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_23FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_23' >Course Id 23</label>
		<input type='text' name='course_id_23' id='course_id_23' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_24FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_24' >Course Id 24</label>
		<input type='text' name='course_id_24' id='course_id_24' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_25FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_25' >Course Id 25</label>
		<input type='text' name='course_id_25' id='course_id_25' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_26FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_26' >Course Id 26</label>
		<input type='text' name='course_id_26' id='course_id_26' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_27FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_27' >Course Id 27</label>
		<input type='text' name='course_id_27' id='course_id_27' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_28FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_28' >Course Id 28</label>
		<input type='text' name='course_id_28' id='course_id_28' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_29FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_29' >Course Id 29</label>
		<input type='text' name='course_id_29' id='course_id_29' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_30FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_30' >Course Id 30</label>
		<input type='text' name='course_id_30' id='course_id_30' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_31FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_31' >Course Id 31</label>
		<input type='text' name='course_id_31' id='course_id_31' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_32FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_32' >Course Id 32</label>
		<input type='text' name='course_id_32' id='course_id_32' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_33FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_33' >Course Id 33</label>
		<input type='text' name='course_id_33' id='course_id_33' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_34FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_34' >Course Id 34</label>
		<input type='text' name='course_id_34' id='course_id_34' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_35FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_35' >Course Id 35</label>
		<input type='text' name='course_id_35' id='course_id_35' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_36FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_36' >Course Id 36</label>
		<input type='text' name='course_id_36' id='course_id_36' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_37FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_37' >Course Id 37</label>
		<input type='text' name='course_id_37' id='course_id_37' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_38FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_38' >Course Id 38</label>
		<input type='text' name='course_id_38' id='course_id_38' value='$value' class='form-control' required />
</div> ";

}
function getCourse_id_39FormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_id_39' >Course Id 39</label>
		<input type='text' name='course_id_39' id='course_id_39' value='$value' class='form-control' required />
</div> ";

}


		
protected function getProgramme(){
	$query ='SELECT * FROM programme WHERE id=?';
	if (!isset($this->array['ID'])) {
		return null;
	}
	$id = $this->array['ID'];
	$result = $this->db->query($query,array($id));
	$result =$result->result_array();
	if (empty($result)) {
		return false;
	}
	include_once('Programme.php');
	$resultObject = new Programme($result[0]);
	return $resultObject;
}
		}
		?>