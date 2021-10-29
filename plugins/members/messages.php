<?php

use App\Flash as Flash;
use App\PageNav as PageNav;

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

if (!defined('PLUGINS_FILE')) {
    echo _NO_ACCESS_DIV_TEXT;
    Header("Refresh: 5; url=index.php");
    exit();
}

$plugin_name = basename(dirname(__FILE__));
$helper->get_plugin_lang($plugin_name);

if (!$members->is_user()) {
    $_SESSION['referer'] = $helper->format_url('index.php?'.$_SERVER['QUERY_STRING']);
    $helper->redirect('index.php?plugin=members');
}

# Check user payments
$members->is_paid();

$members_messages = new Plugins\Members\Classes\Messages;

$_SESSION['paginator']['display'] = (isset($_GET['display']))     ? (int) $_GET['display']     : 20;
$_SESSION['paginator']['start'] = (isset($_GET['start']))       ? (int) $_GET['start']       : 0;

$meta['name'] = _MESSAGES;

switch ($_REQUEST['op']) {
    default:

        if (!empty($_REQUEST['id'])) {
            $id = (int) $_REQUEST['id'];
            $item = $members_messages->get_items_info($id);

            ob_start('replace_for_mod_rewrite');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/messages/layout.message.phtml');
            ob_end_flush();
        } else {
            $meta['title'] = _MESSAGES;
            
            $items_info = $members_messages->list_mesages($_SESSION['paginator']['start'], $_SESSION['paginator']['display']);
            $count_all = $members_messages->count_mesages();

            if ($count_all > $_SESSION['paginator']['display']) {
                $havePaginator = true;
                $pagenav = new Kernel_Classes_PageNav($count_all, $_SESSION['paginator']['display'], $_SESSION['paginator']['start'], 'start', $helper->cleanQueryString('start'));
                $paginator = $pagenav->renderNav();
            }

            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/messages/layout.messages.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        }

    break;

    case 'sent':

        $meta['title'] = _SENT.' '._MESSAGES;
        
        $items_info = $members_messages->list_sent($_SESSION['paginator']['start'], $_SESSION['paginator']['display']);
        $count_all = $members_messages->count_sent();

        if ($count_all > $_SESSION['paginator']['display']) {
            $havePaginator = true;
            $pagenav = new Kernel_Classes_PageNav($count_all, $_SESSION['paginator']['display'], $_SESSION['paginator']['start'], 'start', $helper->cleanQueryString('start'));
            $paginator = $pagenav->renderNav();
        }

        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/messages/layout.sent.phtml');
        $modcontent = ob_get_clean();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');

    break;

    case 'new':

        ob_start('replace_for_mod_rewrite');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/messages/layout.reply.phtml');
        ob_end_flush();

    break;

    case 'reply':

        if (!empty($_REQUEST['id'])) {
            $id = (int) $_REQUEST['id'];
            $item = $members_messages->get_items_info($id);

            ob_start('replace_for_mod_rewrite');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/messages/layout.reply.phtml');
            ob_end_flush();
        }

    break;

    case 'send':

        try {
            $data = $helper->filter($_POST, 1, 1);
            
            if (empty($data['parent'])) {
                if (empty($data['sent_to'])) {
                    throw new Exception('You must enter the email address of the person this email is send to.', 1);
                }

                if (!$helper->check_email($data['sent_to'])) {
                    throw new Exception('Entered email address is invalid.');
                }

                $user_info = $members->get_user_by_email($data['sent_to']);

                if (empty($user_info['id'])) {
                    throw new Exception('The person you are trying to reach does not exists in our system.', 1);
                }
                
                $data['sent_to'] = (int) $user_info['id'];
                $data['receiver_email'] = $user_info['email'];
                $data['receiver_name'] = $user_info['full_name'];
            } else {
                $user_info = $members->get_items_info((int) $data['sent_to']);
                $data['receiver_email'] = $user_info['email'];
                $data['receiver_name'] = $user_info['full_name'];
            }

            $data['readed'] = 0;
            $data['sent_from'] = $_SESSION['user_info']['id'];
            $data['name'] = $_SESSION['user_info']['full_name'];
            $data['email'] = $_SESSION['user_info']['email'];

            if (empty($data['sent_to'])) {
                throw new Exception('You must select who this message will be send to.', 1);
            }

            if (!$members_messages->send($data)) {
                throw new Exception('There was an error sending your message, please try again.', 1);
            }

            $helper->json_response(['answer' => 'done', 'message' => 'Your message has being sent']);
        } catch (Exception $e) {
            $_array = [
                'answer' => 'error',
                'message' => $e->getMessage()
            ];

            $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
        }

    break;

    case 'delete':

        try {
            if (empty($_REQUEST['id'])) {
                throw new Exception(_MEMBERS_DIRECTORY_MESSAGES_DELETE_ERROR, 1);
            }

            $id = (int) $_REQUEST['id'];
            $members_messages->delete($id);

            $helper->json_response(['answer' => 'done', 'message' => 'Your message has being deleted.', 'action' => 'reload']);
        } catch (Exception $e) {
            $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
        }

    break;
}
