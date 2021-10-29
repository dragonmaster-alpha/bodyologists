<?php

use App\Flash as Flash;
use App\Helper;
use App\Log as Log;
use App\PageNav as PageNav;
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

if (!defined('PLUGINS_FILE')) {
    echo _NO_ACCESS_DIV_TEXT;
    Header("Refresh: 5; url=index.php");
    exit();
}

$plugin_name = basename(dirname(__FILE__));
$helper->get_plugin_lang($plugin_name);

if (!$members->is_user()) {
    $_SESSION['referer'] = 'members/events';
    $helper->redirect('members');
}

$events = new Plugins\Events\Classes\Events;

$display = 20;
$start = (isset($_GET['start'])) ? (int) $_GET['start'] : 0;

$meta['name'] = 'Events';

switch ($_REQUEST['op']) {
    default:
        if (!empty($_REQUEST['url'])) {
            unset($_REQUEST['op']);
            $item = $events->get_items_from_url($_REQUEST['url']);
            $id = (int) $item['id'];

            if (empty($item)) {
                Flash::set('error', 'The event you are trying to see is not available at this time, please try again later.', 'back');
            }

            $members->update_event_visits($id);
            
            # Meta Information
            $meta['name'] = $item['title'];
            $meta['title'] = $item['title'];
            $meta['description'] = $item['meta_description'];
            $meta['canonical'] = $item['url'];

            if (!empty($item['media'])) {
                $meta['image'] = '/uploads/events/'.$item['media']['bid'].'/'.$item['media']['image'];
            }
            
            $breadcrumbs = new Breadcrumbs([$item['url'] => $item['title']]);

            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/events/layout.event.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        } else {
            $meta['title'] = 'Events';
            $meta['name'] = 'Events';

            $items_info = $events->list_items(0, 20, 1, $_SESSION['user_info']['id']);

            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/events/layout.events.phtml');
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

            if ($data['end_date'] < date("Y-m-d") || $data['start_date'] < date("Y-m-d")) {
                Flash::set('error', 'Dates cannot be set in the past.', 'back');
            }

            if ($data['end_date'] < $data['start_date']) {
                Flash::set('error', 'Start Date cannot be after End Date.', 'back');
            }

            if (($data['end_date'] == $data['start_date']) && ($data['end_time'] < $data['start_time'])) {
                Flash::set('error', 'Start Time cannot be after End Time.', 'back');
            }
            
            $data['start_date'] = date('Y-m-d', strtotime($data['start_date']));
            $data['end_date'] = date('Y-m-d', strtotime($data['end_date']));
            $data['start_time'] = date('H:i:s', strtotime($data['start_time']));
            $data['end_time'] = date('H:i:s', strtotime($data['end_time']));

            $data['price'] = $helper->number($data['price'], 1);

            $data['title'] = ucwords(strtolower($data['title']));
            $data['seller'] = ucwords(strtolower($data['seller']));
            $data['text'] = Helper::ucfirstOnMultiline($data['text']);
            $data['address'] = ucwords(strtolower($data['address']));
            $data['city'] = ucwords(strtolower($data['city']));
            $data['purchase_link'] = strtolower($data['purchase_link']);

            $result = $events->save_event($data);

            try {
                new Log('[Event Added] New event has being added by '.$_SESSION['user_info']['full_name'].' titled '.$data['name']);
                Flash::set('success', 'Your event has been added.', 'members/events');
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'back');
            }
        } else {
            if (!empty($_GET['id'])) {
                $item = $members->get_event($_GET['id'], $_SESSION['user_info']['id']);
            }

            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/events/layout.events.edit.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        }

    break;

    case 'delete':

        try {
            if (empty($_GET['id'])) {
                throw new Exception('You must select an item to proceed with this action.');
            }

            # Check if it is the owner
            $events->delete_event($_GET['id'], $_SESSION['user_info']['id']);
            Flash::set('success', 'Your event has been deleted.', 'back');
        } catch (Exception $e) {
            Flash::set('error', $e->getMessage(), 'back');
        }
        
    break;


}
