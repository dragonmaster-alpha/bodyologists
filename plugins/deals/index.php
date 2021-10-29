<?php
use App\Breadcrumbs as Breadcrumbs;
use App\ObjectType;
use App\Stats;
use Plugins\Deals\Classes\Deals;
use Plugins\Members\Classes\Wishlist;

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
}

require_once APP_DIR.'/class.object_type.php';

$helper->get_plugin_lang('deals');
# Class inclusion
require_once('plugins/deals/classes/class.deals.php');
$deals = new Plugins\Deals\Classes\Deals();

$display = (!empty($blog_settings['display'])) ? $blog_settings['display'] : 10;
$start = (isset($_GET['start'])) ? (int) $_GET['start'] : 0;

switch ($_REQUEST['op']) {

    default:

        if (!empty($_REQUEST['url'])) {
            unset($_REQUEST['op']);
            $item = $deals->get_items_from_url($_REQUEST['url']);
            if (!$item) {
                header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
                header("Status: 404 Not Found");
                $_SERVER['REDIRECT_STATUS'] = 404;
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/error.php');
                exit;
            }
            $id = (int) $item['id'];

            // Track page view
            (new Stats())->track(ObjectType::DEAL, $id);

            $item['images'] = $deals->get_images($id); // ????
            $item = $deals->get_items_from_url($_REQUEST['url']);
            $item['media'] = $deals->getMedia($item['id']);

            # Meta Information
            $meta['name'] = $item['title'];
            $meta['title'] = $item['meta_title'];
            $meta['keywords'] = $item['tags'];
            $meta['description'] = $item['meta_description'];
            $meta['canonical'] = $item['url'];

            require_once('app/class.breadcrumbs.php');
            $breadcrumbs = new Breadcrumbs([$item['url'] => $item['title']]);
            
            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/deals/layout/layout.deal.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        } else {
            $_meta['name'] = 'Find Deals';
            $_meta['title'] = 'Find Deals';
            $_meta['canonical'] = $item['url'];

            $items_info = $deals->list_items($start, $display, 1);
            $breadcrumbs = new Breadcrumbs([$url => $meta['name']]);
            
            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/deals/layout/layout.deals.phtml');
            $modcontent = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/layout.php');
        }

    break;

    case 'flagdeal':
        $user_id = (int) $_REQUEST['uid'];
        $item_id = (int) $_REQUEST['iid'];

        if (!authUser($user_id)) {
            header('HTTP/1.1 403 Forbidden');
            echo "We couldn't authenticate you";
            die;
        }

        $deal = new Deals();
        $deal_data = $deal->get_items_info($item_id);
        if (!$deal_data) {
            header('HTTP/1.1 404 Not Found');
            echo "We couldn't found that deal";
            die;
        }

        $flag = [
            'user_id' => $user_id,
            'deal_id' => $item_id,
        ];

        $deals->sql_insert('deals_flags', $flag);

        break;

    case 'removeflag':
        $user_id = (int) $_REQUEST['uid'];
        $item_id = (int) $_REQUEST['iid'];

        if (!authUser($user_id)) {
            header('HTTP/1.1 403 Forbidden');
            echo "We couldn't authenticate you";
            die;
        }

        $deal = new Deals();
        $deal_data = $deal->get_items_info($item_id);
        if (!$deal_data) {
            header('HTTP/1.1 404 Not Found');
            echo "We couldn't found that deal";
            die;
        }

        $flag = [
            'user_id' => $user_id,
            'deal_id' => $item_id,
        ];

        $deals->sql_delete('deals_flags', $flag);

        break;

}


