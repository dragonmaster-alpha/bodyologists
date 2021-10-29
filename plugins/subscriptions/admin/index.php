<?php

use App\Flash as Flash;
use App\Formatters\CSVWriter;
use Plugins\Subscriptions\Classes\Subscriptions;

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
    $subscriptions = new Plugins\Subscriptions\Classes\Subscriptions;
    define('_TOTAL_TO_LOAD', 20);

    switch ($_REQUEST['op']) {
        default:

            $meta['title'] = 'Subscription Summary';

            if (isset($_GET['q'])) {
                if (!empty($_GET['q'])) {
                    $query = $frm->filter($_GET['q'], 1, 1);
                    $items_info = $subscriptions->search($query, 0, _TOTAL_TO_LOAD);

                    if (!empty($items_info)) {
                        ob_start('App\Router::mod_rewrite');
                        foreach ($items_info as $item) {
                            include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/subscriptions/admin/layout/layout.subscriptions.list.phtml');
                        }
                        ob_end_flush();
                    } else {
                        ob_start('App\Router::mod_rewrite');
                        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.empty.phtml');
                        ob_end_flush();
                    }
                } else {
                    $items_info = $subscriptions->list_items(0, _TOTAL_TO_LOAD);

                    ob_start('App\Router::mod_rewrite');
                    foreach ($items_info as $item) {
                        include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/subscriptions/admin/layout/layout.subscriptions.list.phtml');
                    }
                    ob_end_flush();
                }
            } else {
                $items_info = $subscriptions->list_items(0, _TOTAL_TO_LOAD);

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/subscriptions/admin/layout/layout.subscriptions.phtml');
                $layout = ob_get_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            }

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/subscriptions/admin/layout/layout.subscriptions.phtml');
            $layout = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');

        break;

        case 'more':

            $start = (int) $_GET['s'];
            $items_info = $subscriptions->list_items($start, _TOTAL_TO_LOAD);

            if (!empty($items_info)) {
                ob_start('App\Router::mod_rewrite');
                foreach ($items_info as $item) {
                    include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/subscriptions/admin/layout/layout.subscriptions.list.phtml');
                }
                ob_end_flush();
            }

        break;

        case "manage_activation":
            
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $run = $subscriptions->manage_activation((int) $_GET['id']);
                $administrator->record_log("Subscription activation/deactivation", "Activation/Deactivation request for '".$run."' has been executed");
                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;

        case "edit":

            $meta['title'] = 'Subscription Management';
            
            if (isset($_GET['id'])) {
                $id = (int) $_GET['id'];
                $item = $subscriptions->get_items_info($id);
            }

            $available_languages = $helper->get_languages();
            $categories = $subscriptions->get_categories();

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/subscriptions/admin/layout/layout.subscriptions.edit.phtml');
            $layout = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');

        break;

        case "save":

            try {
                $data = $frm->filter($_POST, 1, 1);
        
                $result = $subscriptions->save($data);

                $administrator->record_log("Subscription modified", "Modification request for subscription '".$data['name']."' has been successfully executed");
                
                if ($data['save_and'] == 1) {
                    $helper->redirect('admin/admin.php?plugin=subscriptions&op=edit');
                } elseif ($data['save_and'] == 2) {
                    $helper->redirect();
                } else {
                    $helper->redirect('admin/admin.php?plugin=subscriptions');
                }
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'admin/admin.php?plugin=subscriptions&op=edit&id='.$result);
            }

        break;

        case "delete":

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }

                $id = (int) $_GET['id'];
                $item = $subscriptions->get_items_info($id);

                $subscriptions->delete($id);
                $administrator->record_log("Subscription deletion", "Deletion request for subscription from: '".$item['name']."' has been successfully executed");
                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;

        case "export":

            try {
                $today = 'subscriptions-'.date('m-d-y');
                $csv = $subscriptions->export();

                # Record log
                $administrator->record_log("Subscriptions exported", "subscriptions database export request has been executed");

                $export = new App\Formatters\CSVWriter($csv);
                $export->headers();
                $export->output();
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'admin/admin.php?plugin=subscriptions');
            }

        break;
    }
} else {
    Header("Location: index.php");
    exit(0);
}
