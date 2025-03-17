<?php
/**
 *
 */

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require 'third_party/PHPMailer/src/Exception.php';
require 'third_party/PHPMailer/src/PHPMailer.php';
require 'third_party/PHPMailer/src/SMTP.php';

class Messaging extends CI_Model
{

	function __construct()
	{
		parent::__construct();
		$this->load->library('parser');
	}

	public function sendUploadEmailNotification($log_file_path)
	{
		$variables = array('course' => 'misc');
		$recipient = "edutechportal@gmail.com";
		$this->send_mail('score-upload-log', $recipient, $variables, 'ATTENTION!  Score Upload Log, ', '', $log_file_path);
	}

	public function sendUploadCopyEmailNotification(string $recipient, array $variables, string $subject, array $ccList, string $log_file_path)
	{
		$this->send_mail('score-upload-log', $recipient, $variables, $subject, $ccList, $log_file_path);
	}

	/**
	 * Get field name by id
	 *
	 * @param string $template
	 * @param string $to
	 * @param string $variables
	 * @param string $custom_subject
	 * @param string $cc
	 * @param string $attachment
	 * @return void
	 * @throws Exception
	 */
	public function send_mail($template, $to, $variables = '', $custom_subject = '', $cc = '', $attachment = '')
	{
		return true;
		//Query database to get template
		$query = $this->db->get_where('templates', array('slug' => $template, 'type' => 'email', 'active' => 1));
		$this->load->library('parser');
		$mailer = new PHPMailer(true);
		$mailer->addCustomHeader('useragent', 'Infosys');
		$mailer->isSMTP();
		$mailer->Host = get_setting('email_server_url');//'smtp.gmail.com';
		$mailer->SMTPAuth = true;
		$mailer->Username = get_setting('email_server_url_username');//'edtech@dlc.ui.edu.ng';
		$mailer->Password = get_setting('email_server_url_password');//'ed1Naija';
		$mailer->SMTPSecure = 'tls';
		$mailer->Port = get_setting('email_server_port');//587;
		$mailer->isHTML(true);

		try {
			foreach ($query->result() as $row) {
				$subject = ($custom_subject != null) ? $custom_subject : $row->name;
				$message = base64_decode($row->content);
				if ($variables) {
					$message = $this->parser->parse_string($message, $variables, TRUE);
				}
				$mailer->setFrom($template . '@' . get_setting('email_domain'), get_setting('email_domain_name'));
				$mailer->addAddress($to);
				if ($cc) {
					foreach ($cc as $c) {
						$mailer->addCC($c);
					}
				}
				if ($attachment) {
					$mailer->addAttachment($attachment);
				}
				$mailer->Subject = $subject;
				$mailer->Body = $message;
				$mailer->send();
			}

		} catch (Exception $e) {
			// echo 'Message could not be sent.';
			// echo 'Mailer Error: ' . $mailer->ErrorInfo;
			// exit;
		}

	}

}


