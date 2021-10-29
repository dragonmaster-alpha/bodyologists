<?php

use App\Flash as Flash;
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

require_once("mainfile.php");
global $helper, $jQuery, $code, $jscode, $plugin_name, $meta, $modcontent;

if (!defined('PLUGINS_FILE')) {
    echo _NO_ACCESS_DIV_TEXT;
    Header("Refresh: 5; url=index.php");
}

$helper->get_plugin_lang('subscriptions');
# Class inclusion
$subscriptions = new Plugins\Subscriptions\Classes\Subscriptions;

switch ($_REQUEST['op']) {
    default:

        if (!empty($_POST['name'])) {
            $_SESSION['subscription']['name'] = $helper->filter($_POST['name'], 1, 1);
        }
        if (!empty($_POST['email'])) {
            $_SESSION['subscription']['email'] = $helper->filter($_POST['email'], 1, 1);
        }

        if (!empty($_GET['act'])) {
            $meta['name'] = $meta['title'] = _SUBSCRIPTIONS_DONE;
        } else {
            $meta['name'] = $meta['title'] = _SUBSCRIPTIONS_SYSTEM;
        }

        ob_start('ob_gzhandler');
        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/subscriptions/layout/layout.subscriptions.phtml');
        $modcontent = ob_get_clean();
        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/layout.php');

    break;

    case 'a':

        ob_start('App\Router::mod_rewrite');
        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/subscriptions/layout/layout.ajax.phtml');
        ob_end_flush();

    break;

    case 'save':

        try {
            $data = $helper->filter($_POST, 1, 1);

            foreach ($data as $key => $val) {
                $_SESSION['subscription'][$key] = $val;
            }

            if (empty($data['name'])) {
                throw new Exception(_INCORRECT_NAME_NAME, 1);
            }
            if (empty($data['email'])) {
                throw new Exception(_ERROR_INVALID_EMAIL, 1);
            }
            if (!$helper->check_email($data['email'])) {
                throw new Exception(_INCORRECT_EMAIL_ADDRESS, 1);
            }

            $check_email = $subscriptions->get_info_from_email($data['email']);

            if (!empty($check_email)) {
                throw new Exception(_ERROR_EMAIL_ALREADY_EXISTS, 1);
            }

            $data['category'] = 'Subscribed';
            $data['date'] = date('Y-m-d H:i:s');
            $data['lang'] = $_SESSION['lang'];
            
            $subscriptions->save($data);
            $_SESSION['subscriptions']['done'] = 1;
            
            $helper->redirect('index.php?plugin=subscriptions&act=done');
        } catch (Exception $e) {
            Flash::set('error', $e->getMessage(), 'index.php?plugin=subscriptions');
        }
        
    break;

    case 'ajax':

        try {
            $data = $helper->filter($_POST, 1, 1);

            foreach ($data as $key => $val) {
                $_SESSION['subscription'][$key] = $val;
            }

            if (empty($data['name'])) {
                throw new Exception(_INCORRECT_NAME_NAME, 1);
            }
            if (empty($data['email'])) {
                throw new Exception(_ERROR_INVALID_EMAIL, 1);
            }
            if (!$helper->check_email($data['email'])) {
                throw new Exception(_INCORRECT_EMAIL_ADDRESS, 1);
            }

            $check_email = $subscriptions->get_info_from_email($data['email']);

            if (!empty($check_email)) {
                throw new Exception(_ERROR_EMAIL_ALREADY_EXISTS, 1);
            }

            $data['category'] = 'Subscribed';
            $data['date'] = date('Y-m-d H:i:s');
            $data['lang'] = $_SESSION['lang'];
            
            $subscriptions->save($data);
            $_SESSION['subscriptions']['done'] = 1;

            $helper->json_response(['answer' => 'success', 'message' => _SUBSCRIPTIONS_DONE]);
        } catch (Exception $e) {
            $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
        }
        
    break;

    case 'unsubscribe':

        try {
            if (empty($_GET['h'])) {
                throw new Exception(_SUBSCRIPTIONS_MALL_FORMATTED_INFO, 1);
            }

            $email = urldecode(base64_decode(trim($_GET['h'])));

            if (!$helper->check_email($email)) {
                throw new Exception(_SUBSCRIPTIONS_MALL_FORMATTED_INFO, 1);
            }

            $id = $subscriptions->get_id_from_email($email);

            if (empty((int) $id)) {
                throw new Exception(_SUBSCRIPTIONS_INFO_NOT_FOUND, 1);
            }

            $subscriptions->delete('subscriptions', (int) $id);

            $helper->redirect('index.php?plugin=subscriptions&op=done');
        } catch (Exception $e) {
            Flash::set('error', $e->getMessage(), 'index.php?plugin=subscriptions&op=error');
        }
        
    break;

    case 'done':

        ob_start('ob_gzhandler');
        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/subscriptions/layout/layout.unsubscribed.phtml');
        $modcontent = ob_get_clean();
        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/layout.php');

    break;

    case 'error':

        ob_start('ob_gzhandler');
        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/subscriptions/layout/layout.error.phtml');
        $modcontent = ob_get_clean();
        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/layout.php');

    break;

}
