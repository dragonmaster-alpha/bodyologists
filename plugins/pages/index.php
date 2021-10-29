<?php

use App\Breadcrumbs as Breadcrumbs;
use App\PageNav as PageNav;

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

require('app/class.breadcrumbs.php');

$plugin_name = basename(dirname(__FILE__));
$helper->get_plugin_lang($plugin_name);

$_display = 10;
$_start = (!empty($_REQUEST['start'])) ? (int) $_REQUEST['start'] : 0;

switch ($_REQUEST['op']) {
    default:

        if (!empty($_REQUEST['url'])) {
            $url = $_REQUEST['url'];
        }

        $page_content = get_page_content($url);
        $meta['name'] = $page_content['name'];
        $meta['title'] = $page_content['title'];
        $meta['keywords'] = $page_content['meta_keywords'];
        $meta['description'] = $page_content['meta_description'];

        $breadcrumbs = new Breadcrumbs([$url => $meta['name']]);
        
        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/pages/layout/layout.index.phtml');
        $modcontent = ob_get_clean();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');

    break;
    
    case "popup":

        if (!empty($_REQUEST['url'])) {
            $url = $_REQUEST['url'];
        }
        
        $page_content = get_page_content($url);
        $meta['title'] = $page_content['title'];
        $meta['keywords'] = $page_content['keywords'];
        $meta['description'] = $page_content['description'];

        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/pages/layout/layout.index.phtml');
        ob_end_flush();

    break;

    case "category_content":

        $data = $helper->filter($_REQUEST, 1, 1);
        $category = $helper->filter($_REQUEST['category'], 1);
        $items_data = $pages->list_items('', '', 1, $category);
        
        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/pages/layout/layout.content.phtml');
        $modcontent = ob_get_clean();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');

    break;

    case 'search':

        $query = $helper->filter(urldecode($_REQUEST['q']), 1, 1);

        $search_results['pages'] = $pages->search($query, $_start, $_display, 1);
        $countAll = $pages->search_count($query);

        if ($countAll > $_display) {
            $havePagination = true;
            $pagenav = new PageNav($countAll, $_display, $_start, 'start', $helper->cleanQueryString());
            $paginator = $pagenav->renderNav();
        }

        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/pages/layout/layout.search.phtml');
        $modcontent = ob_get_clean();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');

    break;

    case 'save':

        if ($administrator->admin_access($plugin_name)) {
            $pages->save_content($_POST);
            $administrator->record_log("Page modified", "Modification request for '".$helper->filter($_POST['title'], 1)."' has been successfully edited.");
        }
        
    break;

    case "load_states":

        $name = $helper->filter($_REQUEST['name'], 1, 1);
        $country = $helper->filter($_REQUEST['country'], 1, 1);

        if (!empty($_REQUEST['class'])) {
            $class = $helper->filter($_REQUEST['class'], 1, 1);
            $extra = [
                'class' => $class
            ];
        }

        echo $helper->get_states($name, '', $country, $extra);

    break;
}

function get_page_content($url = '')
{
    global $helper, $pages, $members;

    $page_content = [];
    $item_info = (!empty($url)) ? $pages->get_items_from_url($url) : $pages->get_index();
    
    if (!empty($item_info['only_members']) && !$members->is_user()) {
        $_SESSION['referer'] = $item_info['url'];
        $helper->redirect('members');
    }

    $pages->update_visits($item_info['id']);
    $page_content['id'] = $item_info['id'];
    $page_content['name'] = $item_info['name'];
    $page_content['title'] = $item_info['title'];
    $page_content['meta_keywords'] = $item_info['meta_keywords'];
    $page_content['meta_description'] = $item_info['meta_description'];
    $page_content['only_members'] = (!empty($item_info['only_members'])) ? true : false;
    
    # Contact information replacement
    if (isset($_SESSION['contact'])) {
        $jQuery = '';

        foreach ($_SESSION['contact'] as $name => $value) {
            $jQuery .= "$('[name$=\"".$name."\"]').val('".$value."');\n";
        }
    }
    
    $page_content['text'] = $helper->format_addons($item_info['text']);
    $page_content['text'] = str_replace('<div class="all-wide">', '</div><div class="all-wide">', $page_content['text']);

    if (stripos($page_content['text'], '<!-- lastest_blogs -->') !== false) {
        # Insert latest blogs
        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout/menus/menu.blog.latest.php');
        $_menu_blogs = ob_get_clean();
        $page_content['text'] = str_replace('<!-- lastest_blogs -->', $_menu_blogs, $page_content['text']);
    }
    if (stripos($page_content['text'], '<!-- featured_products -->') !== false) {
        # Insert featured products
        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout/menus/menu.products.featured.php');
        $_menu_featured_products = ob_get_clean();
        $page_content['text'] = str_replace('<!-- featured_products -->', $_menu_featured_products, $page_content['text']);
    }
        
    if (isset($_SESSION['error'])) {
        unset($_SESSION['contact']);
    }
    
    return $page_content;
}
