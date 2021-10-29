<?php

use Plugins\Comments\Classes\Comments;

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

$plugin_name = 'comments';
if ($administrator->admin_access($plugin_name)) {
    # Class inclusion
    $comments = new Plugins\Comments\Classes\Comments;
    define('_TOTAL_TO_LOAD', 10);

    switch ($_REQUEST['op']) {
        default:

            $meta['title'] = 'Comments Management';
            $_SESSION['total_to_load'] = 0;
            
            if (isset($_GET['q'])) {
                if (!empty($_GET['q'])) {
                    $query = $frm->filter($_GET['q'], 1, 1);
                    $items_info = $comments->search($query, 0, _TOTAL_TO_LOAD);

                    if (!empty($items_info)) {
                        ob_start('App\Router::mod_rewrite');
                        foreach ($items_info as $item) {
                            include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/comments/admin/layout/layout.comments.list.phtml');
                        }
                        ob_end_flush();
                    } else {
                        ob_start('App\Router::mod_rewrite');
                        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.empty.phtml');
                        ob_end_flush();
                    }
                } else {
                    $items_info = $comments->list_items($_SESSION['total_to_load'], _TOTAL_TO_LOAD);

                    ob_start('App\Router::mod_rewrite');
                    foreach ($items_info as $item) {
                        include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/comments/admin/layout/layout.comments.list.phtml');
                    }
                    ob_end_flush();
                }
            } else {
                $items_info = $comments->list_items($_SESSION['total_to_load'], _TOTAL_TO_LOAD);

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/comments/admin/layout/layout.comments.phtml');
                $layout = ob_get_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            }

        break;

        case 'more':

            $_SESSION['total_to_load'] = ((int) $_SESSION['total_to_load'] + (int) _TOTAL_TO_LOAD);
            $items_info = $comments->list_items($_SESSION['total_to_load'], _TOTAL_TO_LOAD);

            if (!empty($items_info)) {
                ob_start('App\Router::mod_rewrite');
                foreach ($items_info as $item) {
                    include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/comments/admin/layout/layout.comments.list.phtml');
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

        case "spam_manage":

            if (!empty($_GET['id'])) {
                $id = (int) $_GET['id'];
                $comments->spam($id);
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
                $administrator->record_log("comments deletion", "Deletion request for comment '".$item['id']."' has been successfully executed");

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
