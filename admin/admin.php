<?php

/**
 * @author
 * Web Design Enterprise
 * Phone: 786.234.6361
 * Website: www.webdesignenterprise.com
 * E-mail: info@webdesignenterprise.com
 *
 * @copyright
 * This work is licensed under the Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 United States License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/3.0/legalcode
 *
 * Be aware, violating this license agreement could result in the prosecution and punishment of the infractor.
 *
 * � 2002-date('Y') Web Design Enterprise Corp. All rights reserved.
 */

use App\Totals;

defined('DOC_ROOT') || define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT']);
defined('APP_DIR') || define('APP_DIR', DOC_ROOT.'/app');
ini_set('include_path', APP_DIR); //Maximum execution time of each script, in seconds

ini_set('max_execution_time', '1800'); //Maximum execution time of each script, in seconds
ini_set('max_input_time', '1800'); //Maximum amount of time each script may spend parsing request data
ini_set('memory_limit', '256M'); //Maximum amount of memory a script may consume (512MB)
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
define('_TOTAL_TO_LOAD', 20);

require_once(DOC_ROOT.'/mainfile.php');

# Create routes
App\Router::admin_route();

# Crate CSRF token
require_once(APP_DIR.'/security/class.csrf.php');
$csrf = App\Security\Csrf::get_token('token');

if (isset($_REQUEST['logout'])) {
    $administrator->logout();
    $helper->redirect('admin/index.php');
}

if (!$administrator->is_admin()) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $actual_time = date('Y-m-d H:i:s', time() - (_SECURITY_BANNED_TIME * 60 * 60));
    
    # Check times banned
    if ($helper->sql_count('banned', "ip = '".$ip."' AND date > '".$actual_time."'") > 0) {
        $helper->redirect('admin/secure.php');
    } else {
        $helper->sql_delete('banned', "date <= '".$actual_time."'");
    }
    
        $data = $helper->filter($_POST, 1, 1);

    if ($administrator->sign_in($data['username'], $data['password'])) {
        unset($_SESSION['wrong_attempts']);
        new App\Log('[user: '.$data['username'].'] Administrator successfully logged in');
        
        unset($_SESSION['admin_alerts']);

        if (!empty($_COOKIE['admin_last_page'])) {
            $administrator->redirect($helper->filter($_COOKIE['admin_last_page'], 1, 1));
        }
    } else {
        new App\Log('[user: '.$data['username'].'] Failed password for '.$data['username'].' wrong used password: '.$data['password']);

        $_SESSION['wrong_attempts']++;
        
        if ($_SESSION['wrong_attempts'] > _SECURITY_FAILED_ATTEMPTS) {
            $helper->sql_insert('banned', ['ip' => $ip, 'date' => date('Y-m-d H:i:s'), 'username' => $data['username']]);
            $helper->redirect('admin/secure.php');
        } else {
            $helper->redirect('admin/index.php');
        }
    }
}

try {
    $administrator->load_plugins();
    $_SESSION["isLoggedIn"] = true;
    $_SESSION['filesystem.local.rootpath'] = $_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/uploads';
    
    if ($administrator->is_global_admin()) {
        $totals = new Totals();
        $blogs_totals = $totals->get_blog_totals();
        $members_totals = $totals->get_members_totals();
        $orders_totals = $totals->get_orders_totals();
        $sold_total = $totals->get_sold_totals();
        $pages_totals = $totals->get_pages_totals();
        $products_totals = $totals->get_products_totals();
        $properties_totals = $totals->get_properties_totals();
    }
    
    if ($administrator->is_global_admin()) {
        if (isset($_REQUEST['plugin'])) {
            $plugin = $_REQUEST['plugin'];

            $plugin_info = $helper->sql_get_one('plugins', '`active`, `groups`', ['name' => $_REQUEST['plugin']]);
    
            if (!empty($plugin_info['active'])) {
                $open_group = $plugin_info['groups'];

                if (isset($_REQUEST['addon']) && !empty($_REQUEST['addon'])) {
                    $open_plugin = $_REQUEST['addon'];
                    if (file_exists($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/'.$plugin.'/addons/'.$_REQUEST['addon'].'/admin/index.php')) {
                        include_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/'.$plugin.'/addons/'.$_REQUEST['addon'].'/admin/index.php');
                    }
                } elseif (isset($_REQUEST['file']) && !empty($_REQUEST['file'])) {
                    $open_plugin = $_REQUEST['file'];
                    if (file_exists($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/'.$plugin.'/admin/'.$_REQUEST['file'].'.php')) {
                        include_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/'.$plugin.'/admin/'.$_REQUEST['file'].'.php');
                    }
                } else {
                    $open_plugin = strtolower($plugin);
                    if (file_exists($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/'.$plugin.'/admin/index.php')) {
                        include_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/'.$plugin.'/admin/index.php');
                    }
                }
            } else {
                throw new Exception('There was an error processing your request, please go back and try again');
            }
        } else {
            $open_group = 'System';
            $open_plugin = 'dashboard';
            include_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/system/admin/index.php');
        }
    } else {
        if (isset($_REQUEST['plugin'])) {
            $plugin = $_REQUEST['plugin'];
            $plugin_info = $helper->sql_get_one('plugins', 'active, name, groups', "name = '".$plugin."' AND admins LIKE '%".$administrator->admin_aid.",%'");

            if (!empty($plugin_info['active'])) {
                $open_group = $plugin_info['groups'];

                if (isset($_REQUEST['addon']) && !empty($_REQUEST['addon'])) {
                    $open_plugin = $_REQUEST['addon'];

                    if (file_exists($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/'.$plugin.'/addons/'.$_REQUEST['addon'].'/admin/index.php')) {
                        include_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/'.$plugin.'/addons/'.$_REQUEST['addon'].'/admin/index.php');
                    }
                } elseif (isset($_REQUEST['file']) && !empty($_REQUEST['file'])) {
                    $open_plugin = $_REQUEST['file'];

                    if (file_exists($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/'.$plugin.'/admin/'.$_REQUEST['file'].'.php')) {
                        include_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/'.$plugin.'/admin/'.$_REQUEST['file'].'.php');
                    }
                } else {
                    $open_plugin = strtolower($plugin);

                    if (file_exists($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/'.$plugin.'/admin/index.php')) {
                        include_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/'.$plugin.'/admin/index.php');
                    }
                }
            } else {
                $helper->redirect('admin');
            }
        } else {
            $access_plugin = $administrator->get_plugin_admin_access();
            
            //$open_group                         = 'System';
            //$open_plugin                        = 'dashboard';
            include_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/system/admin/index.php');
        }
    }
} catch (Exception $e) {
    die('ERROR: '.$e->getMessage());
}
