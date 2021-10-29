<?php

use Plugins\System\Classes\Messages as Messages;

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

global $administrator, $frm, $helper, $meta, $settings;

# Class inclusion
$messages_class = new Messages($administrator->admin_info['email']);

if ($administrator->admin_access($plugin_name)) {
    switch ($_REQUEST['op']) {
        default:
        
            $meta['title'] = 'Messages Summary';
            
            $items_info = $messages_class->list_items();
            $available_authors = $messages_class->get_authors();
            
            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.messages.phtml');
            $layout = ob_get_contents();
            ob_end_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            
        break;
        
        case "edit":
        
            try {
                if (!isset($_GET['id'])) {
                    throw new Exception('You must select a message to read', 1);
                }
                $items_info = $messages_class->get_tree((int) $_GET['id']);
                $messages_class->update((int) $_GET['id']);

                $meta['title'] = $items_info[0]['subject'];

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.messages.edit.phtml');
                $layout = ob_get_contents();
                ob_end_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                $helper->redirect("admin/admin.php?plugin=system&file=messages");
            }

        break;

        case "update":
        
            try {
                foreach ($_POST as $key => $value) {
                    $_SESSION['temp_data'][$key] = $value;
                }
                $messages_class->insert($frm->filter($_POST, 1, 1));

                unset($_SESSION['temp_data']);
                $helper->redirect("admin/admin.php?plugin=system&file=messages");
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                $helper->redirect("admin/admin.php?plugin=system&file=messages&op=edit&id=".$_POST['aid']);
            }
        break;
            
        case "delete":
                
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('Please select at least one item to delete');
                }
                
                $messages_class->delete((int) $_GET['id']);
            } catch (Exception $e) {
                $_array = [
                    'answer' => 'error',
                    'message' => $e->getMessage()
                ];
                header('Content-type: application/json');
                echo json_encode($_array);
            }
                
        break;

        case "send_message":

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.messages.send.phtml');
            $layout = ob_get_contents();
            ob_end_clean();
            echo $layout;
            
        break;

        case "email_message":

        try {
            if (empty($_POST['receiver_email'])) {
                throw new Exception('You most enter the email address of the person that will receive the email to proceed.');
            }
            if (empty($_POST['receiver_name'])) {
                throw new Exception('You most enter the name of the person that will receive the email to proceed.');
            }
            if (empty($_POST['subject'])) {
                throw new Exception('You most enter a subject to proceed.');
            }
            if (empty($_POST['message'])) {
                throw new Exception('You most enter a message to proceed.');
            }

            $data = $frm->filter($_POST, 1, 1);

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/layout/emails/layout.messages.email.phtml');
            $body = ob_get_contents();
            ob_end_clean();

            $frm->send_emails($_POST['subject'], $body, '', '', $_POST['receiver_email'], $_POST['receiver_name']);

            $_array = [
                'answer' => 'done',
                'message' => 'Email successfully sent.'
            ];
            header('Content-type: application/json');
            echo json_encode($_array);
        } catch (Exception $e) {
            $_array = [
                'answer' => 'error',
                'message' => $e->getMessage()
            ];
            header('Content-type: application/json');
            echo json_encode($_array);
        }
            
        break;
    }
} else {
    header("Location: index.php");
    exit();
}
