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

class Flash
{
    private static $_allowed_types = ['error', 'success', 'info', 'warning', 'danger'];

    public static function set($type = 'error', $message = '', $url = '', $refer_to = '')
    {
        if (!in_array($type, self::$_allowed_types)) {
            return false;
        }

        /**
         * redirect to other pages after action it taken
         * Example, redirect to orders after login
         */
        if (!empty($refer_to)) {
            $_SESSION['referer'] = $refer_to;
        }

        if (!empty($message)) {
            $_SESSION[$type]['message'] = $message;

            if (!empty($url)) {
                if ($url == 'back') {
                    $url = str_replace(['http://', 'https://', 'www.', parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST), _SITE_PATH], '', $_SERVER['HTTP_REFERER']);
                    $url = ($url[0] == '/') ? substr($url, 1): $url;
                }

                $url = htmlentities(trim($url), ENT_QUOTES, 'UTF-8');
                require_once(APP_DIR.'/class.router.php');
                header('Location: '._SITE_PATH.'/'.Router::mod_rewrite($url));
                exit;
            }

            return true;
        }
        
        return false;
    }
 
    public static function get($type = 'error')
    {
        if (!empty($_SESSION[$type])) {
            $message = $_SESSION[$type]['message'];
            unset($_SESSION[$type]);
            return $message;
        }
    }
}
