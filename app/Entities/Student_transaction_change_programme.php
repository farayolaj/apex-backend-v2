<?php
namespace App\Entities;

use App\Models\Crud;

use App\Enums\CommonEnum as CommonSlug;
/**
 * This class queries fresher student and those that paid for programme change
 */
class Student_transaction_change_programme extends Crud
{
    protected static $tablename = 'Student_transaction_change_programme';

    static $apiSelectClause = ['id', 'firstname', 'lastname', 'othernames', 'user_login'];

    /**
     * @param mixed $filterList
     * @param mixed $queryString
     * @param mixed $start
     * @param mixed $len
     * @param mixed $orderBy
     * @return array
     */
    public function APIList($filterList, $queryString, $start, $len, $orderBy)
    {
        $type              = $this->input->get('type', true);
        $paymentStatus     = false;
        $tempPaymentStatus = [];
        if (isset($filterList['payment_status']) && $filterList['payment_status']) {
            $paymentStatus                       = true;
            $tempPaymentStatus['payment_status'] = $filterList['payment_status'];
            unset($filterList['payment_status']);
        } else {
            // using this to still enforce a paid payment_status since FE is not sending any
            $paymentStatus                       = true;
            $tempPaymentStatus['payment_status'] = 'paid';
        }
        $temp           = getFilterQueryFromDict($filterList);
        $filterQuery    = buildCustomWhereString($temp[0], $queryString, false);
        $filterValues   = $temp[1];
        $currentSession = get_setting('active_session_student_portal');

        if ($type == 'fresher') {
            $currentSession = get_setting('admission_session_update');
            // $filterQuery .= ($filterQuery ? " and " : " where ") . " ((academic_record.session_of_admission = '$currentSession')) ";
            $directEntry = $this->db->escape_str(CommonSlug::DIRECT_ENTRY->value);
            $olevel      = $this->db->escape_str(CommonSlug::O_LEVEL->value);
            $olevelPutme = $this->db->escape_str(CommonSlug::O_LEVEL_PUTME->value);
            $fastTrack   = $this->db->escape_str(CommonSlug::FAST_TRACK->value);

            $filterQuery .= ($filterQuery ? " and " : " where ") . " (
				(academic_record.entry_mode = '$directEntry' and academic_record.current_level = '2') ||
				(academic_record.entry_mode = '$fastTrack' and academic_record.current_level = '2') ||
				(academic_record.entry_mode = '$olevel' and academic_record.current_level = '1') ||
                (academic_record.entry_mode = '$olevelPutme' and academic_record.current_level = '1') ||
				(academic_record.entry_mode = '$directEntry' and academic_record.current_level = '1') ||
				(academic_record.entry_mode = '$fastTrack' and academic_record.current_level = '1')
			) ";
        } else {
            if ($paymentStatus) {
                if ($tempPaymentStatus['payment_status'] == 'paid') {
                    // $filterQuery .= ($filterQuery ? " and " : " where ") . " (payment_status in ('00', '01') and fee_description.code = 'CCF') and transaction.session = ? and academic_record.session_of_admission <> ? ";

                    // $filterValues[] = $currentSession;
                    // $filterValues[] = $currentSession;

                    $filterQuery .= ($filterQuery ? " and " : " where ") . " (payment_status in ('00', '01') and fee_description.code = 'CCF')";
                } else if ($tempPaymentStatus['payment_status'] == 'pending') {
                    $filterQuery .= ($filterQuery ? " and " : " where ") . " (payment_status not in ('00', '01') and fee_description.code = 'CCF'  ) ";
                }
            }
        }

        if (isset($_GET['sortBy']) && $orderBy) {
            $filterQuery .= " order by $orderBy ";
        } else {
            $filterQuery .= " order by students.id desc ";
        }

        if ($len) {
            $start = $this->db->escape($start);
            $len   = $this->db->escape($len);
            $filterQuery .= " limit $start, $len";
        }

        if (! $filterValues) {
            $filterValues = [];
        }

        if ($type == 'fresher') {
            $query = "SELECT distinct SQL_CALC_FOUND_ROWS students.id as student_id, CONCAT(students.lastname, ' ', students.firstname,
			' ', students.othernames) AS fullname,passport, academic_record.application_number,
	        academic_record.current_level as level, sessions.date as year_of_entry, department.name as department, faculty.name as faculty,
	        programme.name as programme,programme.id as program_id from students join academic_record  on academic_record.student_id = students.id
	        join sessions on sessions.id = academic_record.year_of_entry join programme on programme.id = academic_record.programme_id left join
	        department on department.id = programme.department_id left join faculty on faculty.id = programme.faculty_id $filterQuery";
        } else {
            $query = "SELECT distinct SQL_CALC_FOUND_ROWS students.id as student_id, CONCAT(students.lastname, ' ', students.firstname, ' ', students.othernames) AS fullname,
            passport, academic_record.application_number,academic_record.current_level as level, sessions.date as year_of_entry,
            department.name as department, faculty.name as faculty, programme.name as programme,programme.id as program_id,
            transaction.payment_status,transaction.date_performed, transaction.date_completed,transaction.payment_description,
            transaction.session as trans_session,academic_record.matric_number,students.user_login as student_email from transaction
            join fee_description on fee_description.id = transaction.payment_id join students on students.id = transaction.student_id join
            academic_record  on academic_record.student_id = students.id join sessions on sessions.id = academic_record.year_of_entry join programme
            on programme.id = academic_record.programme_id join department on department.id = programme.department_id join faculty
            on faculty.id = programme.faculty_id $filterQuery";
        }

        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res    = $this->db->query($query, $filterValues);
        $res    = $res->getResultArray();
        $res2   = $this->db->query($query2);
        $res2   = $res2->getResultArray();
        return [$res, $res2];
    }

}
