<?php

namespace App\FormInputHtml;

class Students
{
    /**
     * @param mixed $value
     * @return string
     */
    public function getFirstnameFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='firstname' >Firstname</label>
		<input type='text' name='firstname' id='firstname' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getOthernamesFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='othernames' >Othernames</label>
		<input type='text' name='othernames' id='othernames' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getLastnameFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='lastname' >Lastname</label>
		<input type='text' name='lastname' id='lastname' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getGenderFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='gender' >Gender</label>
		<input type='text' name='gender' id='gender' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getDoBFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='DoB' >DoB</label>
		<input type='text' name='DoB' id='DoB' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getPhoneFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='phone' >Phone</label>
		<input type='text' name='phone' id='phone' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getMarital_statusFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='marital_status' >Marital Status</label>
		<input type='text' name='marital_status' id='marital_status' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getReligionFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='religion' >Religion</label>
		<input type='text' name='religion' id='religion' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getContact_addressFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='contact_address' >Contact Address</label>
		<input type='text' name='contact_address' id='contact_address' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getPostal_addressFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='postal_address' >Postal Address</label>
		<input type='text' name='postal_address' id='postal_address' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getProfessionFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='profession' >Profession</label>
		<input type='text' name='profession' id='profession' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getState_of_originFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='state_of_origin' >State Of Origin</label>
		<input type='text' name='state_of_origin' id='state_of_origin' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getLgaFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='lga' >Lga</label>
		<input type='text' name='lga' id='lga' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getNationalityFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='nationality' >Nationality</label>
		<input type='text' name='nationality' id='nationality' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getPassportFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='passport' >Passport</label>
		<input type='text' name='passport' id='passport' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getFull_imageFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='full_image' >Full Image</label>
		<input type='text' name='full_image' id='full_image' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getNext_of_kinFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='next_of_kin' >Next Of Kin</label>
		<input type='text' name='next_of_kin' id='next_of_kin' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getNext_of_kin_phoneFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='next_of_kin_phone' >Next Of Kin Phone</label>
		<input type='text' name='next_of_kin_phone' id='next_of_kin_phone' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getNext_of_kin_addressFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='next_of_kin_address' >Next Of Kin Address</label>
		<input type='text' name='next_of_kin_address' id='next_of_kin_address' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getRefereeFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='referee' >Referee</label>
<textarea id='referee' name='referee' class='form-control' >$value</textarea>
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getAlternative_emailFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='alternative_email' >Alternative Email</label>
	<input type='email' name='alternative_email' id='alternative_email' value='$value' class='form-control'  />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getUser_loginFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='user_login' >User Login</label>
		<input type='text' name='user_login' id='user_login' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getUser_passFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='user_pass' >User Pass</label>
		<input type='text' name='user_pass' id='user_pass' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getSession_keyFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='session_key' >Session Key</label>
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getUser_agentFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='user_agent' >User Agent</label>
<textarea id='user_agent' name='user_agent' class='form-control' required>$value</textarea>
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getIp_addressFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='ip_address' >Ip Address</label>
		<input type='text' name='ip_address' id='ip_address' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getLast_logged_inFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='last_logged_in' >Last Logged In</label>
		<input type='text' name='last_logged_in' id='last_logged_in' value='$value' class='form-control' required />
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getActiveFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Active</label>
	<select class='form-control' id='active' name='active' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getIs_verifiedFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Is Verified</label>
	<select class='form-control' id='is_verified' name='is_verified' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getVerified_byFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='verified_by' >Verified By</label>
<textarea id='verified_by' name='verified_by' class='form-control' >$value</textarea>
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getVerify_attemptFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Verify Attempt</label>
	<select class='form-control' id='verify_attempt' name='verify_attempt' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getDate_verifiedFormField($value = '')
    {

        return " ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getIs_screenedFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Is Screened</label>
	<select class='form-control' id='is_screened' name='is_screened' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getScreened_byFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='screened_by' >Screened By</label>
<textarea id='screened_by' name='screened_by' class='form-control' >$value</textarea>
</div> ";
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getScreening_attemptFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Screening Attempt</label>
	<select class='form-control' id='screening_attempt' name='screening_attempt' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";
    }

    /**
     * @param mixed $type
     * @return void
     */
    public function getState_of_originOptions($type = '')
    {
        exit("i am trying to load state and origin options here");
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function getDate_createdFormField($value = '')
    {

        return " ";
    }
}