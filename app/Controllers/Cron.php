<?php
/**
 * This is a cron job schedule to run at 11:59 UTC+1 as a backup plan incase no client is available
 * thus, we can still be sure that a timestamp would be picked up just at 11:59
 * The server timezone is GMT-4 meaning we are 5hrs ahead that timezone. i.e whatever the current time
 * we are, count 5hrs backward to get equivalent time. 11:59 UTC+1 => 18:59 GMT -4
 *
 * AS OF TODAY, IT SEEMS THE CRON JOB IS USING OUR OWN CURRENT TIMEZONE TO RUN
 *
 */

namespace App\Controllers;

use CodeIgniter\CLI\CLI;

class Cron extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper('string');
        $this->db = db_connect();
    }

    /**
     * This function is called by cron job once in a day at midnight 00:00
     */
    public function cronJob(string $task)
    {
        CLI::write('cron_running', 'green');

        return $this->performAction($task);
    }

    // this function is important for the cashout page fetching periodically
    private function performAction(string $task)
    {

    }


}

// here is the script to run the cron job
// php index.php cron cronJob <args>

// /usr/local/bin/php /home/desktop/public_html/project/index.php cron cronJob giveaway_threshold
