<?php

namespace App\Security;

/**
 * @author
 * Web Design Enterprise
 * Website: www.webdesignenterprise.com
 * E-mail: info@webdesignenterprise.com
 *
 * @copyright
 * This work is licensed under the Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 United States License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/3.0/legalcode
 *
 * Be aware, violating this license agreement could result in the prosecution and punishment of the infractor.
 *
 * @copyright 2002- date('Y') Web Design Enterprise Corp. All rights reserved.
 */

class Backups
{
    private $_data;

    public function __construct($host = 'localhost', $dbname = '', $dbuser = '', $dbpass = '', $filename = '')
    {
        try {
            if (!empty($host)) {
                $this->_data['host'] = $host;
            } else {
                throw new Exception("You most provide your database host address to proceed.", 1);
            }

            if (!empty($dbname)) {
                $this->_data['db'] = $dbname;
            } else {
                throw new Exception("You most provide your database name to proceed.", 1);
            }

            if (!empty($dbuser)) {
                $this->_data['user'] = $dbuser;
            } else {
                throw new Exception("You most provide your database user information to proceed.", 1);
            }

            if (!empty($dbpass)) {
                $this->_data['password'] = $dbpass;
            } else {
                throw new Exception("You most provide your database password to proceed.", 1);
            }

            $this->_data['file'] = (!empty($filename) ? $filename : 'db-'.$this->_data['db'].'-'.date('Y-m-d-H-i-s').'.sql');
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function backup()
    {
        shell_exec("mysqldump --routines -h ".$this->_data['host']." -u ".$this->_data['user']." -p'".$this->_data['password']."' ".$this->_data['db']." > ".$this->_data['file']);
    }

    public function restore()
    {
        shell_exec("mysql -h ".$this->_data['host']." -u ".$this->_data['user']." -p'".$this->_data['password']."' ".$this->_data['db']." < ".$this->_data['file']);
    }
}
