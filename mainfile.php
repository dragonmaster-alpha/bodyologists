<?php

use App\Log as Log;
use App\Security\Antihack;

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

if (stristr($_SERVER['PHP_SELF'], basename(__FILE__))) {
    Header("Location: /");
    exit();
}

# Version check
if (version_compare('5.5.0', phpversion(), '>')) {
    die(sprintf('Your server is running PHP version %s but this software requires at least 5.5.0 to run properly', phpversion()));
}

# Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests
if (isset($_SERVER['SCRIPT_FILENAME']) && (strpos($_SERVER['SCRIPT_FILENAME'], 'php.cgi') == strlen($_SERVER['SCRIPT_FILENAME']) - 7)) {
    $_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];
}

# Fix for Dreamhost and other PHP as CGI hosts
if (strpos($_SERVER['SCRIPT_NAME'], 'php.cgi') !== false) {
    unset($_SERVER['PATH_INFO']);
}

# Fix empty PHP_SELF
if (empty($_SERVER['PHP_SELF'])) {
    $_SERVER['PHP_SELF'] = preg_replace("/(\?.*)?$/", '', $_SERVER["REQUEST_URI"]);
}

# Secure Session Handler
if (!session_id()) {
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_trans_sid', 0);
    ini_set('session.cookie_httponly', 1);
    session_set_cookie_params(0, '/');
    session_start();
}

# Configuration File Inclusion
require_once(__DIR__.'/config/main.php');
require_once('app/class.format.php');
require_once('app/class.helper.php');

$frm = new App\Format;
$helper = new App\Helper;

# Check files and folders permissions
if (!is_writable($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/uploads')) {
    die('ERROR: /uploads/ folder is not writable. Change mode to 777 to the entire folder and it\'s content');
}
if (!is_writable($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/config')) {
    die('ERROR: /config folder is not writable. Change mode to 777 to the entire folder and it\'s content');
}
if (!is_writable($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/logs.log')) {
    die('ERROR: /logs.log file is not writable.');
}
if (!is_writable($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/sitemap.xml')) {
    die('ERROR: /sitemap.xml file is not writable.');
}
if (!is_writable($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/robots.txt')) {
    die('ERROR: /robots.txt file is not writable.');
}

# $admin and $user are COOKIES
if ((isset($admin) && $admin != $_COOKIE['admin']) or (isset($user) && $user != $_COOKIE['user'])) {
    new Log('[HACK ATTEMPT] Possible XSS hack attempt.');
    header("Location: index.php");
    exit;
}

# Auto-Redirects to HTTPS
//var_dump($_SERVER);
//die("HELO ". $helper->site_domain());
//if (!empty($helper->config['force_https'])) {
//    if ($_SERVER['HTTPS'] != "on") {
//        header("Location: https://www.".$helper->site_domain().$_SERVER['REQUEST_URI']);
//        exit;
//    }
//}

# Check for mobile
if (preg_match('/phone|iphone|itouch|ipod|symbian|android|htc_|htc-|palmos|blackberry|opera mini|mobi|windows ce|nokia|fennec|hiptop|kindle|mot |mot-|webos\/|samsung|sonyericsson|^sie-|nintendo|mobile/', strtolower($_SERVER['HTTP_USER_AGENT']))) {
    $_is_mobile = true;
}

if (!function_exists('array_group_by')) {
    function array_group_by(array $array, $key)
    {
        if (!is_string($key) && !is_int($key) && !is_float($key) && !is_callable($key)) {
            trigger_error('array_group_by(): The key should be a string, an integer, or a callback', E_USER_ERROR);
            return null;
        }
        $func = (!is_string($key) && is_callable($key) ? $key : null);
        $_key = $key;
        $grouped = [];
        foreach ($array as $value) {
            $key = null;
            if (is_callable($func)) {
                $key = call_user_func($func, $value);
            } elseif (is_object($value) && property_exists($value, $_key)) {
                $key = $value->{$_key};
            } elseif (isset($value[$_key])) {
                $key = $value[$_key];
            }
            if ($key === null) {
                continue;
            }
            $grouped[$key][] = $value;
        }
        if (func_num_args() > 2) {
            $args = func_get_args();
            foreach ($grouped as $key => $value) {
                $params = array_merge([ $value ], array_slice($args, 2, func_num_args()));
                $grouped[$key] = call_user_func_array('array_group_by', $params);
            }
        }
        return $grouped;
    }
}

# Website Settings
require_once('app/class.settings.php');
$settings = new App\Settings;
$_seo_settings = $settings->get('SEO');

# Admin class declaration
require_once 'app/class.admin.php';
$administrator = new App\Admin();

# Include Pages class by default
require_once 'plugins/pages/classes/class.pages.php';
$pages = new Plugins\Pages\Classes\Pages;

# Include Members class by default
if (file_exists($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/classes/class.members.php')) {
    require_once 'plugins/members/classes/class.members.php';
    $members = new Plugins\Members\Classes\Members;
    $members_settings = $settings->get('members');
}
# Include Products class by default
if (file_exists($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/products/classes/class.products.php')) {
    $products = new Plugins\Products\Classes\Products;
    $products_settings = $settings->get('products');
}

# Include Orders class by default
if (file_exists($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/orders/classes/class.orders.php')) {
    $cart = new Plugins\Orders\Classes\Cart($products);
    $cart_settings = $settings->get('orders');

    $orders = new Plugins\Orders\Classes\Orders;
    $orders_settings = $settings->get('orders');
}

# Hack security check
require_once 'app/security/class.antihack.php';
$anti = new App\Security\Antihack;

//if ($anti->detect_hack(null, 'SPIDER') == true || $anti->detect_hack($anti->curPageURL()) == true) {
//    new Log('[HACK ATTEMPT] Possible XSS hack attempt.');
//    header("Location: "._SITE_PATH."/");
//    exit;
//}
//
//if ($anti->detect_hack(null, 'POST') == true || $anti->detect_hack(null, 'COOKIE') == true || $anti->detect_hack(null, 'QUERY') == true) {
//    new Log('[HACK ATTEMPT] Possible XSS hack attempt.');
//    header("Location: "._SITE_PATH."/");
//    exit;
//}
//
//if ($anti->detect_hack($_SESSION) == true) {
//    new Log('[HACK ATTEMPT] Possible XSS hack attempt.');
//    header("Location: "._SITE_PATH."/");
//    exit;
//}

require $_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/app/vendors/autoload.php';

spl_autoload_register(function ($class){
    ini_set('include_path', 'app');
    $class_path = explode('/', str_replace('\\', '/', $class));
    $class_name = array_pop($class_path);

    $requested = $_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/'.strtolower(implode('/', $class_path)).'/class.'.strtolower($class_name).'.php';
    if (file_exists($requested)) {
        require_once($requested);
    } elseif (_HALT_ON_ERROR) {
        echo '<pre>';
        echo 'Class: ' . $class . PHP_EOL;
        echo 'Class Name: ' . $class_name . PHP_EOL;
        echo 'Requested: ' . $requested . PHP_EOL;
        echo 'Exists?: ' . (file_exists($class) ? 'Yes' : 'No') . PHP_EOL;
        echo PHP_EOL;
        echo 'Backtrace: ' . PHP_EOL;
        debug_print_backtrace(null, 10);
        echo '</pre>';
        die;
    }
}, true);

if (!function_exists('mime_content_type')) {
    function mime_content_type($filename)
    {
        $mime_types = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/config/mime.json'), 1);
        $ext = strtolower(array_pop(explode('.', $filename)));

        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);

            return $mimetype;
        }
         
        return 'application/octet-stream';
    }
}
