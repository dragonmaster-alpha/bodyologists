<?php

use App\Flash as Flash;

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

if (strstr($_SERVER['PHP_SELF'], "layout.php")) {
    Header("Location: index.php");
    exit();
}

require_once('../mainfile.php');

global $administrator, $meta;

try {
    if ($administrator->is_admin()) {
        $admin_menu = $administrator->load_admin_menu($open_group, $open_plugin);
        
        $flash_error = Flash::get('error');
        $flash_success = Flash::get('success');

        ob_start('ob_gzhandler');
        include('layout/layout.index.phtml');
        $page_content = ob_get_clean();
        exit(App\Router::mod_rewrite($page_content));
    }
} catch (Exception $e) {
    die('There was an error on your authentication, please try again');
}
