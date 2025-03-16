#!/usr/bin/php

<?php

// Define log file path
define('LOG_DIRECTORY', 'cronlogs/script_logs/');
define('LOG_FILE_NAME', 'import_log_');

// Define batch size for processing
define('BATCH_SIZE', 500);

// Database connection configurations
$db1_config = [
	'host' => '127.0.0.1',
	'username' => 'uidlc',
	'password' => '6YFUwapUJ9eGKn9c',
	'database' => 'mc403'
];

// db from which to get emails
$db2_config = [
	'host' => '127.0.0.1',
	'username' => 'uidlc',
	'password' => '6YFUwapUJ9eGKn9c',
	'database' => 'lms_remote_mood'
];

// Function to log messages to a file
if (!function_exists('logMessage')) {
	function logMessage($message, $type = 'INFO')
	{
		if (!file_exists(LOG_DIRECTORY)) {
			mkdir(LOG_DIRECTORY, 0777, true);
		}

		$logFileName = LOG_DIRECTORY . LOG_FILE_NAME . date('Y-m-d') . '.log';
		$date = date('Y-m-d H:i:s');
		$logMessage = "[$date][$type] $message" . PHP_EOL;
		file_put_contents($logFileName, $logMessage, FILE_APPEND);
	}
}

// Function to clean up old log files
if (!function_exists('cleanupOldLogs')) {
	function cleanupOldLogs($daysToKeep = 7)
	{
		$files = glob(LOG_DIRECTORY . LOG_FILE_NAME . '*.log');
		$cutoffTime = strtotime("-{$daysToKeep} days");

		foreach ($files as $file) {
			// Extract date from filename
			$pattern = '/' . LOG_FILE_NAME . '(\d{4}-\d{2}-\d{2})\.log/';
			if (preg_match($pattern, $file, $matches)) {
				$fileDate = strtotime($matches[1]);

				// Delete files older than cutoff time
				if ($fileDate < $cutoffTime) {
					unlink($file);
					logMessage("Deleted old log file: " . basename($file), 'CLEANUP');
				}
			}
		}
	}
}

// Function to create database connection
if (!function_exists('createConnection')) {
	function createConnection($config)
	{
		try {
			$conn = new mysqli(
				$config['host'],
				$config['username'],
				$config['password'],
				$config['database']
			);

			if ($conn->connect_error) {
				throw new Exception("Connection failed: " . $conn->connect_error);
			}

			// Set larger packet size for batch operations
			$conn->query("SET GLOBAL max_allowed_packet=67108864");

			logMessage("Successfully connected to database: {$config['database']}");
			return $conn;
		} catch (Exception $e) {
			logMessage($e->getMessage(), 'ERROR');
			die("Error: " . $e->getMessage() . "\n");
		}
	}
}

// Function to check if email has a numeric timestamp suffix
if (!function_exists('hasTimestamp')) {
	function hasTimestamp($email): bool
	{
		$parts = explode('.', $email);
		$lastPart = end($parts);
		return is_numeric($lastPart) && strlen($lastPart) > 5; // Assuming timestamp is a long number
	}
}

// Function to get base email (remove timestamp if exists)
if (!function_exists('getBaseEmail')) {
	function getBaseEmail($email)
	{
		if (hasTimestamp($email)) {
			$lastDotPos = strrpos($email, '.');
			return substr($email, 0, $lastDotPos);
		}
		return $email;
	}
}

try {
	// Start script execution
	logMessage("Script execution started");

	cleanupOldLogs(7);

	// Connect to both databases
	$db1 = createConnection($db1_config);
	$db2 = createConnection($db2_config);

	// Enable autocommit for better batch handling
	$db1->autocommit(FALSE);

	// Counter for statistics
	$stats = [
		'processed' => 0,
		'matches' => 0,
		'updates' => 0,
		'errors' => 0,
		'batches' => 0
	];

	// Process records in batches
	$offset = 0;
	$hasMoreRecords = true;

	while ($hasMoreRecords) {
		// Get batch of records from db2
		$query2 = "SELECT alternative_email, user_username FROM remote_user where is_updated = ? LIMIT ? OFFSET ?";
		$stmt2 = $db2->prepare($query2);
		$isUpdated = 0;
		$BATCH_SIZE = BATCH_SIZE;
		$stmt2->bind_param('iii', $isUpdated, $BATCH_SIZE, $offset);
		$stmt2->execute();
		$result2 = $stmt2->get_result();

		if ($result2->num_rows == 0) {
			$hasMoreRecords = false;
			break;
		}

		$stats['batches']++;
		logMessage("Processing batch {$stats['batches']}, offset: $offset");

		// Create a map of base emails to search in db1
		$emails_to_check = [];
		while ($row2 = $result2->fetch_assoc()) {
			$normalizedEmail = strtolower(trim($row2['alternative_email']));
			$emails_to_check[$normalizedEmail] = $row2['user_username'];
		}

		if (empty($emails_to_check)) {
			break;
		}

		// Get matching records from db1 in one query
		$placeholders = str_repeat('?,', count($emails_to_check) - 1) . '?';
		$query1 = "SELECT * FROM mdl_user WHERE LOWER(TRIM(email)) IN ($placeholders)";

		$stmt1 = $db1->prepare($query1);
		if ($stmt1) {
			$types = str_repeat('s', count($emails_to_check));
			$stmt1->bind_param($types, ...array_keys($emails_to_check));
			$stmt1->execute();
			$result1 = $stmt1->get_result();

			// Prepare batch update
			$updates = [];
			$updates1 = [];
			while ($row1 = $result1->fetch_assoc()) {
				$stats['matches']++;
				$email1 = strtolower(trim($row1['email']));

				if (isset($emails_to_check[$email1])) {
					$new_email = $emails_to_check[$email1];

					$whereClause = $db1->real_escape_string($row1['email']);
					$setClause = $db1->real_escape_string($new_email);
					$whereClause2 = $db2->real_escape_string($new_email);
					$updates[] = "UPDATE mdl_user SET 
                                email = '{$setClause}'
                                WHERE email = '{$whereClause}' ";

					$updates1[] = "UPDATE remote_user SET 
								is_updated = '1'
								WHERE user_username = '{$whereClause2}' ";
				}
			}

			//$stringUpdate = implode("\n", $updates);
			//logMessage($stringUpdate, 'INFO_DEBUG');
			//exit;

			// Execute batch update
			if (!empty($updates)) {
				$db1->begin_transaction();

				foreach ($updates as $key => $update) {
					if ($db1->query($update)) {
						$stats['updates']++;
						$db2->query($updates1[$key]);
					} else {
						$stats['errors']++;
						logMessage("Update failed: " . $db1->error, 'ERROR');
					}
				}

				$db1->commit();
			}

			$stmt1->close();
		}

		$offset += BATCH_SIZE;
		$stats['processed'] += $result2->num_rows;

		// Log batch progress
		logMessage("Batch {$stats['batches']} completed. " .
			"Processed: {$stats['processed']}, " .
			"Matches: {$stats['matches']}, " .
			"Updates: {$stats['updates']}, " .
			"Errors: {$stats['errors']}");
	}

	// Log final statistics
	logMessage("Processing completed. Final statistics:");
	logMessage("Total batches processed: {$stats['batches']}");
	logMessage("Total records processed: {$stats['processed']}");
	logMessage("Total matches found: {$stats['matches']}");
	logMessage("Successful updates: {$stats['updates']}");
	logMessage("Errors encountered: {$stats['errors']}");

} catch (Exception $e) {
	if (isset($db1)) {
		$db1->rollback();
	}
	logMessage($e->getMessage(), 'ERROR');
	echo "Error: " . $e->getMessage() . "\n";
} finally {
	// Close connections if they exist
	if (isset($db1) && $db1) {
		$db1->close();
		logMessage("DB1 connection closed");
	}
	if (isset($db2) && $db2) {
		$db2->close();
		logMessage("DB2 connection closed");
	}
	logMessage("Script execution completed");
	logMessage("\n");
}
