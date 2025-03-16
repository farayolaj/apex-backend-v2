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
		// Get batch of records from db1
		$query1 = "SELECT * FROM `mdl_user` WHERE lastname REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}$' LIMIT ? OFFSET ?";
		$stmt1 = $db1->prepare($query1);
		$isUpdated = 0;
		$BATCH_SIZE = BATCH_SIZE;
		$stmt1->bind_param('ii', $BATCH_SIZE, $offset);
		$stmt1->execute();
		$result2 = $stmt1->get_result();

		if ($result2->num_rows == 0) {
			$hasMoreRecords = false;
			break;
		}

		$stats['batches']++;
		logMessage("Processing batch {$stats['batches']}, offset: $offset");


		// Prepare batch update
			$updates = [];
			while ($row1 = $result2->fetch_assoc()) {
				$stats['matches']++;
				$email = $row1['email'];
				$lastname = $row1['lastname'];
				$id = $row1['id'];

					$updates[] = "UPDATE mdl_user SET 
                                email = '{$lastname}', lastname = '{$email}'
                                WHERE id = '{$id}' ";
			}

			// $stringUpdate = implode("\n", $updates);
			// logMessage($stringUpdate, 'INFO_DEBUG');
			// exit;

			// Execute batch update
			if (!empty($updates)) {
				$db1->begin_transaction();

				foreach ($updates as $update) {
					if ($db1->query($update)) {
						$stats['updates']++;
					} else {
						$stats['errors']++;
						logMessage("Update failed: " . $db1->error, 'ERROR');
					}
				}

				$db1->commit();
			}

			$stmt1->close();

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

	logMessage("Script execution completed");
	logMessage("\n");
}
