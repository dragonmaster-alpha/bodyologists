<?php

use Plugins\Events\Classes\Events;

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

$plugin_name = 'events';

# Class inclusion
$events = new Plugins\Events\Classes\Events;

define('_TOTAL_TO_LOAD', 20);

if ($administrator->admin_access($plugin_name)) {
    switch ($_REQUEST['op']) {
        default:

            $meta['title'] = 'Events Summary';

            if (isset($_GET['q'])) {
                if (!empty($_GET['q'])) {
                    $query = $helper->filter($_GET['q'], 1, 1);
                    $items_info = $events->search($query, 0, _TOTAL_TO_LOAD);

                    if (!empty($items_info)) {
                        ob_start('Kernel_Classes_Router::mod_rewrite');
                        foreach ($items_info as $item) {
                            include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/events/admin/layout/layout.events.list.phtml');
                        }
                        ob_end_flush();
                    } else {
                        ob_start('Kernel_Classes_Router::mod_rewrite');
                        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.empty.phtml');
                        ob_end_flush();
                    }
                } else {
                    $items_info = $events->list_items(0, _TOTAL_TO_LOAD);

                    ob_start('Kernel_Classes_Router::mod_rewrite');
                    foreach ($items_info as $item) {
                        include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/events/admin/layout/layout.events.list.phtml');
                    }
                    ob_end_flush();
                }
            } else {
                $items_info = $events->list_items(0, _TOTAL_TO_LOAD);

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/events/admin/layout/layout.events.phtml');
                $layout = ob_get_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            }

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/events/admin/layout/layout.events.phtml');
            $layout = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');

        break;

        case 'more':

            $start = (int) $_GET['s'];
            $items_info = $events->list_items($start, _TOTAL_TO_LOAD);

            if (!empty($items_info)) {
                ob_start('Kernel_Classes_Router::mod_rewrite');
                foreach ($items_info as $item) {
                    include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/events/admin/layout/layout.events.list.phtml');
                }
                ob_end_flush();
            }

        break;

        case "manage_activation":
            
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $run = $events->manage_activation((int) $_GET['id']);
                $administrator->record_log("Event activation/deactivation", "Activation/Deactivation request for '".$run."' has been executed");
                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;

        case "edit":

            $meta['title'] = 'Event Management';
            
            if (isset($_GET['id'])) {
                $id = (int) $_GET['id'];
                $item = $events->get_items_info($id);
            }

            $available_languages = $helper->get_languages();

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/events/admin/layout/layout.events.edit.phtml');
            $layout = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');

        break;

        case "save":

            try {
                $data = $helper->filter($_POST, 1, 1);
                $data['text'] = $helper->filter($_POST['text'], 0, 1);
        
                $result = $events->save($data);

                $administrator->record_log("Event modified", "Modification request for event '".$data['name']."' has been successfully executed");
                
                # Generate SitemapXML
                new \App\Xml\SitemapXML;

                if ($data['save_and'] == 1) {
                    $helper->redirect('admin/admin.php?plugin=events&op=edit');
                } elseif ($data['save_and'] == 2) {
                    $helper->redirect('admin/admin.php?plugin=events&op=edit&id='.$result);
                } else {
                    $helper->redirect('admin/admin.php?plugin=events');
                }
            } catch (Exception $e) {
                Kernel_Classes_Flash::set('error', $e->getMessage(), 'admin/admin.php?plugin=events&op=edit&id='.$result);
            }

        break;

        case "delete":

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }

                $id = (int) $_GET['id'];
                $item = $events->get_items_info($id);

                $events->delete($id);
                $administrator->record_log("event deletion", "Deletion request for event from: '".$item['name']."' has been successfully executed");

                # Generate SitemapXML
                new \App\Xml\SitemapXML;
                
                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;

        # Images Handlers
        case "load_images":

            $id = (int) $_GET['id'];
            $item_images = $events->get_images($id);

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/events/admin/layout/layout.load_images.phtml');
            ob_end_flush();

        break;
    }
} else {
    Header("Location: index.php");
    exit(0);
}
