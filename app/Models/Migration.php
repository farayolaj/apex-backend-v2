<?php

class Migration extends CI_Model
{

	private const AUTHURL = "https://admissions.ui.edu.ng/backend/apis/api_v1/jwt_api/auth.php";
	private const APIURL = "https://admissions.ui.edu.ng/backend/apis/api_v1/jwt_api/api.php";

	public function curlPost($url, $header = null, $jsonData = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		($header) ? curl_setopt($ch, CURLOPT_HTTPHEADER, $header) : null;
		($jsonData) ? curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData) : null;

		$response = curl_exec($ch);
		if ($response === false) {
			$error = "Error: " . curl_error($ch);
			curl_close($ch);
			return ['status' => false, 'message' => $error];
		}
		curl_close($ch);
		return json_decode($response, true);
	}

	private function getAuthToken()
	{
		// $this->load->driver('cache', array('adapter' => 'file'));
		// if ($token = $this->cache->get('uiadmission_token_auth')) {
		//	return $token;
		// }

		$username = $this->config->item('admission_username');
		$password = $this->config->item('admission_password');

		$param = [
			'username' => $username,
			'password' => $password,
		];
		$response = $this->curlPost(self::AUTHURL, null, $param);

		if (!$response['status']) {
			return ['status' => false, 'message' => $response['message']];
		}

		if ($response['status'] !== 'success') {
			return ['status' => false, 'message' => "Authentication failed: " . $response['message']];
		}

		$jwtToken = $response['token'];
		// $this->cache->save('uiadmission_token_auth', $jwtToken, 3600); // 1hr
		return $jwtToken;
	}

	public function fetchData()
	{
		$accessToken = $this->getAuthToken();

		$response = $this->curlPost(self::APIURL, [
			'Content-Type: application/json',
		], json_encode(['token' => $accessToken]));

		if (!$response) {
			$message = @$response['message'] ?: 'Something went wrong';
			return ['status' => false, 'message' => $message];
		}

		if (!$response['status']) {
			return ['status' => false, 'message' => $response['message']];
		}

		if ($response['status'] !== 'success') {
			return ['status' => false, 'message' => "Error: " . $response['message']];
		}

		return $response['data']['records'];
	}
}
