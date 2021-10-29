<?php

use App\Spellcheck;
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
}

$plugin_name = basename(dirname(__FILE__));
$helper->get_plugin_lang($plugin_name);

require_once('plugins/deals/classes/class.deals.php');
require_once('plugins/events/classes/class.events.php');
$deals = new Plugins\Deals\Classes\Deals();
$events = new Plugins\Events\Classes\Events();

$_SESSION['paginator']['display'] = (!empty($_GET['display']))    ? (int) $_GET['display']     : 60;
$_SESSION['paginator']['start'] = (!empty($_GET['start']))      ? (int) $_GET['start']       : 0;

$data = '';
$sort_date = 'date';
$sort_order = 'DESC';

switch ($_REQUEST['op']) {
    default:

        $meta['title'] = 'Find a Professional';

        $data = $helper->filter($_GET, 1, 1);

        if (isset($_REQUEST['honey']) && !empty($_REQUEST['honey'])) {
            break;
        }

        // unset the empty fields from the advanced search
        if (isset($data['q'])) {
            foreach ($data as $key => $value) {
                if ($value == '') {
                    unset($data[$key]);
                }
            }

            if (!empty($_REQUEST['sb'])) {
                if ($_REQUEST['sb'] == 'new') {
                    // NEW MEMBERS
                    $sort_date = 'date';
                    $sort_order = 'DESC';
                } elseif ($_REQUEST['sb'] == 'best') {
                    // POPULAR
                    $sort_date = 'visits';
                    $sort_order = 'DESC';
                } elseif ($_REQUEST['sb'] == 'down') {
                    // PRICES LOW TO HIGH
                    $sort_date = 'fee';
                    $sort_order = 'ASC';
                } elseif ($_REQUEST['sb'] == 'up') {
                    // PRICE HIGH TO LOW
                    $sort_date = 'fee';
                    $sort_order = 'DESC';
                } elseif ($_REQUEST['sb'] == 'az') {
                    // A-Z
                    $sort_date = 'first_name';
                    $sort_order = 'ASC';
                } elseif ($_REQUEST['sb'] == 'za') {
                    // Z-A
                    $sort_date = 'first_name';
                    $sort_order = 'DESC';
                }
            }
        }
        define('_NEWEST', 'New Member');
        define('_POPULAR', 'Popular');

        /** @var \Plugins\Members\Classes\Members $members */
        $items_info = $members->search($data, 0, 10, $sort_date, $sort_order);

        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/search/layout/layout.index.phtml');
        $modcontent = ob_get_clean();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');

    break;

    case 'events':

        $meta['title'] = 'Find Events';
        
        $data = $helper->filter($_GET, 1, 1);

        if (isset($data['q'])) {
            // unset the empty fields from the advanced search
            foreach ($data as $key => $value) {
                if ($value == '') {
                    unset($data[$key]);
                }
            }
        }
        if (!empty($_REQUEST['sb'])) {
            if ($_REQUEST['sb'] == 'new') {
                // RECENTLY ADDED
                $sort_date = 'date';
                $sort_order = 'DESC';
            } elseif ($_REQUEST['sb'] == 'best') {
                // POPULAR
                $sort_date = 'visits';
                $sort_order = 'DESC';
            } elseif ($_REQUEST['sb'] == 'down') {
                // ENDING SOON
                $sort_date = 'end_date';
                $sort_order = 'ASC';
            } elseif ($_REQUEST['sb'] == 'up') {
                // STARTING SOON
                $sort_date = 'start_date';
                $sort_order = 'DESC';
            } elseif ($_REQUEST['sb'] == 'az') {
                // A-Z
                $sort_date = 'title';
                $sort_order = 'ASC';
            } elseif ($_REQUEST['sb'] == 'za') {
                // Z-A
                $sort_date = 'title';
                $sort_order = 'DESC';
            }
        }
        define(_NEWEST, 'Newest Event');
        define(_POPULAR, 'Popular');

        $items_info = $events->search($data, '', '', $sort_date, $sort_order);

        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/search/layout/layout.events.phtml');
        $modcontent = ob_get_clean();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');

    break;

    case 'deals':

        $meta['title'] = 'Find Deals';
        
        $data = $helper->filter($_GET, 1, 1);
        
        if (isset($data['q'])) {
            // unset the empty fields from the advanced search
            foreach ($data as $key => $value) {
                if ($value == '') {
                    unset($data[$key]);
                }
            }
        }

        if (!empty($_REQUEST['sb'])) {
            if ($_REQUEST['sb'] == 'new') {
                // RECENTLY ADDED
                $sort_date = 'date';
                $sort_order = 'DESC';
            } elseif ($_REQUEST['sb'] == 'best') {
                // POPULAR
                $sort_date = 'visits';
                $sort_order = 'DESC';
            } elseif ($_REQUEST['sb'] == 'down') {
                // ENDING SOON
                $sort_date = 'discount';
                $sort_order = 'ASC';
            } elseif ($_REQUEST['sb'] == 'up') {
                // STARTING SOON
                $sort_date = 'discount';
                $sort_order = 'DESC';
            } elseif ($_REQUEST['sb'] == 'az') {
                // A-Z
                $sort_date = 'title';
                $sort_order = 'ASC';
            } elseif ($_REQUEST['sb'] == 'za') {
                // Z-A
                $sort_date = 'title';
                $sort_order = 'DESC';
            } elseif ($_REQUEST['sb'] == 'expiring') {
                // Z-A
                $sort_date = 'end_date';
                $sort_order = 'ASC';
            }
        }
        define(_NEWEST, 'Newest Deals');
        define(_POPULAR, 'Popular');
        $items_info = $deals->search($data, 0, 20, 1, $sort_date, $sort_order);
        
        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/search/layout/layout.deals.phtml');
        $modcontent = ob_get_clean();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');

    break;
}
