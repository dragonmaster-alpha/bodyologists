<?php

use Plugins\Comments\Classes\Comments;

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
 * � 2002-2009 Web Design Enterprise Corp. All rights reserved.
 */

$plugin_name = 'comments';
if ($administrator->admin_access($plugin_name)) {
    # Class inclusion
    require_once DOC_ROOT.'/plugins/comments/classes/class.comments.php';
    $comments = new Comments();
    define('_TOTAL_TO_LOAD', 20);

    switch ($_REQUEST['op']) {
        default:

            $meta['title'] = 'Comments';
            
            if (isset($_GET['q'])) {
                if (!empty($_GET['q'])) {
                    $query = $frm->filter($_GET['q'], 1, 1);
                    $items_info = $comments->search_reviews($query, 0, _TOTAL_TO_LOAD);

                    if (!empty($items_info)) {
                        ob_start('App\Router::mod_rewrite');
                        foreach ($items_info as $item) {
                            include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/blog/addons/comments/admin/layout/layout.comments.list.phtml');
                        }
                        ob_end_flush();
                    } else {
                        ob_start('App\Router::mod_rewrite');
                        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.empty.phtml');
                        ob_end_flush();
                    }
                } else {
                    $items_info = $comments->list_items(0, _TOTAL_TO_LOAD);

                    ob_start('App\Router::mod_rewrite');
                    foreach ($items_info as $item) {
                        include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/blog/addons/comments/admin/layout/layout.comments.list.phtml');
                    }
                    ob_end_flush();
                }
            } else {
                $items_info = $comments->list_items(0, _TOTAL_TO_LOAD);

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/blog/addons/comments/admin/layout/layout.comments.phtml');
                $layout = ob_get_contents();
                ob_end_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            }

        break;

        case 'spam':

            $meta['title'] = 'Spam';
            
            if (isset($_GET['q'])) {
                if (!empty($_GET['q'])) {
                    $query = $frm->filter($_GET['q'], 1, 1);
                    $items_info = $comments->search_reviews($query, 0, _TOTAL_TO_LOAD, 1);

                    if (!empty($items_info)) {
                        ob_start('App\Router::mod_rewrite');
                        foreach ($items_info as $item) {
                            include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/blog/addons/comments/admin/layout/layout.comments.list.phtml');
                        }
                        ob_end_flush();
                    } else {
                        ob_start('App\Router::mod_rewrite');
                        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.empty.phtml');
                        ob_end_flush();
                    }
                } else {
                    $items_info = $comments->list_items(0, _TOTAL_TO_LOAD, 1);

                    ob_start('App\Router::mod_rewrite');
                    foreach ($items_info as $item) {
                        include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/blog/addons/comments/admin/layout/layout.comments.list.phtml');
                    }
                    ob_end_flush();
                }
            } else {
                $items_info = $comments->list_items(0, _TOTAL_TO_LOAD, 1);

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/blog/addons/comments/admin/layout/layout.comments.phtml');
                $layout = ob_get_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            }

        break;

        case 'more':

            $start = (int) $_GET['s'];
            $items_info = $comments->list_items($start, _TOTAL_TO_LOAD, (int) $_GET['spam']);

            if (!empty($items_info)) {
                ob_start('App\Router::mod_rewrite');
                foreach ($items_info as $item) {
                    include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/blog/addons/comments/admin/layout/layout.comments.list.phtml');
                }
                ob_end_flush();
            }

        break;

        case "approve":

            if (!empty($_GET['id'])) {
                $id = (int) $_GET['id'];

                $comments->approve($id);
                
                $helper->json_response(['answer' => 'done']);
            }

        break;

        case "delete":

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }

                $id = (int) $_GET['id'];
                $item = $comments->get_items_info($id);
                $comments->delete($id);
                $administrator->record_log("Review deletion", "Deletion request for comment '".$item['id']."' has been successfully executed");

                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;
    }
} else {
    Header("Location: index.php");
    exit();
}
