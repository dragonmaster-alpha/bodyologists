<?php

use Plugins\System\Classes\Stats as Stats;

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

global $administrator, $settings, $meta, $totals_class;

switch ($_REQUEST['op']) {
    case 'logout':
        
        $administrator->logout();
        $helper->redirect('admin/index.php');
    
    break;
    
    default:

        $meta['title'] = 'Dashboard';

        if (isset($_POST['date-range'])) {
            $date_range = explode('|', $_POST['date-range']);
            $_SESSION['stats']['start'] = $frm->filter($date_range[1], 1, 1);
            $_SESSION['stats']['end'] = $frm->filter($date_range[0], 1, 1);
            $selected_date = $frm->filter($date_range[2], 1, 1);
        } else {
            if (isset($_POST['start_date'])) {
                $_SESSION['stats']['start'] = $frm->filter($_POST['start_date'], 1, 1);
            } else {
                $_SESSION['stats']['start'] = date('Y-m-d', strtotime('1 week ago'));
            }
            
            if (isset($_POST['end_date'])) {
                $_SESSION['stats']['end'] = $frm->filter($_POST['end_date'], 1, 1);
            } else {
                $_SESSION['stats']['end'] = date('Y-m-d');
            }
        }

        $stats = new Stats($_SESSION['stats']['start'], $_SESSION['stats']['end']);
        $all_visits = $stats->get_total_visits();
        $unique_visits = $stats->get_unique_visits();
        $page_views = $stats->get_page_views();
        $pages_vs_visits = $stats->get_pages_vs_visits();
        
        $visits_per_day = $stats->get_visits_per_day();
        $views_per_day = $stats->get_views_per_day();
        $ticks_per_day = $stats->get_ticks_per_day();

        if ($administrator->admin_access('members')) {
            $count_latest_members = $stats->count_latest_members();
            $get_latest_members = $stats->get_latest_members();
        }

        if (empty($_SESSION['admin_alerts']['comments_adviced'])) {
            $unapproved_blog_comments = $stats->get_recent_comments();
        }

        # Collect RSS
        // $rss                                    = Feed::loadRss('https://www.webdesignerexpress.com/blog/rss/?category=ecommerce');
        // Feed::$cacheDir                         = $_SERVER['DOCUMENT_ROOT'] . _SITE_PATH . '/cache/rss';
        // Feed::$cacheExpire                      = '5 hours';
        
        ob_start('ob_gzhandler');
        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.index.phtml');
        $layout = ob_get_clean();
        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');

    break;
}
