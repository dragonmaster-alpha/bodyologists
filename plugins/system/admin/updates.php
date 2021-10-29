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

$plugin_name = basename(str_replace('admin', '', dirname(__FILE__)));

# Class inclusion
$updates_class = new Plugins_System_Classes_Updates;

if ($administrator->admin_access($plugin_name)) {
    global $administrator, $frm, $helper, $meta, $settings;
    
    switch ($_REQUEST['op']) {
        default:
        
            $meta['title'] = 'Updates';
            
            $items_info = $updates_class->list_items();
            
            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.updates.phtml');
            $layout = ob_get_contents();
            ob_end_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            
        break;
    }
} else {
    header("Location: index.php");
    exit();
}
