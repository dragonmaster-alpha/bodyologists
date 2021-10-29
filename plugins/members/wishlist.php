<?php

use App\Flash as Flash;
use App\Helper;
use App\PageNav as PageNav;
use Plugins\Deals\Classes\Deals;
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
    exit();
}

$plugin_name = basename(dirname(__FILE__));
$helper->get_plugin_lang($plugin_name);

$wishlist = new Plugins\Members\Classes\Wishlist;

$display = 20;
$start = (isset($_GET['start'])) ? (int) $_GET['start'] : 0;

$meta['name'] = _WISH_LIST;

switch ($_REQUEST['op']) {
    default:

        if (!$members->is_user()) {
            $_SESSION['referer'] = App\Router::format_url('index.php?'.$_SERVER['QUERY_STRING']);
            $helper->redirect('index.php?plugin=members');
        }

        # Check user payments
        $members->is_paid();

        $meta['title'] = "My Wish List";
        $_in_wishlist = true;
        $items_info = $wishlist->list_items($start, $display);
        $count_all = $wishlist->count();

        if ($count_all > $display) {
            $show_paginator = true;
            $pagenav = new PageNav($count_all, $display, $start, 'start');
            $paginator = $pagenav->renderNav();
        }
        
        ob_start();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/wishlist/layout.wishlist.phtml');
        $modcontent = ob_get_clean();
        include("layout.php");

    break;

    case 'add':

        try {
            if (!$members->is_user()) {
                Flash::set('error', _INCORRECT_MEMBERSHIP_REQUIRED);
                $_SESSION['referer'] = App\Router::format_url('index.php?plugin=members&amp;file=wishlist&amp;op=add&amp;id='.$_GET['id']);
                $helper->redirect('index.php?plugin=members');
            }

            # Check user payments
            $members->is_paid();

            $data = $helper->filter($_GET, 1, 1);
            $data['id'] = (int) $data['id'];
            $data['item_id'] = (int) $data['id'];
            $data['owner'] = (int) $_SESSION['user_info']['id'];
            
            if (empty($data['item_id'])) {
                throw new Exception(_INCORRECT_SUBMITTED_ITEM, 1);
            }

            $check_list_for_duplicates = $wishlist->check_item($data);

            if (empty($check_list_for_duplicates)) {
                unset($data['id']);
                $wishlist->insert($data);
            }

            $helper->redirect('index.php?plugin=members&amp;file=wishlist');
        } catch (Exception $e) {
            Flash::set('error', $e->getMessage(), 'back');
        }

    break;

    case 'adddeal':

        $user_id = (int) $_REQUEST['uid'];
        $item_id = (int) $_REQUEST['iid'];

        handleUnauthorized($user_id);

        $deal_data = collectDealInfo($item_id);

        $wish = [
            'owner' => $user_id,
            'item_id' => $item_id,
            'title' => $deal_data['title'],
            'end_date' => $deal_data['end_date'],
            'belongs' => 'deal',
            'url' => $deal_data['url']
        ];
        (new Wishlist())->insert($wish);

        break;

    case 'addevent':

        $user_id = (int) $_REQUEST['uid'];
        $item_id = (int) $_REQUEST['iid'];

        handleUnauthorized($user_id);

        $event_data = collectEventInfo($item_id);

        $wish = [
            'owner' => $user_id,
            'item_id' => $item_id,
            'title' => $event_data['title'],
            'end_date' => $event_data['end_date'],
            'belongs' => 'event',
            'url' => $event_data['url']
        ];
        (new Wishlist())->insert($wish);

        break;

    case 'addprofessional':

        try {
            if (!$members->is_user()) {
                Flash::set('error', _INCORRECT_MEMBERSHIP_REQUIRED);
                $_SESSION['referer'] = App\Router::format_url('index.php?plugin=members&amp;file=wishlist&amp;op=addprofessional&amp;id='.$_GET['id']);
                $helper->redirect('index.php?plugin=members');
            }

            # Check user payments
            $members->is_paid();

            $data = $helper->filter($_GET, 1, 1);
            $data['id'] = (int) $data['id'];

            $professional_info = $wishlist->get_professional_info($data['id']);
   
            $data['owner'] = (int) $_SESSION['user_info']['id'];
            $data['item_id'] = (int) $professional_info['id'];
            $data['title'] = $frm->filter($professional_info['first_name'].' '.$professional_info['last_name'], 1, 1);
            $data['end_date'] = '';
            $data['belongs'] = 'professional';
            $data['url'] = 'members/'.$professional_info['url'];
            
            if (empty($data['item_id'])) {
                throw new Exception(_INCORRECT_SUBMITTED_ITEM, 1);
            }

            $check_list_for_duplicates = $wishlist->check_item($data);

            if (empty($check_list_for_duplicates)) {
                unset($data['id']);
                $wishlist->insert($data);
            }

            $helper->redirect('index.php?plugin=members&amp;file=wishlist');
        } catch (Exception $e) {
            Flash::set('error', $e->getMessage(), 'back');
        }

    break;

    case 'delete':
        
        $user_id = (int) $_REQUEST['uid'];
        $item_id = (int) $_REQUEST['iid'];
        $item_type = strtolower($_REQUEST['type']);

        if (!in_array($item_type, ['deal','event','professional'])) {
            header('HTTP/1.1 400 Bad request');
            echo "Bad request";
            die;
        }

        handleUnauthorized($user_id);

        (new Wishlist())->delete($item_id, $item_type, $user_id);

        Flash::set('success', "The {$item_type} has been removed from your wishlist.", 'members/wishlist');

        break;

    case 'pub':

        if (!empty($_GET['id'])) {
            $id = (int) $_GET['id'];

            $user_info = $members->get_items_info($id);
            $meta['title'] = _WISHLIST_CHECK_MY_WISHLIST_ON.' '.$helper->config['contactname'].'!';
            $meta['description'] = 'Hey guys, check my wish list on http://'.$helper->site_domain()._SITE_PATH.'/members/wishlist/pub/?id='.$id.', shown me your love buying one of this for me!!';
            $items_info = $wishlist->list_items($start, $display, $id);
            $count_all = $wishlist->count($id);

            if ($count_all > $display) {
                $show_paginator = true;
                $pagenav = new PageNav($count_all, $display, $start, 'start');
                $paginator = $pagenav->renderNav();
            }
        
            ob_start();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/wishlist/layout.wishlist.public.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        }

    break;
}

/**
 * @param int $user_id
 * @return bool
 */
function authUser(int $user_id): bool
{
    return ($user_id !== 0) && ($user_id === (int) $_SESSION['user_info']['id']);
}

/**
 * @param $user_id
 */
function handleUnauthorized($user_id): void
{
    if (!authUser($user_id)) {
        header('HTTP/1.1 403 Forbidden');
        echo "We couldn't authenticate you";
        die;
    }
}

/**
 * @param $deal_id
 * @return array
 * @throws Exception
 */
function collectDealInfo($deal_id): array
{
    $deal = new Deals();
    $deal_data = $deal->get_items_info($deal_id);
    if (!$deal_data) {
        header('HTTP/1.1 404 Not Found');
        echo "We couldn't found that deal";
        die;
    }

    return $deal_data;
}

/**
 * @param $event_id
 * @return array
 */
function collectEventInfo($event_id): array
{
    $event = new Events();
    $event_data = $event->get_items_info($event_id);
    if (!$event_data) {
        header('HTTP/1.1 404 Not Found');
        echo "We couldn't found that event";
        die;
    }

    return $event_data;
}