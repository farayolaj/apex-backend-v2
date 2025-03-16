<?php
/**
 * This is the class that contain the method that will be called whenever any data is inserted for a particular table.
 * the url path should be linked to this page so that the correct operation is performed ultimately. T
 */

namespace App\Models;

use App\Entities\Cashback;
use App\Models\WebSessionManager;
use Exception;

class ModelControllerCallback
{
    private $webSessionManager;

    function __construct()
    {
        helper(['string', 'url', 'array']);
        $this->webSessionManager = new WebSessionManager;
    }

    /**
     * @param mixed $data
     * @param mixed $type
     * @param mixed $db
     * @param mixed $message
     * @return bool
     */
    public function onAdminInserted($data, $type, &$db, &$message): bool
    {
        //remember to remove the file if an error occured here
        //the user type should be admin
        $user = loadClass('user');
        if ($type == 'insert') {
            // login details as follow: username = email, password = firstname(in lowercase)
            $password = encode_password(strtolower($data['firstname']));
            $param = array('user_type' => 'admin', 'username' => $data['email'], 'username_2' => '08109994485', 'password' => $password, 'user_table_id' => $data['LAST_INSERT_ID']);
            $std = new $user($param);
            if ($std->insert($db, $message)) {
                return true;
            }
            return false;
        }
        return true;
    }

}
