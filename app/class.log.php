<?php

namespace App;

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

class Log
{
    private static $_log_file;

    public function __construct($msg)
    {
        self::$_log_file = $_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/logs.log';

        if (!file_exists(self::$_log_file)) {
            self::_create_log();
        }
        
        if (filesize(self::$_log_file) > 20 * 1024 * 1024) {
            $new_file_name = '/logs.'.date('Y-m-d-H-i-s').'.log';
            rename(self::$_log_file, $_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/'.$new_file_name);
            self::_create_log();
        }
        
        $log = "[".date('D M j H:i:s Y')."] [client ".$_SERVER["REMOTE_ADDR"]."] ".$msg."\n";
        error_log($log, 3, self::$_log_file);
    }

    private static function _create_log()
    {
        touch(self::$_log_file);
        chmod(self::$_log_file, 0666);
    }
}
