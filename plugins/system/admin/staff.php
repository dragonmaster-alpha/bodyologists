<?php

use App\Flash as Flash;
use Plugins\System\Classes\Staff as Staff;

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
    # Class inclusion
    $staff = new Plugins\System\Classes\Staff;
    
    switch ($_REQUEST['op']) {
        default:
        
            $meta['title'] = 'Staff Summary';
            $items_info = $staff->list_items();
            
            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.staff.phtml');
            $layout = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            
        break;

        case "manage_activation":
            
            try {
                if (empty($_GET['aid'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }

                $id = $frm->filter($_GET['aid'], 1, 1);
                $run = $staff->manage_activation($id);

                $administrator->record_log("Staff activation/deactivation", "Activation/Deactivation request for '".$id."' has been executed");

                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;
        
        case "edit":
        
            try {
                $meta['title'] = 'Staff Management';
                
                if (!empty($_GET['aid'])) {
                    $item = $staff->get_items_info($_GET['aid']);
                }

                $available_plugins = $staff->get_plugins();
                
                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.staff.edit.phtml');
                $layout = ob_get_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'back');
            }

        break;

        case "update":
        
            try {
                foreach ($_POST as $key => $value) {
                    $_SESSION['temp_data'][$key] = $value;
                }
                if (empty($_SESSION['temp_data']['aid'])) {
                    throw new Exception('You must enter a login name.');
                }
                if (empty($_SESSION['temp_data']['name'])) {
                    throw new Exception('You must enter this person name.');
                }
                if (empty($_SESSION['temp_data']['email'])) {
                    throw new Exception('You must enter this person e-mail address.');
                }
                if (empty($_SESSION['temp_data']['radminsuper']) && count($_SESSION['temp_data']['admin_plugins']) == 0) {
                    throw new Exception('You must set the permissions for this person to access this administrator console.');
                }
                
                if ($staff->exists($_SESSION['temp_data']['aid']) == 1) {
                    $staff->update($frm->filter($_POST, 1, 1));
                } else {
                    $staff->insert($frm->filter($_POST, 1, 1));
                }
                
                $administrator->record_log("Staff Management", "Staff Member ".$_POST['aid']." has being successfully edited");
                $helper->redirect("admin/admin.php?plugin=system&file=staff");
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'back');
            }

        break;
            
        case "delete":
                
            try {
                if (empty($_GET['aid'])) {
                    throw new Exception('Please select at least one item to delete');
                }
                
                $staff->delete($frm->filter($_GET['aid'], 1, 1));
                $helper->json_response(['answer' => 'done', 'message' => 'Action successfully done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }
                
        break;

        case "load_image":

            $id = $frm->filter($_GET['aid']);
            $item_images = $staff->get_images($_GET['aid']);
            $item_images['image'] = _SITE_PATH.'/uploads/staff/'.$item_images['bid'].'/small-'.$item_images['image'];
            echo json_encode($item_images, true);

        break;
    }
} else {
    header("Location: index.php");
    exit;
}
