<?php

namespace App\Models;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * The model for sending emails
 */
class Mailer
{
	private PHPMailer $mailer;

	/**
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->mailer = new PHPMailer(true);
		$this->privateMailConfig();
        helper('string');
	}

	/**
	 * @throws Exception
	 */
	private function privateMailConfig(): void
	{
		$this->mailer->addCustomHeader('useragent', 'Infosys');
		// $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
		$this->mailer->SMTPDebug = SMTP::DEBUG_SERVER | SMTP::DEBUG_CONNECTION;
		$this->mailer->isSMTP();
		$this->mailer->CharSet = "utf-8";
		$this->mailer->Host = get_setting('email_server_url');
		$this->mailer->SMTPAuth = true;
		$this->mailer->AuthType = 'LOGIN';
		$this->mailer->Username = get_setting('email_server_url_username');
		$this->mailer->Password = get_setting('email_server_url_password');
		$this->mailer->SMTPSecure = false;
		$this->mailer->SMTPAutoTLS = false;
		$this->mailer->Port = get_setting('email_server_port');
		$this->mailer->isHTML(true);

	}

	public function sendUploadCopyEmailNotification(string $recipient, array $variables, string $subject, array $ccList, $attachment = null): ?bool
	{
		return $this->send_new_mail('score-upload-log', $recipient, $variables, $subject, $ccList, $attachment);
	}

	/**
	 * This picks mail content from the db to send mail
	 */
	public function send_new_mail($template, $to, $variables = '', $custom_subject = null, $cc = null, $attachment = null)
	{
		$this->load->library('parser');
		$query = $this->db->get_where('templates', array('slug' => $template, 'type' => 'email', 'active' => 1));
		$mailer = $this->mailer;
		$emailDomain = $template . '@' . get_setting('email_domain');
		$emailDomainName = get_setting('email_domain_name');
		try {
			foreach ($query->result() as $row) {
				$mailer->clearAddresses();
				$mailer->clearAttachments();
				$mailer->clearCCs();

				$subject = ($custom_subject != null) ? $custom_subject : $row->name;
				$message = base64_decode($row->content);
				if ($variables) {
					$message = $this->parser->parse_string($message, $variables, TRUE);
				}
				$mailer->setFrom($emailDomain, $emailDomainName);

				if (is_array($to)) {
					foreach ($to as $recipient) {
						$mailer->addAddress($recipient);
					}
				} else {
					$mailer->addAddress($to);
				}

				if ($cc) {
					foreach ($cc as $c) {
						$mailer->addCC($c);
					}
				}

				if ($attachment) {
					if (is_array($attachment)) {
						foreach ($attachment as $attach) {
							$mailer->addAttachment($attach);
						}
					} else {
						$mailer->addAttachment($attachment);
					}
				}
				$mailer->Subject = $subject;
				$mailer->Body = $message;
				$mailer->send();
				return true;
			}

		} catch (Exception $e) {
			// echo 'Message could not be sent.';
			$message = 'Edutech::Mailer:Error: ' . $mailer->ErrorInfo;
			log_message('error', $message);
			return false;
		}

	}

	/**
	 * This send mail getting the content directly
	 */
	public function sendMail($template, $to, $subject, $content, $cc = array(), $attachment = false)
	{
		try {
			$this->mailer->clearAddresses();
			$this->mailer->clearAttachments();
			$this->mailer->clearCCs();

			if (is_array($to)) {
				foreach ($to as $recipient) {
					$this->mailer->addAddress($recipient);
				}
			} else {
				$this->mailer->addAddress($to);
			}

			if ($cc) {
				if (is_array($cc)) {
					foreach ($cc as $c) {
						$this->mailer->addCC($c);
					}
				} else {
					$this->mailer->addCC($cc);
				}
			}
			if ($attachment) {
				if (is_array($attachment)) {
					foreach ($attachment as $attach) {
						$this->mailer->addAttachment($attach);
					}
				} else {
					$this->mailer->addAttachment($attachment);
				}
			}
			$this->mailer->setFrom($template . '@' . get_setting('email_domain'), get_setting('email_domain_name'));
			$this->mailer->Subject = $subject;
			$this->mailer->Body = $content;
			$this->mailer->send();
			return true;
		} catch (Exception $e) {
			// dddump($this->mailer->ErrorInfo);
			$message = $this->mailer->ErrorInfo ?? 'Message could not be sent.';
			log_message('error', "Edutech::Mailer:Error:" . $message);
			log_message('error', "SMTP Debug Output: " . $this->mailer->Debugoutput);
			return false;
		}

	}

}
