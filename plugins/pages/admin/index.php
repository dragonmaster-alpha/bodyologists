<?php

use App\Flash as Flash;
use App\MultiArray as MultiArray;
use App\XMLGenerator as XMLGenerator;

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

if ($administrator->admin_access($plugin_name)) {
    switch ($_REQUEST['op']) {
        default:
        
            $meta['title'] = 'Pages Summary';

            if (isset($_GET['q'])) {
                if (!empty($_GET['q'])) {
                    $query = $helper->filter($_GET['q'], 1, 1);
                    $items_info = $pages->search($query, 0, _TOTAL_TO_LOAD);

                    if (!empty($items_info)) {
                        ob_start('App\Router::mod_rewrite');
                        foreach ($items_info as $item) {
                            include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/pages/admin/layout/layout.pages.list.phtml');
                        }
                        ob_end_flush();
                    } else {
                        ob_start('App\Router::mod_rewrite');
                        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.empty.phtml');
                        ob_end_flush();
                    }
                } else {
                    $items_info = $pages->list_items(0, _TOTAL_TO_LOAD);

                    ob_start('App\Router::mod_rewrite');
                    foreach ($items_info as $item) {
                        include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/pages/admin/layout/layout.pages.list.phtml');
                    }
                    ob_end_flush();
                }
            } else {
                $items_info = $pages->list_items(0, _TOTAL_TO_LOAD);
                $languages_count = count($helper->get_languages());

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/pages/admin/layout/layout.pages.phtml');
                $layout = ob_get_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            }
            
        break;

        case 'more':

            $start = (int) $_GET['s'];
            $items_info = $pages->list_items($start, _TOTAL_TO_LOAD);

            if (!empty($items_info)) {
                ob_start('App\Router::mod_rewrite');
                foreach ($items_info as $item) {
                    include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/pages/admin/layout/layout.pages.list.phtml');
                }
                ob_end_flush();
            }

        break;
        
        case "manage_activation":
            
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $run = $pages->manage_activation((int) $_GET['id']);
                $administrator->record_log("Page activation/deactivation", "Activation/Deactivation request for '".$run."' has been executed");
                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }
            
        break;
        
        case "manage_members_only":
            
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $run = $pages->manage_members_only((int) $_GET['id']);
                $administrator->record_log("Page members_only", "Permissions change request for '".$run."' has been executed");
                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }
            
        break;
        
        case "manage_in_menu":
            
           try {
               if (empty($_GET['id'])) {
                   throw new Exception('You must select an item to proceed with this action.');
               }
                
               $run = $pages->manage_in_menu((int) $_GET['id']);
               $administrator->record_log("Page in menu", "In menu change request for '".$run."' has been executed");
               $helper->json_response(['answer' => 'done']);
           } catch (Exception $e) {
               $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
           }
            
        break;
        
        case "edit":
            
            try {
                if (isset($_GET['id'])) {
                    $item = $pages->get_items_info((int) $_GET['id']);
                }
                
                $meta['title'] = 'Page Management';

                $items_array = new MultiArray($pages->get_menu());
                $items_list = $items_array->render();

                $available_languages = $helper->get_languages();
                
                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/pages/admin/layout/layout.pages.edit.phtml');
                $layout = ob_get_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'admin/admin.php?plugin=pages');
            }
            
        break;
        
        case "save":
            
            try {
                if (empty($_POST['name'])) {
                    throw new Exception('Page must have a name');
                }
                if (empty($_POST['url'])) {
                    throw new Exception('Page must have a URL');
                }
                
                $data = $frm->filter($_POST, 'nohtml', 1);
                $data['text'] = $frm->filter($_POST['text'], '', 1);
                
                if ($data['page_type'] == 1) {
                    $data['url'] = $frm->link(str_replace('.html', '', $data['url']));
                    $data['url'] = preg_replace("/[^0-9A-Za-z\'\.;_&#=?\+-]/", '', $data['url']);

                    if (empty($data['url'])) {
                        throw new Exception('Page URL can NOT be empty or include only symbols');
                    }
                    
                    if (!$frm->str_search($data['url'], ['.html', '.htm', '.php', '.asp', '.jsp', '.do'])) {
                        $data['url'] = $data['url'].'.html';
                    }
                }
                
                $result = $pages->save($data);
                $administrator->record_log("Page modified", "Modification request for '".$data['name']."' has been successfully edited");

                # Generate SitemapXML
                new \App\Xml\SitemapXML;
                
                if ($data['save_and'] == 1) {
                    $helper->redirect('admin/admin.php?plugin=pages&op=edit');
                } elseif ($data['save_and'] == 2) {
                    $helper->redirect('admin/admin.php?plugin=pages&op=edit&id='.$result);
                } else {
                    $helper->redirect('admin/admin.php?plugin=pages');
                }
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'admin/admin.php?plugin=pages');
            }
        
        break;
        
        case "delete":
            
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                $id = (int) $_GET['id'];
                $item_info = $pages->get_items_info($id);
                $pages->delete($id);
                
                $administrator->record_log("Page deleted", "Deletion request for '".$item_info['name']."' has been executed");

                # Generate SitemapXML
                new \App\Xml\SitemapXML;
                $helper->json_response(['answer' => 'done', 'message' => 'Action successfully done.']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }
            
        break;
        
        case "reorder":
        
            try {
                $pages->reorder($_GET['item']);
                $helper->json_response(['answer' => 'done', 'message' => 'Action successfully done.']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;
        
        case "check_url":
               
            try {
                if (empty($_GET['url'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $result = $pages->check_url((int) $_GET['id'], $_GET['url']);
                
                if ($result > 0) {
                    echo "<b style='color: red;'>This url is invalid or already in use, please try another</b>";
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }

        break;
    
    }
} else {
    header("Location: index.php");
    exit();
}
