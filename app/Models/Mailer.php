<?php

namespace App\Models;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Config\Services;

class Mailer
{
    private PHPMailer $mailer;

    /**
     * Constructor to initialize PHPMailer and configure it.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->privateMailConfig();
    }

    /**
     * Configures PHPMailer with SMTP settings.
     *
     * @throws Exception
     */
    private function privateMailConfig(): void
    {
        $this->mailer->addCustomHeader('useragent', 'Infosys');
        // $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER | SMTP::DEBUG_CONNECTION; // Debugging enabled
        $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
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

    /**
     * Sends an email notification for upload copy.
     *
     * @param string $recipient The recipient email address.
     * @param array $variables Variables to replace in the email template.
     * @param string $subject The email subject.
     * @param array $ccList CC recipients.
     * @param mixed $attachment Attachment file(s).
     * @return bool|null
     */
    public function sendUploadCopyEmailNotification(string $recipient, array $variables, string $subject, array $ccList, $attachment = null): ?bool
    {
        return $this->sendNewMail('score-upload-log', $recipient, $variables, $subject, $ccList, $attachment);
    }

    /**
     * Sends an email using a template from the database.
     *
     * @param string $template The template slug.
     * @param mixed $to Recipient(s).
     * @param array|string $variables Variables to replace in the template.
     * @param string|null $custom_subject Custom email subject.
     * @param array|null $cc CC recipients.
     * @param mixed $attachment Attachment file(s).
     * @return bool
     */
    public function sendNewMail(string $template, $to, $variables = '', ?string $custom_subject = null, ?array $cc = null, $attachment = null): bool
    {
        $parser = Services::parser();
        $db = db_connect();

        // Fetch the email template from the database
        $query = $db->table('templates')
            ->where('slug', $template)
            ->where('type', 'email')
            ->where('active', 1)
            ->get();

        $emailDomain = $template . '@' . get_setting('email_domain');
        $emailDomainName = get_setting('email_domain_name');

        try {
            foreach ($query->getResult() as $row) {
                $this->mailer->clearAddresses();
                $this->mailer->clearAttachments();
                $this->mailer->clearCCs();

                $subject = $custom_subject ?? $row->name;
                $message = base64_decode($row->content);

                if ($variables) {
                    $message = $parser->setData($variables)->renderString($message);
                }
                $this->mailer->setFrom($emailDomain, $emailDomainName);

                if (is_array($to)) {
                    foreach ($to as $recipient) {
                        $this->mailer->addAddress($recipient);
                    }
                } else {
                    $this->mailer->addAddress($to);
                }

                if ($cc) {
                    foreach ($cc as $c) {
                        $this->mailer->addCC($c);
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

                $this->mailer->Subject = $subject;
                $this->mailer->Body = $message;

                if ($this->mailer->send()) {
                    return true;
                } else {
                    log_message('error', 'Edutech::Mailer:Error: ' . $this->mailer->ErrorInfo);
                    return false;
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Edutech::Mailer:Error: ' . $e->getMessage());
            return false;
        }

        return false;
    }

    /**
     * Sends an email with direct content (no template from the database).
     *
     * @param string $template The template slug (used for the "From" address).
     * @param mixed $to Recipient(s).
     * @param string $subject The email subject.
     * @param string $content The email content.
     * @param array $bcc
     * @param mixed $attachment Attachment file(s).
     * @return bool
     */
    public function sendMail(string $template, $to, string $subject, string $content, array $bcc = [], $attachment = false): bool
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

            if ($bcc) {
                if (is_array($bcc)) {
                    foreach ($bcc as $c) {
                        $this->mailer->addCC($c);
                    }
                } else {
                    $this->mailer->addCC($bcc);
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

            if ($this->mailer->send()) {
                return true;
            } else {
                log_message('error', 'Edutech::Mailer:Error: ' . $this->mailer->ErrorInfo);
                return false;
            }
        } catch (Exception $e) {
            log_message('error', 'Edutech::Mailer:Error: ' . $e->getMessage());
            return false;
        }
    }
}