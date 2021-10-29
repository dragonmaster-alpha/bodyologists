<?php

require_once 'app/class.breadcrumbs.php';
require_once 'app/class.flash.php';
require_once 'app/class.log.php';
require_once 'app/class.pagenav.php';
require_once 'plugins/deals/classes/class.deals.php';

use App\Breadcrumbs;
use App\Flash as Flash;
use App\Helper;
use App\Log as Log;
use App\PageNav as PageNav;
use Plugins\Deals\Classes\Deals;

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
    $_SESSION['referer'] = 'members/deals';
    $helper->redirect('members');
}

require_once 'plugins/deals/classes/class.deals.php';
$deals = new Plugins\Deals\Classes\Deals();

$display = 20;
$start = (isset($_GET['start'])) ? (int) $_GET['start'] : 0;

$meta['name'] = 'Deals';

switch ($_REQUEST['op']) {
    default:

        if (!empty($_REQUEST['url'])) {
            unset($_REQUEST['op']);

            if (empty($item)) {
                Flash::set('error', 'The deal you are trying to see is not available at this time, please try again later.', 'back');
            }

            $id = (int) $item['id'];

            $members->update_deal_visits($id);
            
            # Meta Information
            $meta['name'] = $item['title'];
            $meta['title'] = $item['title'];
            $meta['description'] = $item['meta_description'];
            $meta['canonical'] = $item['url'];

            if (!empty($item['media'])) {
                $meta['image'] = '/uploads/deals/'.$item['media']['bid'].'/'.$item['media']['image'];
            }
            
            $breadcrumbs = new Breadcrumbs([$item['url'] => $item['title']]);

            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/deals/layout.deal.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        } else {
            $meta['title'] = 'Deals';
            $meta['name'] = 'Deals';

            $items = $deals->list_items(0, 20, 1, $_SESSION['user_info']['id']);
            $items_info = [];
            foreach ($items as $item) {
                $item['media'] = $deals->getMedia($item['id']);
                $items_info[] = $item;
            }

            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/deals/layout.deals.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        }

    break;

    case 'add':

        if (!empty($_POST)) {
            if (isset($_REQUEST['honey']) && !empty($_REQUEST['honey'])) {
                break;
            }
            
            $data = $helper->filter($_POST, '1', 1);
            $data['owner'] = (int) $_SESSION['user_info']['id'];
            $data['active'] = 1;

            $data['title'] = ucwords(strtolower($data['title']));
            $data['seller'] = ucwords(strtolower($data['seller']));
            $data['text'] = Helper::ucfirstOnMultiline($data['text']);
            $data['meta']['free_item'] = ucfirst(strtolower($data['meta']['free_item']));
            $data['meta']['free_item_price'] = ucfirst(strtolower($data['meta']['free_item_price']));
            $data['purchase_link'] = strtolower($data['purchase_link']);
            $data['policies'] = Helper::ucfirstOnMultiline($data['policies']);
            $data['city'] = ucwords(strtolower($data['city']));

            $data['reg_price'] = $helper->number($data['reg_price'], 1);
            $data['discount'] = $helper->number($data['discount'], 1);

            if ($data['end_date'] < date("Y-m-d")) {
                Flash::set('error', 'Expiration Date cannot be set in the past', 'back');
            }

            if (
                (isset($data['meta']['bogo_price']) && !empty($data['meta']['bogo_price']))
                &&
                (!isset($data['meta']['bogo_savings']) || empty($data['meta']['bogo_savings']))
            ) {
                Flash::set('error', 'If BOGO price is set, savings field is required.', 'back');
            }

            try {
                $result = $deals->save($data);

                new Log('[Deal Added] New deal has being added by '.$_SESSION['user_info']['full_name'].' titled '.$data['name']);
                Flash::set('success', 'Your deal has been saved.', 'members/deals');
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'back');
            }
        } else {
            if (!empty($_GET['id'])) {
                $item = $deals->get_user_items_info($_GET['id'], $_SESSION['user_info']['id']);
                $item['images'] = $deals->get_images($item['id']);
            }

            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/deals/layout.deals.edit.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        }

    break;

    case 'delete':

        try {
            if (empty($_GET['id'])) {
                throw new Exception('You must select an item to proceed with this action.');
            }

            $deals->delete($_GET['id'], $_SESSION['user_info']['id']);
            Flash::set('success', 'Your deal has been deleted.', 'back');
        } catch (Exception $e) {
            Flash::set('error', $e->getMessage(), 'back');
        }
        
    break;
}
