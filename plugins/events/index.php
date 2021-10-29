<?php
use App\Breadcrumbs as Breadcrumbs;
use App\ObjectType;
use App\Stats;
use Plugins\Events\Classes\Events;
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

$helper->get_plugin_lang('events');
# Class inclusion
$events = new Events();

$display = 10;
$start = 0;

switch ($_REQUEST['op']) {

    default:

        if (!empty($_REQUEST['url'])) {
            unset($_REQUEST['op']);
            $item = $events->get_items_from_url($_REQUEST['url']);
            $id = (int) $item['id'];

            // Track page view
            (new Stats())->track(ObjectType::EVENT, $id);

            # Meta Information
            $_meta['name'] = $item['title'];
            $_meta['title'] = $item['title'];
            $_meta['keywords'] = $item['tags'];
            $_meta['description'] = $item['text'];
            $_meta['canonical'] = $item['url'];

            $breadcrumbs = new Breadcrumbs([$item['url'] => $item['title']]);
            
            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/events/layout/layout.event.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        } else {
            $_meta['name'] = 'Find Events';
            $_meta['title'] = 'Find Events';
            $_meta['canonical'] = $item['url'];

            $items_info = $events->list_items($start, $display, 1, 1);
            $breadcrumbs = new Breadcrumbs([$url => $meta['name']]);
            
            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/events/layout/layout.events.phtml');
            $modcontent = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/layout.php');
        }

    break;

    case 'flagevent':
        $user_id = (int) $_REQUEST['uid'];
        $item_id = (int) $_REQUEST['iid'];

        if (!authUser($user_id)) {
            header('HTTP/1.1 403 Forbidden');
            echo "We couldn't authenticate you";
            die;
        }

        $event = new Events();
        $event_data = $event->get_items_info($item_id);
        if (!$event_data) {
            header('HTTP/1.1 404 Not Found');
            echo "We couldn't found that event";
            die;
        }

        $flag = [
            'user_id' => $user_id,
            'event_id' => $item_id,
        ];

        $events->sql_insert('events_flags', $flag);

        break;

    case 'removeflag':
        $user_id = (int) $_REQUEST['uid'];
        $item_id = (int) $_REQUEST['iid'];

        if (!authUser($user_id)) {
            header('HTTP/1.1 403 Forbidden');
            echo "We couldn't authenticate you";
            die;
        }

        $event = new Events();
        $event_data = $event->get_items_info($item_id);
        if (!$event_data) {
            header('HTTP/1.1 404 Not Found');
            echo "We couldn't found that event";
            die;
        }

        $flag = [
            'user_id' => $user_id,
            'event_id' => $item_id,
        ];

        $events->sql_delete('events_flags', $flag);

        break;

}

