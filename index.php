<?php

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


defined('DOC_ROOT') || define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT']);
defined('APP_DIR') || define('APP_DIR', DOC_ROOT.'/app');

define('PLUGINS_FILE', true);
require_once(DOC_ROOT.'/mainfile.php');
require(APP_DIR.'/class.router.php');

# Create routes
if (!empty($_seo_settings['use_mod_rewrite'])) {
    App\Router::route();
}

# Pages visibility restriction mechanism for the pre-launch stage
if (IS_PRELAUNCH) {
    $whitelist = [
        '/',
        'pages/faqs',
        'pages/terms',
        'pages/privacy',
        'pages/contact',
        'members/getlisted',
    ];
    $plug = $_REQUEST['plugin'] ?: '';
    $page = $_REQUEST['url'] ?: '';
    $uri = $plug . '/' . $page;

    # Restrict access?
    if (!in_array($uri, $whitelist)) {
        header('Location: /#prelaunch');
    }
}

try {
    if (isset($_REQUEST['plugin'])) {
        $_REQUEST['plugin'] = strtolower($_REQUEST['plugin']);
        $base_path = $_SERVER["DOCUMENT_ROOT"]._SITE_PATH;
        $plugin = $base_path.'/plugins/'.$_REQUEST['plugin'];

        if (file_exists($plugin.'/index.php')) {
            if (isset($_REQUEST['addon']) && !empty($_REQUEST['addon']) && ctype_alnum($_REQUEST['addon'])) {
                if (file_exists($addon = $plugin.'/addons/'.$_REQUEST['addon'].'/index.php')) {
                    include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.$_REQUEST['plugin'].'/addons/'.$_REQUEST['addon'].'/index.php');
                }
            } elseif (isset($_REQUEST['file']) && !empty($_REQUEST['file']) && ctype_alnum($_REQUEST['file'])) {
                if (file_exists($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.$_REQUEST['plugin'].'/'.$_REQUEST['file'].'.php')) {
                    include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.$_REQUEST['plugin'].'/'.$_REQUEST['file'].'.php');
                }
            } else {
                include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.$_REQUEST['plugin'].'/index.php');
            }
        } else {
            header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
            header("Status: 404 Not Found");
            $_SERVER['REDIRECT_STATUS'] = 404;
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/error.php');
            exit;
        }
    } else {
        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.$helper->config['index_plugin'].'/index.php');
    }
} catch (Exception $e) {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    header("Status: 404 Not Found");
    $_SERVER['REDIRECT_STATUS'] = 404;
    require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/error.php');
    exit;
}
