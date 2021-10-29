<?php

use App\Flash as Flash;
use Plugins\Members\Addons\Groups\Classes\Groups;
use Plugins\Members\Addons\Packages\Classes\Packages;

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

$plugin_name = 'members';
if ($administrator->admin_access($plugin_name)) {
    # Class inclusion
    $packages = new Plugins\Members\Addons\Packages\Classes\Packages;

    switch ($_REQUEST['op']) {
        default:

            $meta['title'] = 'Plans Summary';
            $items_info = $packages->list_items(0, 100);

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/members/addons/packages/admin/layout/layout.packages.phtml');
            $layout = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');

        break;

        case "manage_activation":
            
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $run = $packages->manage_activation((int) $_GET['id']);

                $administrator->record_log("Plan activation/deactivation", "Activation/Deactivation request for '".$run."' has been executed");
                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;

         case "edit":

            $meta['title'] = 'Plan Management';
            
            if (isset($_GET['id'])) {
                $item = $packages->get_items_info((int) $_GET['id']);
            }
        
            $available_categories = $packages->get_categories();
            
            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/members/addons/packages/admin/layout/layout.packages.edit.phtml');
            $layout = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');

        break;

        case "save":

            try {
                if (empty($_POST['name'])) {
                    throw new Exception('You must enter this plan name to proceed');
                }

                $data = $helper->filter($_POST, 1, 1);
                $result = $packages->save($data);
                $administrator->record_log("Plan modified", "Modification request for plan '".$data['name']."' has been successfully executed");
                
                if ($data['save_and'] == 1) {
                    $helper->redirect('admin/admin.php?plugin=members&addon=packages&op=edit');
                } elseif ($data['save_and'] == 2) {
                    $helper->redirect();
                } else {
                    $helper->redirect('admin/admin.php?plugin=members&addon=packages');
                }
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'back');
            }

        break;

        case "delete":

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }

                $id = (int) $_GET['id'];
                $item = $packages->get_items_info($id);
                $packages->delete($id);
                $administrator->record_log("Plan deletion", "Deletion request for plan '".$item['name']."' has been successfully executed");
                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;

        // Images Handlers
        case "load_images":

            $id = (int) $_GET['id'];
            $image = $members->get_images($id);

            ?>
            <img src="<?=_SITE_PATH?>/uploads/packages/<?=md5((string) $id)?>/small-<?=$image?>" alt="">
            <?php

        break;
    }
} else {
    Header("Location: index.php");
    exit();
}
