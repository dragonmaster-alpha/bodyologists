<?php

use App\Flash as Flash;

require_once('app/class.multiarray.php');
require_once('app/class.flash.php');
require_once('app/class.router.php');
require_once('app/class.minify.php');

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

if (stristr($_SERVER['PHP_SELF'], basename(__FILE__))) {
    Header("Location: "._SITE_PATH."/");
    exit();
}

# General Settings
$_meta['title'] = (!empty($meta['title']))          ? $meta['title']                    : $_seo_settings['meta_title'];
$_meta['name'] = (!empty($meta['name']))           ? $meta['name']                     : $_seo_settings['meta_title'];
$_meta['keywords'] = (!empty($meta['keywords']))       ? $meta['keywords']                 : $_seo_settings['meta_keywords'];
$_meta['description'] = (!empty($meta['description']))    ? $meta['description']              : $_seo_settings['meta_description'];
$_meta['js'] = (empty($meta['code']))            ? $meta['code']                     : '';
$_meta['load'] = (empty($meta['load']))            ? 'onload="'.$meta['load'].'"'  : '';
$_meta['canonical'] = (empty($meta['canonical']))       ? $meta['canonical']                : str_replace(_SITE_PATH, '', $_SERVER['REQUEST_URI']);
$_meta['image'] = (!empty($helper->config['force_https']) ? 'https' : 'http').'://'.$_seo_settings['url_syntax'].$helper->site_domain()._SITE_PATH.(!empty($meta['image']) ? $meta['image'] : '/images/logo.png');
$_meta['year'] = date('Y');

# Build Menu
$sorting_pages = $pages->get_sorting_pages();

$items_array = new App\MultiArray($sorting_pages);
$menus = $items_array->render();
        
$flash_error = Flash::get('error');
$flash_success = Flash::get('success');

if (!empty($flash_error)) {
    $code .= "<script type='text/javascript'>$(window).load(function(){alert({title: 'ERROR', html: true, text: '".$flash_error."'});});</script>";
}

if (!empty($flash_success)) {
    $code .= "<script type='text/javascript'>$(window).load(function(){alert({title: 'SUCCESS', html: true, text: '".$flash_success."', confirmButtonColor: '#598f2c'});});</script>";
}

if (empty($meta['canonical'])) {
    if ($_REQUEST['plugin'] == 'pages' || $_REQUEST['plugin'] == 'places') {
        $meta['canonical'] = $_REQUEST['url'];
    } else {
        $meta['canonical'] = $_REQUEST['plugin'].'/'.(!empty($_REQUEST['addon']) ? $_REQUEST['addon'].'/' : '').(!empty($_REQUEST['op']) ? $_REQUEST['op'].'/' : '').(!empty($_REQUEST['url']) ? $_REQUEST['url'].'/' : '');
    }
}

$_website_schema = @file_get_contents($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/config/website.schema.json');
$_company_schema = @file_get_contents($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/config/company.schema.json');

# Actual page info
if ($_REQUEST['plugin'] == 'pages' && !isset($_REQUEST['addon'])) {
    $current_page_info = $pages->get_page_by_url(end(explode('/', $_SERVER["REQUEST_URI"])));
    $category = $current_page_info['category'];
    $top_image = (!empty($current_page_info['topImage'])) ? '<img src="'.$current_page_info['topImage'].'" />' : '';
}

ob_start('ob_gzhandler');
# Start Layout management area
if (App\Router::is_index()) {
    # Developers credits
    require_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout/layout.index.phtml');
} else {
    require_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout/layout.inner.phtml');
}

# End Layout management area

$page_content = App\Minify::minify(ob_get_clean(), ['xhtml' => 1, 'cssMinifier' => '', 'jsMinifier' => '']);
exit(!empty($_seo_settings['use_mod_rewrite'])                      ? App\Router::mod_rewrite($page_content) : $page_content);

