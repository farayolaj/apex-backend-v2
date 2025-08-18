<?php

namespace App\Libraries;

use CodeIgniter\Config\Factories;
use InvalidArgumentException;

class Mail
{

    /**
     * Send an email notification for score upload
     * @param string $recipient
     * @param array $variables
     * @param string $subject
     * @param array $ccList
     * @param null $attachment
     * @return bool|null
     */
    public static function sendUploadCopyEmailNotification(string $recipient, array $variables, string $subject, array $ccList, $attachment = null): ?bool
    {
        $mailer = Factories::models('Mailer');
        return $mailer->sendNewMail('score-upload-log', $recipient, $variables, $subject, $ccList, $attachment);
    }

    /**
     * Send a confirmation email for attendance
     * @param string $to
     * @param array{
     *     code: string, title: string, fullname: string, venue: string, category: string,
     *     active_session: string, active_semester: string, event_date: string, time: string
     *     } $param
     * @return bool
     */
    public static function sendConfirmInteractive(string $to, array $param): bool
    {
        if (!isset($param['code']) || !isset($param['title']) || !isset($param['fullname'])) {
            throw new InvalidArgumentException(
                'Course array must contain "code" and "title" keys'
            );
        }

        $mailer = Factories::models('Mailer');
        $courseCode = $param['code'];
        $courseTitle = $param['title'];
        $fullname = $param['fullname'];
        $venue = $param['venue'];
        $category = $param['category'];
        $activeSession = $param['active_session'];
        $activeSemester = $param['active_semester'];
        $date = $param['event_date'] ?? date('Y-m-d');
        $time = $param['time'];
        $bcc = [
            'edutechportal.org@gmail.com',
            'edutechportal@dlc.ui.edu.ng',
            'ebomobowale@yahoo.com',
            'aboard.junaid@gmail.com',
        ];

        $subject = "Confirmation of {$activeSession} {$activeSemester} {$courseCode} Lecture Event";

        $content = "
			<p>Dear {$fullname},</p>

			<p>This is to confirm that the {$courseCode} {$courseTitle} {$category} Interactive Session took place on {$date} {$time}.</p>
			
			<p>We appreciate your dedication and commitment to the success of UIDLC and its learners.</p>
			
			<p>Thank you.</p>
			
			<p>Best regards, <br />
			Programme Delivery Team, <br />
			University of Ibadan Distance Learning Centre</p>
		";
        return $mailer->sendMail('ui.notice', $to, $subject, $content, $bcc);
    }

    /**
     * Builds and sends an email with the specified parameters.
     * @param string $to The recipient's email address.
     * @param string $subject The subject line of the email.
     * @param mixed $content The content/body of the email.
     * @return bool|null Returns true if the email was sent successfully, false if it failed, or null on error.
     */
    public static function sendMailBuilder(string $to, string $subject, $content): ?bool
    {
        if(ENVIRONMENT === 'development') return true;

        $mailer = Factories::models('Mailer');
        return $mailer->sendMail('ui.notice', $to, $subject, $content);
    }

    /**
     * Send a confirmation email for attendance
     * @param string $to
     * @param array{code: string, title: string, fullname: string, session_name: string, total_graded: integer} $param
     * @return bool
     */
    public static function sendGradeNotification(string $to, array $param): bool
    {
        if (!isset($param['code'])
            || !isset($param['title'])
            || !isset($param['fullname'])
            || !isset($param['session_name'])
            || !isset($param['total_graded'])
        ) {
            throw new InvalidArgumentException(
                'Course array must contain "code" and "title" keys'
            );
        }

        $mailer = Factories::models('Mailer');
        $courseCode = $param['code'];
        $courseTitle = $param['title'];
        $fullname = $param['fullname'];
        $sessionName = $param['session_name'];
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $totalGraded = $param['total_graded'];

        $subject = "{$sessionName} Academic Session Graded Student Count Submission Confirmation - [{$courseTitle} - {$courseCode}]";
        $bcc = [
            'edutechportal.org@gmail.com',
            'edutechportal@dlc.ui.edu.ng',
            'ebomobowale@yahoo.com',
            'aboard.junaid@gmail.com'
        ];

        $content = "
			<p> Dear {$fullname}, </p>
			
			<p>
				This email confirms your submission of <b>{$totalGraded} as the total number of graded students<b/> for 
				<b>{$courseTitle} - {$courseCode} on {$date} at {$time}.</b>
			</p>
			
			<p>
				<strong>Important:</strong> This submitted number will be used as the basis for your claim submission. 
				Please be aware that if this number is found to be at variance with the actual number of graded students, your claim will be declined.
				<br /><br />
				If you believe there has been an error or did not make this submission, 
				please contact the UIDLC Programme Delivery / Faculty Dashboard support team.
				You also have the option to update this record on your faculty dashboard before finalizing your claim submission.
			</p>
			<p></p>
			
			<p>Thank you.</p>
			
			<p>UIDLC Faculty Dashboard - Apex - Support Team</p>
		";

        if(ENVIRONMENT === 'development') log_message('info', "[EVENT:MAILER:QUEUE] was fired to" . $to);
        return $mailer->sendMail('ui.notice', $to, $subject, $content, $bcc);
    }

}