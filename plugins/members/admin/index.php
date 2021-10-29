<?php

use App\Flash;
use App\Formatters\CSVWriter;
use Plugins\Comments\Classes\Comments;
use Plugins\Members\Addons\Files\Classes\Files;
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

if (!strstr($_SERVER['PHP_SELF'], "admin.php")) {
    header("Location: index.php");
    exit();
}

$plugin_name = basename(str_replace('admin', '', dirname(__FILE__)));

# Class inclusion
if (class_exists('\Plugins\Members\Addons\Packages\Classes\Packages')) {
    $packages = new \Plugins\Members\Addons\Packages\Classes\Packages;
}
//if (class_exists('\Plugins\Members\Addons\Groups\Classes\Groups')) {
//    $groups = new \Plugins\Members\Addons\Groups\Classes\Groups;
//}
//if (class_exists('\Plugins\Members\Addons\Files\Classes\Files')) {
//    $files = new \Plugins\Members\Addons\Files\Classes\Files;
//}

$comments = new Plugins\Comments\Classes\Comments;

define('_TOTAL_TO_LOAD', 20);

if ($administrator->admin_access($plugin_name)) {
    switch ($_REQUEST['op']) {
        default:
        
            if (count($members_settings) == 0) {
                $helper->redirect('admin/admin.php?plugin=members&op=settings');
            }

            $meta['title'] = 'Clients Summary';

            if (isset($_GET['q'])) {
                if (!empty($_GET['q'])) {
                    $query = $helper->filter($_GET['q'], 1, 1);
                    $items_info = $members->search($query, 0, _TOTAL_TO_LOAD);

                    if (!empty($items_info)) {
                        ob_start('App\Router::mod_rewrite');
                        foreach ($items_info as $item) {
                            include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/members/admin/layout/layout.members.list.phtml');
                        }
                        ob_end_flush();
                    } else {
                        ob_start('App\Router::mod_rewrite');
                        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.empty.phtml');
                        ob_end_flush();
                    }
                } else {
                    $items_info = $members->list_items(0, _TOTAL_TO_LOAD);
                    ob_start('App\Router::mod_rewrite');
                    foreach ($items_info as $item) {
                        include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/members/admin/layout/layout.members.list.phtml');
                    }
                    ob_end_flush();
                }
            } else {
                $items_info = $members->list_items(0, _TOTAL_TO_LOAD);

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/members/admin/layout/layout.members.phtml');
                $layout = ob_get_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            }
            
        break;

        case 'more':

            $start = (int) $_GET['s'];
            $items_info = $members->list_items($start, _TOTAL_TO_LOAD);

            if (!empty($items_info)) {
                ob_start('App\Router::mod_rewrite');
                foreach ($items_info as $item) {
                    include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/members/admin/layout/layout.members.list.phtml');
                }
                ob_end_flush();
            }

        break;

        case "manage_activation":
            
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $run = $members->manage_activation((int) $_GET['id']);

                $administrator->record_log("Client activation/deactivation", "Activation/Deactivation request for '".$run."' has been executed");
                
                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;

        case "manage_featured":

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $run = $members->manage_featured((int) $_GET['id']);

                $administrator->record_log("Client featured/unfeatured", "Featured/Unfeatured request for '".$run."' has been executed");
                
                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }
        break;

        case "edit":
        
            try {
                $meta['title'] = 'Client Management';
        
                if (!empty($_GET['id'])) {
                    $id = (int) $_GET['id'];
                    $item = $members->get_items_info($id);

                    if (is_object($orders)) {
                        $members_orders = $orders->list_items(0, 0, 0, $id);
                    }

                    if (is_object($files)) {
                        $available_files = $files->list_items(0, 0, $id);
                    }

                    if (is_object($comments)) {
                        $available_comments = $comments->get_from_owner($id);
                    }
        
                    $available_addresses = $members->get_addresses($id);
                } else {
                    $item = getDefaultUserValues(['date' => date('Y-m-d H:i:s')]);
                    $item['id'] = $helper->sql_insert('customers', $item);
                }

                if (is_object($groups)) {
                    $available_groups = $groups->list_items(0, 0, 0, 'name', 'ASC');
                }

                if (is_object($packages)) {
                    $available_plans = $packages->list_items(0, 0, 0, 'name', 'ASC');
                }

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/members/admin/layout/layout.members.edit.phtml');
                $layout = ob_get_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            } catch (Exception $e) {
                App\Flash::set('error', $e->getMessage(), 'admin/members');
            }

        break;

        case "save":

            try {
                if (empty($_POST['first_name'])) {
                    throw new Exception('You must enter this client first name to proceed');
                }
                if (empty($_POST['last_name'])) {
                    throw new Exception('You must enter this client last name to proceed');
                }
                if (empty($_POST['email'])) {
                    throw new Exception('You must enter this email address last name to proceed');
                }

                $data = $helper->filter($_POST, 1, 1);
                $data['author'] = $administrator->admin_info['name'];

                $result = (!empty($data['id'])) ? $members->update($data) : $members->insert($data);

                $administrator->record_log("Client modified", "Modification request for '".$data['first_name']." ".$data['last_name']."' has been successfully executed");
                
                if ($data['save_and'] == 1) {
                    $helper->redirect('admin/admin.php?plugin=members&op=edit');
                } elseif ($data['save_and'] == 2) {
                    $helper->redirect('admin/admin.php?plugin=members&op=edit&id='.$result);
                } else {
                    $helper->redirect('admin/admin.php?plugin=members');
                }
            } catch (Exception $e) {
                App\Flash::set('error', $e->getMessage(), 'admin/admin.php?plugin=members&op=edit&id='.$result);
            }

        break;

        case "delete":

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $id = (int) $_GET['id'];
                $item = $members->get_items_info($id);
                $members->delete($id);
                $administrator->remove_dir($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/uploads/members/'.md5((string) $id));
                $administrator->record_log("Client deletion", "Deletion request for '".$item['first_name']." ".$item['last_name']."' has been successfully executed");

                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;

        case "settings":

            $meta['title'] = 'Members Settings';

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/members/admin/layout/layout.members.settings.phtml');
            $layout = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
        break;

        case "save_settings":

            foreach ($_POST as $key => $value) {
                if ($key != "op") {
                    if (is_array($value)) {
                        $_save_array[$key] = '|'.implode('|', $value).'|';
                    } else {
                        $_save_array[$key] = $helper->filter($value, 'nohtml', 1);
                    }
                }
            }

            $settings->set('members', $_save_array);
            $administrator->record_log("Members settings modification", "A new members settings modification request has been executed");
            
            App\Flash::set('success', 'members settings modifications successfully saved.', 'admin/admin.php?plugin=members');

        break;

        # Images Handlers
        case "load_images":

            $id = (int) $_GET['id'];
            $avatar = $members->get_images($id);

            ?>
            <img src="<?=_SITE_PATH?>/uploads/members/<?=md5((string) $id)?>/small-<?=$avatar?>" alt="">
            <?php
        break;

        # Notes
        case "notes":

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an order to proceed', 1);
                }
                $id = (int) $_GET['id'];
                $item = $members->get_items_info((int) $_GET['id']);

                ob_start('App\Router::mod_rewrite');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/members/admin/layout/layout.members.notes.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                App\Flash::set('error', $e->getMessage());
            }

        break;

        case "save_notes":

            try {
                if (empty($_POST['id'])) {
                    throw new Exception('You must select an order to proceed', 1);
                }
                $data = $helper->filter($_POST, 1, 1);
                $data['author'] = $administrator->admin_info['name'];
                
                $members->save_notes($data);
                $administrator->record_log("Client Notes", "Notes for client: '".$data['name']."' has been successfully saved");

                $helper->json_response(['answer' => 'done', 'message' => 'Notes successfully saved.']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;

        # Export Members
        case "export":

            try {
                $today = 'clients-'.date('m-d-y');
                $csv = $members->export();

                # Record log
                $administrator->record_log("Clients exported", "clients database export request has been executed");

                require (APP_DIR.'/formatters/class.csv.writer.php');
                $export = new \App\Formatters\CSVWriter($csv);
                $export->headers();
                $export->output();
            } catch (Exception $e) {
                App\Flash::set('error', $e->getMessage(), 'admin/admin.php?plugin=members');
            }

        break;
    }
} else {
    Header("Location: index.php");
    exit();
}

function getDefaultUserValues(array $custom)
{
    return array_merge([
        'grouped' => 4,
        'professional' => 1,
        'available' => null,
        'address' => '',
        'city' =>  '',
        'state' => '',
        'zipcode' => null,
        'business_phone' => '',
        'main_category' => '',
        'category' => '',
        'gender' => '',
        'birthday' => null,
        'first_name' => '',
        'last_name' => '',
        'url' => '',
        'company' => '',
        'website' => '',
        'email' => '',
        'pwd' => '',
        'phone' => '',
        'fax' => '',
        'bio' => '',
        'extra' => '',
        'insurance' => '',
        'plan' => 0,
        'next_payment' => '1969-12-31',
        'newsletters' => 0,
        'referred' => 0,
        'active' => 1,
        'featured' => 0,
        'date' => '',
        'visits' => 0,
        'modified' => date('Y-m-d H:i:s'),
        'last_login' => '1969-12-31',
        'languages' => '',
        'notes' => '',
        'cc' => '',
        'tt' => '',
        'alive' => 1,
        'approved' => 1,
    ], $custom);
}
