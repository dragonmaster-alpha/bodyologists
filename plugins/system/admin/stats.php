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

# Class inclusion
$stats = new Stats($_SESSION['stats']['start'], $_SESSION['stats']['end']);

if ($administrator->admin_access($plugin_name)) {
    switch ($_REQUEST['op']) {
        default:
    
            $all_visits = $stats->get_total_visits();
            $unique_visits = $stats->get_unique_visits();
            $page_views = $stats->get_page_views();
            $pages_vs_visits = $stats->get_pages_vs_visits();
            
            $visits_per_day = $stats->get_visits_per_day();
            $views_per_day = $stats->get_views_per_day();
            $ticks_per_day = $stats->get_ticks_per_day();

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.stats.phtml');
            $layout = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            
        break;
        
        case "get_countries":
        
            try {
                $table_headers = ['Country', 'Visits', '% Visits'];
                $results = $stats->get_most_country_visits();

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.stats.info.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $_GET['id'].' - '.$e->getMessage()]);
            }
        
        break;
            
        case "get_browsers":
                
            try {
                $table_headers = ['Browser', 'Visits', '% Visits'];
                $results = $stats->get_most_browser_visits();

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.stats.info.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $_GET['id'].' - '.$e->getMessage()]);
            }
                
        break;
        
        case "get_os":
        
            try {
                $table_headers = ['Operating System', 'Visits', '% Visits'];
                $results = $stats->get_non_mobile_visits();

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.stats.info.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $_GET['id'].' - '.$e->getMessage()]);
            }

        break;

        case "get_mobile":
        
            try {
                $table_headers = ['Operating System', 'Visits', '% Visits'];
                $results = $stats->get_mobile_visits();

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.stats.info.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $_GET['id'].' - '.$e->getMessage()]);
            }

        break;

     case "get_sources":
        
            try {
                $table_headers = ['Landing Page', 'Visits', '% Visits'];
                $results = $stats->get_page_visits();

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.stats.info.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $_GET['id'].' - '.$e->getMessage()]);
            }

        break;

        case "get_referrers":
        
            try {
                $table_headers = ['Referrer', 'Referrer URL', 'Visits', '% Visits'];
                $results = $stats->get_referrers_visits();

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.stats.referrer.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $_GET['id'].' - '.$e->getMessage()]);
            }

        break;

        case "get_searchs":
        
            try {
                $table_headers = ['Search Engine', 'Search Term', 'Visits', '% Visits'];
                $results = $stats->get_search_visits();

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.stats.referrer.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $_GET['id'].' - '.$e->getMessage()]);
            }

        break;

        case "get_sn":
        
            try {
                $table_headers = ['Social Network', 'Referrer URL', 'Visits', '% Visits'];
                $results = $stats->get_social_networks_visits();

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.stats.referrer.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $_GET['id'].' - '.$e->getMessage()]);
            }

        break;
    }
} else {
    header("Location: index.php");
    exit();
}
