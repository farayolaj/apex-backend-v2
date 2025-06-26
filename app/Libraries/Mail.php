<?php

namespace App\Libraries;

use CodeIgniter\Config\Factories;

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
     * @param array{code: string, title: string, fullname: string} $param
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
        $subject = "Confirmation of {$courseCode} Lecture Event";
        $bcc = [
            'edutechportal.org@gmail.com',
            'edutechportal@dlc.ui.edu.ng',
            'ebomobowale@yahoo.com',
            'aboard.junaid@gmail.com'
        ];
        $date = date('Y-m-d');
        $time = $param['time'];
        $content = "
			<p>Dear {$fullname},</p>

			<p>This is to confirm that the {$courseCode} {$courseTitle} interactive session took place on {$date} at {$time} in {$venue}</p>
			
			<p>We appreciate your dedication and commitment to the success of UIDLC and its learners.</p>
			
			<p>Thank you.</p>
			
			<p>Best regards, <br />
			Dr A. Junaid <br />
			Programme Officer, <br />
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
        $mailer = Factories::models('Mailer');
        return $mailer->sendMail('ui.notice', $to, $subject, $content);
    }

}