<?php

use App\Flash as Flash;

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

if ($administrator->admin_access($plugin_name)) {
    switch ($_REQUEST['op']) {
        default:
        
            $meta['title'] = 'System Website Settings';
            $selected_country = (!empty($helper->config['country'])) ? $helper->config['country'] : 'US';

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.settings.phtml');
            $layout = ob_get_contents();
            ob_end_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            
        break;

        case "update":
        
            try {
                foreach ($_POST as $key => $value) {
                    if ($key != "op" && $key != "plugin" && $key != "file") {
                        if ($key == "phone") {
                            $_settings_data[$key] = $helper->phone($value);
                        } elseif ($key == "fax") {
                            $_settings_data[$key] = $helper->phone($value);
                        } else {
                            $_settings_data[$key] = $helper->filter($value, 1, 1);
                        }
                    }
                }
            
                if (!isset($_POST['thumb_width'])) {
                    $_settings_data['thumb_width'] = 190;
                }
                if (!isset($_POST['thumb_height'])) {
                    $_settings_data['thumb_height'] = 190;
                }
                if (!isset($_POST['image_width'])) {
                    $_settings_data['image_width'] = 800;
                }
                if (!isset($_POST['image_height'])) {
                    $_settings_data['image_height'] = 800;
                }
                if (!isset($_POST['video_width'])) {
                    $_settings_data['video_width'] = 320;
                }
                if (!isset($_POST['video_height'])) {
                    $_settings_data['video_height'] = 240;
                }

                $settings->set('settings', $_settings_data);

                $administrator->record_log("General Site Settings modified", "General site settings modification request has been executed");
                Flash::set('success', 'General site settings modification request successfully executed.', 'admin/system/settings');
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }

        break;

        case "check_url":

            if (!empty($_GET['table']) && !empty($_GET['url'])) {
                if ($helper->check_new_url($_GET['table'], $_GET['url'])) {
                    $header->json_response(['answer' => 'error', 'message' => 'URL already in use, please choose another...']);
                }
            }

        break;
    }
} else {
    header("Location: index.php");
    exit();
}
