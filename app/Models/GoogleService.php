<?php

namespace App\Models;
use Exception;

require_once ROOTPATH . 'vendor/autoload.php';

class GoogleService
{

	public static function initAuth(string $type, string $subject)
	{
		putenv('GOOGLE_APPLICATION_CREDENTIALS=gmail-service-account.json');
		$client = new Google\Client();
		$client->useApplicationDefaultCredentials();

		switch ($type) {
			case 'read_gmail':
				$client->addScope(Google\Service\Gmail::GMAIL_READONLY);
				$client->setSubject($subject);
				return new Google\Service\Gmail($client);
				break;

			case 'create_gmail':
				$client->addScope([Google\Service\Directory::ADMIN_DIRECTORY_USER, Google\Service\Directory::ADMIN_DIRECTORY_USER_SECURITY, Google\Service\Directory::ADMIN_DIRECTORY_GROUP, Google\Service\Directory::ADMIN_DIRECTORY_GROUP_MEMBER]);
				$client->setSubject($subject);
				return new Google\Service\Directory($client);
				break;

		}

	}

	public function listMessages($subject, $pageToken = null)
	{
		$gmail = self::initAuth('read_gmail', $subject);
        return $gmail->users_messages->listUsersMessages('me', array("maxResults" => 15, "pageToken" => $pageToken));
	}

	public function getMessage($subject, $messageID)
	{
		$gmail = self::initAuth('read_gmail', $subject);
        return $gmail->users_messages->get('me', $messageID);
	}

	public function getAttachments($subject, $messageID, $attachmentID)
	{
		$gmail = self::initAuth('read_gmail', $subject);
        return $gmail->users_messages_attachments->get('me', $messageID, $attachmentID);
	}

	public static function createInstitutionEmail($lastname, $firstname, $email)
	{
		$subject = get_setting('email_admin_account');
		$service = self::initAuth('create_gmail', $subject);
		$password = strtolower($lastname) . '12345';

		$user = new Google\Service\Directory\User();
		$name = new Google\Service\Directory\UserName();

		$name->setGivenName($firstname);
		$name->setFamilyName($lastname);
		$user->setName($name);
		$user->setPrimaryEmail($email);
		$user->setHashFunction("MD5");
		$user->setPassword(hash("md5", $password));

		try {
			$createUserResult = $service->users->insert($user);
			return $createUserResult->getPrimaryEmail();
		} catch (Exception $e) {
			log_message('error', "Google:user:directory -> " . $e->getMessage());
			return null;
		}

	}
}
