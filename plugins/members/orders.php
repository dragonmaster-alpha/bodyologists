<?php

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

switch ($_REQUEST['op']) {
    default:

        $meta['title'] = _MEMBERS_ORDERS_OVERVEW;
        $meta['name'] = _MEMBERS_ORDERS_OVERVEW;

        if (isset($_REQUEST['id'])) {
            $id = (int) $_REQUEST['id'];
            $item = $orders->get_members_order($id, $_SESSION['user_info']['id']);

            ob_start();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/orders/layout.order.overview.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        } else {
            $items_info = $orders->list_items('', '', '', $_SESSION['user_info']['id']);
        
            ob_start();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/orders/layout.orders.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        }

    break;

    case 'remake':

    break;
}
