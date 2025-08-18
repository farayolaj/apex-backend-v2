alter table faculty change active active tinyint(1) not null default 1;
alter table faculty change date_created date_created datetime not null default now();

alter table department change active active tinyint(1) not null default 1;
alter table department change date_created date_created datetime not null default now();


alter table programme change active active tinyint(1) not null default 1;
alter table programme change date_created date_created datetime not null default now();

-- this sql is to truncate all tables in the database. It would bring unnecessary tables
-- too which can be filter out
SELECT Concat('DROP TABLE ', TABLE_NAME, ';') FROM INFORMATION_SCHEMA.TABLES

-- checking transaction between those date
SELECT * from transaction where (payment_status <> '00' or payment_status <> '01') and payment_status != '' 
and cast(date_performed as date) >= '01-07-2023' and cast(date_performed as date) <= '02-08-2023'

SELECT * from transaction where (payment_status <> '00' or payment_status <> '01') and payment_status != '' 
and date_performed between cast('01-07-2023' as date) and cast('02-08-2023' as date)

-- this resolved the incorrect datetime field error for no-zero strict mode on field
-- query to convert transaction level(127) to correct level(501)
-- you might wanna run the sql via CLI
show variables like 'sql_mode';
-- update the variables with the below to allow and reverse it when done
SET sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
-- this is to return it back to it original values
SET sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';



update transaction set level = '501' WHERE `level` = '127'

-- this is to select duplicate values
SELECT id, row_num FROM (SELECT id,ROW_NUMBER() OVER (PARTITION BY student_id ORDER BY student_id) AS row_num
    FROM academic_record) t

-- this is to update student document status if their document path is empty
update students,student_verification_documents set students.document_verification = 'Not Verified' 
	where students.id = student_verification_documents.students_id and  student_verification_documents.document_path = ''

-- this is to move all NON-SUCCESS transaction into transaction archive
INSERT into transaction_archive (
      transaction_id,real_payment_id,payment_id,payment_description,payment_option,student_id,programme_id,session,level,
      transaction_ref,rrr_code,payment_status,beneficiary_1,beneficiary_2,payment_status_description,amount_paid,penalty_fee,
      service_charge,total_amount,payment_url,date_performed,date_completed,date_payment_communicated,preselected_payment,
      transaction_ref_id,subaccount_amount,mainaccount_amount,beneficiary_3,source_table) 
    SELECT 
      id,real_payment_id,payment_id,payment_description,payment_option,student_id,programme_id,session,level,
      transaction_ref,rrr_code,payment_status,beneficiary_1,beneficiary_2,payment_status_description,amount_paid,
      penalty_fee,service_charge,total_amount,payment_url,date_performed,date_completed,date_payment_communicated,
      preselected_payment,transaction_id,subaccount_amount,mainaccount_amount,beneficiary_3,'transaction' from transaction 
      where payment_status not in ('00', '01') and payment_id in ('1','2');


INSERT into transaction_archive (
      transaction_id,real_payment_id,payment_id,payment_description,payment_option,student_id,programme_id,session,level,
      transaction_ref,rrr_code,payment_status,beneficiary_1,beneficiary_2,payment_status_description,amount_paid,penalty_fee,
      service_charge,total_amount,payment_url,date_performed,date_completed,date_payment_communicated,preselected_payment,
      transaction_ref_id,subaccount_amount,mainaccount_amount,beneficiary_3,source_table) 
    SELECT 
      id,0,payment_id,payment_description,0,applicant_id,0,session,0,
      transaction_ref,rrr_code,payment_status,beneficiary_1,beneficiary_2,payment_status_description,amount_paid,
      '',service_charge,total_amount,'',date_performed,date_completed,date_payment_communicated,
      0,transaction_id,subaccount_amount,mainaccount_amount,beneficiary_3,'applicant_transaction' from applicant_transaction 
      where payment_status not in ('00', '01');

-- then delete the transaction from the transaction table
DELETE from transaction where payment_status not in ('00', '01');
DELETE from applicant_transaction where payment_status not in ('00', '01') and year(date_performed) < '2025';


-- updating wrong academic_record level
update transaction a, academic_record b set a.level = 2, b.current_level = 2 
  where a.student_id = b.student_id and 
  (b.entry_mode = 'Direct Entry' and a.level = 1) and a.session = 23 and b.current_session = 23 and a.payment_id = 16


select a.* from transaction a join academic_record b on b.student_id = a.student_id 
where (b.entry_mode = 'Direct Entry' and a.level = 1) and a.session = 23 and b.current_session = 23 and a.payment_id = 16

-- turn all freshers successful transaction to pending to use bulk requery to generate their matric
-- where last date is 2023-11-11
update transaction a set a.payment_status = '021' where (a.level = 1 or a.level = 2) and a.payment_status in ('00', '01')
  and a.payment_id = 1 and a.session = 23 and cast(date_performed as date) >= '2023-11-01'

  -- use this to update transaction session for sundry RoS & SuS using payment session
select a.* from transaction a, payment b where a.real_payment_id = b.id and a.session = 34 
  and a.payment_id in ('4', '42', '41', '18', '9', '3', '63')
  
update transaction a, payment b set a.session = b.session where a.real_payment_id = b.id and a.session = 34 
  and a.payment_id in ('4', '42', '41', '18', '9', '3', '63')


update academic_record set current_session = '23' where current_session = '34'



-- queries for paymenmt prerequisite
ALTER TABLE `payment` CHANGE `prerequisite_fee` `prerequisite_fee` text  NULL  COMMENT '';

ALTER TABLE `payment` ADD COLUMN `payment_code` varchar(150)  NOT NULL  COMMENT '';


-- queries to update student.user_login with matric.surname@dlc.ui.edu.ng
Update academic_record a, students b set a.has_institution_email = '1', b.user_login = concat(LOWER(a.matric_number),'.',LOWER(b.lastname),'@dlc.ui.edu.ng') 
where a.student_id = b.id and a.has_matric_number = '1' and has_institution_email = '0' and a.session_of_admission = '23'

-- update student in current session 34 to current session while moving the value of current_session to outstanding_session
update academic_record set outstanding_session = 22 where current_session = 34
update payment set is_visible = 3 where session = 22
update payment set prerequisite_fee = '[]' where session = 34 and prerequisite_fee = 16

update academic_record set current_session = 23 where current_session = 34

update payment set session = 22 where session = 34

-- query to check those who doesn't have matric number but had paid
SELECT b.* from transaction a join students b on b.id = a.student_id join academic_record c on c.student_id = b.id 
where a.payment_id = '1' and a.payment_status in ('00', '01') and c.has_matric_number = '0'

update admission_programme_requirements set session = 35 where session = 23 and admission_id = 2

update transaction set session = 23 where (session = 34 or session = 33)
update transaction set transaction_ref = 'BURAPPROVED' where transaction_ref = 'BURSARYAPPROVED';


-- to know those who have not pay their topup fees
SELECT a.* FROM `transaction` a join students b on b.id = a.student_id join academic_record c on c.student_id = b.id 
where a.payment_status not in ('00', '01') and a.session = '23' and c.topup_session is not null 
ORDER BY `date_performed` DESC 

--duplicate payment using certain param
INSERT into payment (description,service_type_id,amount,subaccount_amount,service_charge,penalty_fee,prerequisite_fee,preselected_fee,
fee_breakdown,options,fee_category,programme,session,level,entry_mode,
level_to_include,entry_mode_to_include,date_due,status,is_visible,
date_created,discount_amount,payment_code,date_modified)
SELECT '2',service_type_id,amount,subaccount_amount,service_charge,penalty_fee,concat('["',id,'"]'),preselected_fee,
fee_breakdown,options,fee_category,programme,session,level,entry_mode,level_to_include,entry_mode_to_include,
date_due,status,is_visible,date_created,discount_amount,payment_code,date_modified 
from payment where description = '1' AND session = '23'

update transaction a set a.payment_status = '021' where cast(date_performed as date) >= '2023-11-01'


-- creating a temporal table for LMS
create table mdl_user_temp(id int(11) not null, matric_number varchar(25) not null, 
  application_number varchar(50) default null ) SELECT id, matric_number,application_number 
from academic_record where has_matric_number = '1'


SELECT * from mdl_user where username like 'uiodel%'

update mdl_user a, mdl_user_temp b set a.username = b.matric_number 
  where a.username = b.application_number and a.username like 'uiodel%'

-- finding student who paid part payment in first semester
SELECT * FROM `transaction` WHERE real_payment_id = '170' and payment_status in ('00', '01') 
and payment_id = '1'

UPDATE `transaction` set payment_option = '1A' WHERE real_payment_id = '170' and payment_status in ('00', '01') 
and payment_id = '1'

UPDATE `transaction` set payment_option = '1' WHERE payment_status in ('00', '01') and payment_id = '2' and 
real_payment_id != '170'


-- returns student with unknown session_of_admission
SELECT distinct if(c.date, c.date, 'Unknown') as name,b.student_id from transaction a join academic_record b 
on b.student_id = a.student_id left join sessions c on c.id = b.session_of_admission 
where a.payment_status in ('00','01') and (a.payment_id = '1') and a.session = '23' 
group by c.date,b.student_id having name = 'Unknown'  order by name desc


UPDATE academic_record b,transaction a set b.session_of_admission = b.year_of_entry 
where b.student_id = a.student_id and a.payment_status in ('00','01') 
and (a.payment_id = '1') and a.session = '23' and session_of_admission = '0'

-- to filter out suspension of study session period they paid
SELECT SQL_CALC_FOUND_ROWS a.student_id,c.lastname, c.firstname, c.othernames,d.matric_number,a.payment_status,
a.date_performed as paid_date,a.payment_description,e.date as year_of_entry,d.entry_mode,a.session from 
transaction a join fee_description b on b.id = a.payment_id join students c on c.id = a.student_id join 
academic_record d on d.student_id = c.id join sessions e on e.id = d.year_of_entry where b.code = 'Sus' and 
date(a.date_performed) between date('2023-11-01') and date('2024-04-08') and payment_status in ('00', '01')
order by paid_date asc

--
UPDATE transaction a,fee_description b set a.session = '23' where b.id = a.payment_id and b.code = 'Sus' 
and date(a.date_performed) between date('2023-11-01') and date('2024-04-09') and payment_status in ('00', '01')


SELECT * from medical_record where disabilities is not null and disabilities <> '' and disabilities <> 'No'
and disabilities <> 'Yes' ORDER BY `id` DESC

-- student biodata for those that paid in full without any outstanding fee from last session and current session
SELECT DISTINCT b.application_number,b.matric_number,c.lastname,c.firstname,c.othernames,c.gender,c.DoB,c.marital_status,
c.religion,c.phone,c.user_login as email,g.date as year_of_entry,c.state_of_origin,c.lga,c.nationality,d.name as programme,
e.name as faculty,f.name as department,b.current_level,b.mode_of_study,b.exam_center from transaction a
join academic_record b on b.student_id = a.student_id join students c on c.id = b.student_id join programme d on d.id = b.programme_id
join faculty e on e.id = d.faculty_id join department f on f.id = d.department_id join sessions g on g.id = b.year_of_entry
where payment_status in ('00', '01') and payment_id = '2' and (payment_option = '2' or payment_option = '2B') and session = '23'
and ((b.outstanding_session is null or b.outstanding_session = '') and payment_id = '65')


-- student biodata for those that paid part or full payment
SELECT DISTINCT b.application_number,b.matric_number,c.lastname,c.firstname,c.othernames,c.gender,c.DoB,c.marital_status,
c.religion,c.phone,c.user_login as email,g.date as year_of_entry,c.state_of_origin,c.lga,c.nationality,d.name as programme,
e.name as faculty,f.name as department,b.current_level,b.mode_of_study,b.exam_center from transaction a
join academic_record b on b.student_id = a.student_id join students c on c.id = b.student_id join programme d on d.id = b.programme_id
join faculty e on e.id = d.faculty_id join department f on f.id = d.department_id join sessions g on g.id = b.year_of_entry
where payment_status in ('00', '01') and payment_id = '1' and payment_option in ('1', '1A' '1B') and session = '23'


-- to check for applicant_putme who had paid sch-fees
SELECT * FROM `academic_record`,transaction WHERE entry_mode = 'O'' Level Putme' and 
transaction.student_id = academic_record.student_id and payment_id = '1' and 
payment_status in ('00', '01')


-- to update the email from apes to moodle
SELECT DISTINCT lower(b.user_login) as user_username,lower(c.alternative_email) as alternative_email FROM `course_enrollment` a join academic_record b
on b.student_id = a.student_id join students c on c.id = b.student_id where a.session_id = '35'


-- to update some missing values in academic record for applicant putme
UPDATE academic_record a, transaction b set entry_mode = 'O'' Level ', programme_duration = '5', 
min_programme_duration = '60', max_programme_duration = '60' and level_of_admission = '1' 
where b.student_id = a.student_id and payment_status in ('00', '01') and b.payment_id = '1' and 
a.applicant_type = 'applicant_post_utme' and entry_mode = 'O'' Level Putme'


-- to get aplicant putme who paid sch fee payment but their entry mode didn't change
SELECT a.* from academic_record a join transaction b on b.student_id = a.student_id where 
payment_status in ('00', '01') and b.payment_id = '1' and a.applicant_type = 'applicant_post_utme' and 
entry_mode = 'O'' Level Putme' 

-- to check if a column contain email data = 1389
SELECT * FROM `mdl_user` WHERE lastname REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}$'; 

-- swap emal for lastname and lastname for email on moodle user table
update `mdl_user` set lastname = email, email = lastname WHERE 
  lastname REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}$'


-- update academic record where year of entry is not id
UPDATE `academic_record`,sessions set year_of_entry = sessions.id WHERE 
academic_record.year_of_entry = sessions.date and length(academic_record.year_of_entry) > 3


-- get paid top-up but couldn't register for course yet in current session
SELECT transaction.* FROM transaction join fee_description ON fee_description.id = transaction.payment_id where
    fee_description.code = 'TUB' and transaction.payment_status in ('00', '01') and session = '35'


-- update paid top-up in current session
Update transaction, fee_description set payment_id = '1' where fee_description.id = transaction.payment_id 
and fee_description.code = 'TUB' and transaction.payment_status in ('00', '01') and session = '35'

-- update top-up to inactive
UPDATE payment, fee_description set is_visible = '0' where fee_description.id = payment.description and 
fee_description.code in ('TUB', 'TU')


-- selecting column with leading/trailing space
SELECT * FROM `academic_record` where entry_mode REGEXP '^ | $';


-- update academic_record.exam center when student make payment
UPDATE `transaction`,academic_record set exam_center = 'Lagos' WHERE transaction.student_id = academic_record.student_id 
and payment_id = '6' and payment_status in ('00', '01') and session = '35'

-- to filter out courses that exist in lsm_mood.course_enrollment
SELECT any_value(id) as id, course_shortname FROM remote_enrollment where course_shortname not in 
(SELECT course_shortname from remote_courses) group by course_shortname ORDER BY `remote_enrollment`.`course_shortname` ASC 


-- create a temporal remote_course from LMS mdl_course
create table remote_courses(id int(11) not null, fullname varchar(255) not null, 
  shortname varchar(150) not null) SELECT id, fullname, shortname from mdl_course

-- archive student transaction using date range

INSERT into transaction_archive (
      transaction_id,real_payment_id,payment_id,payment_description,payment_option,student_id,programme_id,session,level,
      transaction_ref,rrr_code,payment_status,beneficiary_1,beneficiary_2,payment_status_description,amount_paid,penalty_fee,
      service_charge,total_amount,payment_url,date_performed,date_completed,date_payment_communicated,preselected_payment,
      transaction_ref_id,subaccount_amount,mainaccount_amount,beneficiary_3,source_table) 
    SELECT 
      id,real_payment_id,payment_id,payment_description,payment_option,student_id,programme_id,session,level,
      transaction_ref,rrr_code,payment_status,beneficiary_1,beneficiary_2,payment_status_description,amount_paid,
      penalty_fee,service_charge,total_amount,payment_url,date_performed,date_completed,date_payment_communicated,
      preselected_payment,transaction_id,subaccount_amount,mainaccount_amount,beneficiary_3,'transaction' from transaction 
      where payment_status not in ('00', '01') and date(date_performed) >= '2024-11-01' and 
      date(date_performed) <= '2024-11-30';

      DELETE from transaction where payment_status not in ('00', '01') and 
      date(date_performed) >= '2024-11-01' and date(date_performed) <= '2024-11-30';


-- 
SELECT * FROM `transaction` WHERE 
payment_description = "Outstanding 2021/22 2nd Semester School Fee 2022/2" and payment_id = '2' and 
session = '22' and payment_status in ('00', '01')

--
SELECT * FROM `transaction` WHERE real_payment_id = '58' and payment_id = '2' and session = '22' 

-- Update payment_id = 65 to payment_id = 2, session = 22
UPDATE `transaction` set payment_id = '2', session = '22' WHERE payment_id = '65' and 
payment_status in ('00', '01') 

-- update exam center for those that their exam center wasn't updated accordingly
UPDATE transaction a, academic_record b SET b.exam_center = 'Lagos' WHERE a.student_id = b.student_id 
and payment_id = '6' and b.exam_center = 'Ibadan' and payment_status in ('00', '01') and 
session = '35'

-- delete multiple event data
DELETE FROM events_exams_meta 
WHERE events_id IN (SELECT id FROM events WHERE session_id = '35');

DELETE FROM events 
WHERE session_id = '35';

-- stats on telco numbers
SELECT 
    CASE 
        WHEN telco_number REGEXP '^[0-9]+$' THEN 'Glo'
        WHEN telco_number LIKE '%MTN%' THEN 'MTN'
        WHEN telco_number LIKE '%Airtel%' THEN 'Airtel'
        WHEN telco_number LIKE '%9mobile%' THEN '9mobile'
        ELSE 'Unknown'  -- Handles unexpected values
    END AS telco_provider, 
    COUNT(*) AS total 
FROM students 
WHERE telco_number IS NOT NULL AND telco_number <> ''
GROUP BY telco_provider;

-- archive applicants based on certain filters
INSERT IGNORE INTO applicants_archive  
SELECT applicants.* FROM applicants  
JOIN applicant_transaction ON applicant_transaction.applicant_id = applicants.id  
WHERE YEAR(applicants.date_created) < '2025'  
AND applicant_transaction.payment_status NOT IN ('01', '00');

-- delete archive applicants based on certain filters
DELETE FROM applicants  
WHERE YEAR(date_created) < '2025'  
AND EXISTS (  
    SELECT 1 FROM applicant_transaction  
    WHERE applicant_transaction.applicant_id = applicants.id  
    AND applicant_transaction.payment_status NOT IN ('01', '00')  
);

-- updating all applicant_putme back to their default entry_mode
SELECT a.* from academic_record a join transaction b on b.student_id = a.student_id where 
payment_status in ('00', '01') and b.payment_id = '1' and a.applicant_type = 'applicant_post_utme'

UPDATE academic_record a, transaction b set a.entry_mode = 'O'' Level Putme' where a.student_id = b.student_id 
and a.applicant_type = 'applicant_post_utme' and b.payment_status in ('00', '01') and 
b.payment_id = '1'

-- to determine if an applicant is from applicant putme
SELECT * from academic_record where application_number not like 'uiodel%' and year_of_entry = '35'

-- update applicant putme applicant_type to it actual data
UPDATE academic_record set applicant_type = 'applicant_post_utme' where application_number not 
like 'uiodel%' and year_of_entry = '35'

-- select applicant putme that paid part payment and completed their balance
SELECT DISTINCT b.* from academic_record a join transaction b on b.student_id = a.student_id 
where a.applicant_type = 'applicant_post_utme' and b.payment_status in ('00', '01') and 
b.payment_id = '1' and b.payment_option = '1B' 

-- get courses listed under course manager based on department
SELECT * from course_manager where course_id in (select id from courses where code like '%CSC%') 
and session_id = '35'

-- checking department total enrollment and total score
SELECT 
    d.id AS department_id,
    d.name AS department_name,
    COUNT(DISTINCT e.student_id) AS total_enrollments,
    COUNT(DISTINCT CASE WHEN e.total_score IS NOT NULL THEN e.student_id END) AS students_with_scores
FROM 
    department d
LEFT JOIN 
    courses c ON d.id = c.department_id
LEFT JOIN 
    course_enrollment e ON c.id = e.course_id
GROUP BY 
    d.id, d.name;

-- counting total score enrollment
SELECT COUNT(DISTINCT CASE WHEN e.total_score IS NOT NULL THEN e.student_id END) AS total from 
course_enrollment e where course_id = '55' and session_id = '1'


-- insert those that need to pay topup
INSERT INTO `edutech_live`.`payment` (`id`, `description`, `service_type_id`, `amount`, `subaccount_amount`, `service_charge`, `penalty_fee`, `prerequisite_fee`, `preselected_fee`, `fee_breakdown`, `options`, `fee_category`, `programme`, `session`, `level`, `entry_mode`, `level_to_include`, `entry_mode_to_include`, `date_due`, `status`, `is_visible`, `date_created`, `discount_amount`, `payment_code`, `date_modified`) VALUES 
(NULL, '2', '11214180160', '67191', '37794', 7944, 0, '["219"]', 0, NULL, '2', 1, '["27","28","29","41","26","42","38"]', 35, '["1"]', '["O'' Level Putme Top"]', '[]', '[]', '2025-02-05', 1, 1, '2024-08-27 07:22:46', '0', 'F_ED/SCI-SCF-L1OPT.L2D-SY2023/2024-SM1', '2025-04-02 00:47:49'),
(NULL, '2', '11214180160', '62391', '35094', 7944, 0, '["220"]', 0, NULL, '2', 1, '["9","11","12","17","18","19","20","21","25","35","36","37","43","44","10","13","39","14"]', 35, '["1"]', '["O'' Level Putme Top"]', '[]', '[]', '2025-02-05', 1, 1, '2024-08-27 07:36:27', '0', 'F_AR-SCF-L1OPT.L2D.L2F-SY2023/2024-SM1', '2025-04-02 00:47:49');

-- getting all student transaction who initiated course pack
SELECT * from transaction WHERE `real_payment_id` IN (224,225,226,227) 

-- get all student that paid course pack separated by commas
SELECT GROUP_CONCAT(DISTINCT student_id ORDER BY student_id SEPARATOR ',') AS student_ids
FROM transaction
WHERE `real_payment_id` IN (224,225,226,227) and payment_status in ('00', '01');

-- output for paid course pack
40708,42662,42676,42695,42703,42713,42738,42762,42763,42772,42782,42802,42819,42840,42849,43112,43893,43930

-- *** rectifying Space/Space-less Course Codes to use Space-less course in course_enrollment
SELECT 
    c1.id AS spaced_id,
    c1.code AS spaced_code,
    c2.id AS spaceless_id,
    c2.code AS spaceless_code
FROM courses c1
JOIN courses c2 ON REPLACE(c1.code, ' ', '') = c2.code
WHERE c1.code LIKE '% %' 
AND c1.code != c2.code;

-- Update enrollments to use existing space-less course IDs
UPDATE course_enrollment ce
JOIN courses c_spaced ON ce.course_id = c_spaced.id
JOIN courses c_spaceless ON REPLACE(c_spaced.code, ' ', '') = c_spaceless.code
SET ce.course_id = c_spaceless.id
WHERE c_spaced.code LIKE '% %'
AND c_spaced.code != c_spaceless.code;

-- Update approved_courses similarly
UPDATE approved_courses ac
JOIN courses c_spaced ON ac.course_id = c_spaced.id
JOIN courses c_spaceless ON REPLACE(c_spaced.code, ' ', '') = c_spaceless.code
SET ac.course_id = c_spaceless.id
WHERE c_spaced.code LIKE '% %'
AND c_spaced.code != c_spaceless.code;

-- First identify which spaced courses can be safely deleted
SELECT c_spaced.*
FROM courses c_spaced
JOIN courses c_spaceless ON REPLACE(c_spaced.code, ' ', '') = c_spaceless.code
WHERE c_spaced.code LIKE '% %'
AND c_spaced.code != c_spaceless.code
AND NOT EXISTS (
    SELECT 1 FROM course_enrollment 
    WHERE course_id = c_spaced.id
)
AND NOT EXISTS (
    SELECT 1 FROM approved_courses 
    WHERE course_id = c_spaced.id
);

-- Then delete them (if the above query returns only safe-to-delete records)
DELETE c_spaced
FROM courses c_spaced
JOIN courses c_spaceless ON REPLACE(c_spaced.code, ' ', '') = c_spaceless.code
WHERE c_spaced.code LIKE '% %'
AND c_spaced.code != c_spaceless.code
AND NOT EXISTS (
    SELECT 1 FROM course_enrollment 
    WHERE course_id = c_spaced.id
)
AND NOT EXISTS (
    SELECT 1 FROM approved_courses 
    WHERE course_id = c_spaced.id
);

-- *** end rectifying Space/Space-less Course Codes

-- getting staffs with multiple same email
SELECT u.*
FROM staffs u
JOIN (
    SELECT email
    FROM staffs
    GROUP BY email
    HAVING COUNT(*) > 1
) dup ON u.email = dup.email
ORDER BY u.email;


-- verified
SELECT distinct programme.name, count(distinct transaction.student_id) as total from
transaction join students on transaction.student_id = students.id join academic_record
on academic_record.student_id = students.id join programme on programme.id = transaction.programme_id
where academic_record.session_of_admission = '35' and students.is_verified = '1' and
payment_status in ('00', '01') and payment_id = '16' group by programme.name
order by programme.name asc

-- check duplicate emails
SELECT id, email, row_num FROM ( SELECT id, email, ROW_NUMBER() OVER
  (PARTITION BY email ORDER BY id) AS row_num FROM staffs ) t WHERE row_num > 1

TRUNCATE TABLE `transaction`;
TRUNCATE TABLE `academic_record`;
TRUNCATE TABLE `students`;

-- query to figure out those with outstanding but bypassed to still pay
SELECT DISTINCT t35.student_id
FROM transaction t35
WHERE t35.session = 35
  AND t35.payment_status IN ('00', '01')
  AND t35.payment_id IN ('1', '2')
  AND EXISTS (
    SELECT 1
    FROM transaction t_prev
    WHERE t_prev.student_id = t35.student_id
      AND t_prev.session IN (22, 23)
      AND t_prev.payment_status IN ('00', '01')
      AND t_prev.payment_id IN ('1', '2')
    GROUP BY t_prev.student_id, t_prev.payment_id
    HAVING (
      -- No full payment
      COUNT(DISTINCT CASE WHEN t_prev.payment_option = t_prev.payment_id THEN t_prev.id END) = 0
      AND NOT (
        -- Not both partial payments (checking for at least one of each)
        COUNT(DISTINCT CASE WHEN t_prev.payment_option = CONCAT(t_prev.payment_id, 'A') THEN t_prev.id END) > 0
        AND COUNT(DISTINCT CASE WHEN t_prev.payment_option = CONCAT(t_prev.payment_id, 'B') THEN t_prev.id END) > 0
      )
    )
  );

  -- gender acceptance fee stats
  SELECT distinct CASE
          WHEN lower(gender) = 'male' THEN 'Male'
          WHEN lower(gender) = 'female' THEN 'Female'
          ELSE 'Null'
          END as gender_name,
          count(distinct b.student_id) as total from transaction b join
        students a on b.student_id = a.id where
        b.payment_status in ('00','01') and b.session = '35' and b.payment_id = '16' group by gender_name order by gender_name


-- filtering student that needs to pay RuS and SuS and are finalist
SELECT DISTINCT t35.student_id, ac.matric_number
FROM transaction t35
JOIN academic_record ac ON ac.student_id = t35.student_id
WHERE t35.session = 35
  AND t35.payment_id = '2'
  AND t35.payment_status IN ('00', '01')
  AND t35.level >= 5
  -- Must NOT have paid Reactivation Fee in same session
  AND t35.student_id NOT IN (
    SELECT student_id
    FROM transaction
    WHERE session = 35
      AND payment_id = '83'
      AND payment_status IN ('00', '01')
  )
  -- Must NOT have paid anything in session 23
  AND t35.student_id NOT IN (
    SELECT student_id
    FROM transaction
    WHERE session = 23
      AND payment_id IN ('1','1A','1B','2','2A','2B')
      AND payment_status IN ('00', '01')
  );

-- return students who paid both 2A and 2B for session 23, but have different levels for each
SELECT 
  t2a.student_id, 
  t2a.session,
  t2a.payment_id AS payment_id_2A,
  t2a.payment_option AS payment_option_2A,
  t2a.level AS level_2A, 
  t2b.payment_id AS payment_id_2B,
  t2b.payment_option AS payment_option_2B,
  t2b.level AS level_2B
FROM transaction t2a
JOIN transaction t2b 
  ON t2a.student_id = t2b.student_id 
  AND t2a.session = t2b.session
WHERE 
  t2a.payment_option = '2A' AND t2a.payment_id = 2
  AND t2a.payment_status IN ('00', '01')
  AND t2b.payment_option = '2B' AND t2b.payment_id = 2
  AND t2b.payment_status IN ('00', '01') 
  AND t2a.session = '23'
  AND t2a.level != t2b.level;

-- now update for students who paid both 2A and 2B for session 23, but have different levels for each
UPDATE transaction t2b
JOIN transaction t2a 
  ON t2a.student_id = t2b.student_id 
  AND t2a.session = t2b.session
  AND t2a.payment_option = '2A' AND t2a.payment_id = 2
  AND t2a.payment_status IN ('00', '01')
  AND t2b.payment_option = '2B' AND t2b.payment_id = 2
  AND t2b.payment_status IN ('00', '01')
SET t2b.level = t2a.level
WHERE 
  t2a.session = '23'
  AND t2a.level != t2b.level;



SELECT t.student_id, COUNT(t.id) AS number_of_2A_payments, MAX(t.reg_num) AS reg_num, MAX(t.amount_paid) AS amount_paid, MAX(t.total_amount) AS total_amount, MAX(t.service_charge) AS service_charge, MAX(t.payment_status) AS payment_status, GROUP_CONCAT(t.transaction_ref ORDER BY t.date_performed) AS transaction_refs, MAX(t.date_performed) AS last_payment_date, MAX(t.payment_description) AS last_payment_description FROM `transaction` t WHERE t.payment_option = '2A' AND t.payment_status IN ('00', '01') -- Paid or pending payments AND t.session = '35' AND t.service_charge = '7944' GROUP BY t.student_id HAVING COUNT(t.id) > 1 

-- get student that paid mulitple sch-fees payment with service charge as 7944
SELECT t.id, t.student_id, t.reg_num, t.payment_option, t.amount_paid, t.total_amount, t.service_charge, 
t.payment_status, t.transaction_ref, t.rrr_code, t.date_performed, t.payment_description FROM 
`transaction` t WHERE t.payment_option = '2A' AND t.payment_status IN ('00', '01') 
AND t.session = '35' AND t.service_charge = '7944' AND t.student_id IN 
( SELECT student_id FROM `transaction` WHERE payment_option = '2A' AND payment_status IN ('00', '01') 
  AND session = '35' AND service_charge = '7944' GROUP BY student_id HAVING COUNT(id) > 1 ) 
ORDER BY `t`.`student_id` ASC 

-- alternative query for paid mulitple sch-fees payment
SELECT t.id, t.student_id, ar.matric_number, t.reg_num, t.payment_option, t.amount_paid, t.total_amount, 
t.service_charge, t.payment_status, t.transaction_ref, t.rrr_code, t.date_performed, t.payment_description 
FROM `transaction` t JOIN `academic_record` ar ON t.student_id = ar.student_id 
WHERE t.service_charge = '7944' AND t.session = '35'
AND t.payment_status IN ('00', '01')
AND t.payment_id IN (1, 2)
AND (t.student_id, t.payment_option) IN 
( SELECT student_id, payment_option FROM `transaction` WHERE service_charge = '7944' AND session = '35' 
  AND payment_status IN ('00', '01') AND payment_id IN (1, 2) 
  GROUP BY student_id, payment_option HAVING COUNT(id) > 1 ) ORDER BY ar.matric_number

-- to get student who didn't pay acceptance fee but admitted already
SELECT a.*
FROM `academic_record` a
WHERE a.year_of_Entry IN ('23', '35') 
  AND NOT EXISTS (
      SELECT 1
      FROM transaction b
      WHERE b.student_id = a.student_id
      and payment_id = '16'
  );


SELECT a.*
FROM `academic_record` a
WHERE a.year_of_Entry IN ('36') and year_of_entry != current_session 
and has_matric_number <> 1
AND NOT EXISTS (
      SELECT 1
      FROM transaction b
      WHERE b.student_id = a.student_id
      and payment_id = '16'
  );

SELECT a.*
FROM `academic_record` a
WHERE a.year_of_Entry IN ('36') and year_of_entry != current_session 
and has_matric_number <> 1
AND NOT EXISTS (
      SELECT 1
      FROM transaction b
      WHERE b.student_id = a.student_id
  );

-- extract matric number from email if exist and username still using uiodel, then
-- update the username with the extracted matric from the email
SELECT 
    username,
    email,
    SUBSTRING_INDEX(email, '.', 1) AS new_username
FROM mdl_user
WHERE 
    email LIKE '%.%@dlc.ui.edu.ng'
    AND username LIKE 'uiodel%';

-- to avoid duplicate username error and safely update unique username
UPDATE mdl_user u
JOIN (
    SELECT 
        u.id,
        SUBSTRING_INDEX(u.email, '.', 1) AS new_username
    FROM mdl_user u
    LEFT JOIN (
        SELECT 
            SUBSTRING_INDEX(email, '.', 1) AS email_prefix
        FROM mdl_user
        WHERE email LIKE '%.%@dlc.ui.edu.ng'
          AND username LIKE 'uiodel%'
        GROUP BY email_prefix
        HAVING COUNT(*) = 1  -- Only unique email prefixes
    ) unique_prefixes ON SUBSTRING_INDEX(u.email, '.', 1) = unique_prefixes.email_prefix
    LEFT JOIN mdl_user existing_user 
        ON existing_user.mnethostid = u.mnethostid
       AND existing_user.username = SUBSTRING_INDEX(u.email, '.', 1)
       AND existing_user.id <> u.id
    WHERE 
        u.email LIKE '%.%@dlc.ui.edu.ng'
        AND u.username LIKE 'uiodel%'
        AND unique_prefixes.email_prefix IS NOT NULL
        AND existing_user.id IS NULL  -- No username conflict globally
) safe_updates ON u.id = safe_updates.id
SET u.username = safe_updates.new_username;


DELETE FROM course_request_claim_items where course_request_claim_id in 
  (SELECT id from course_request_claims where id in (171,172,173))

DELETE from course_request_claims where id in (171,172,17);




