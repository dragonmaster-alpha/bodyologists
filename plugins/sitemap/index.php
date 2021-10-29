<?php

use Plugins\Sitemap\Classes\Sitemap;

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
    header("Refresh: 5; url=index.php");
}

$plugin_name = basename(dirname(__FILE__));
$helper->get_plugin_lang($plugin_name);

# Class inclusion
$sitemap = new Plugins\Sitemap\Classes\Sitemap;

$meta['title'] = $helper->config['sitename'].' '._SITE_MAP;
$entries = [];

# Pages
$entries['pages'] = $helper::cache()->get('_sitemap_pages_data_');

if ($entries['pages'] == null) {
    $entries['pages'] = $sitemap->get_pages();
    $helper::cache()->set('_sitemap_pages_data_', $entries['pages']);
}

# Members
if ($helper->is_plugin('members')) {
    $entries['members'][] = [
        'url' => 'index.php?plugin=members',
        'name' => 'Members area'
    ];
    $entries['members'][] = [
        'url' => 'index.php?plugin=members&amp;op=register',
        'name' => 'Members registration'
    ];
    $entries['members'][] = [
        'url' => 'index.php?plugin=members&amp;op=lost_password',
        'name' => 'Members password recovery'
    ];
}

# Products
if ($helper->is_plugin('products')) {
    $entries['categories'] = $helper::cache()->get('_sitemap_categories_data_');

    if ($entries['categories'] == null) {
        $entries['categories'] = $sitemap->get_products_categories();
    
        $helper::cache()->set('_sitemap_categories_data_', $entries['categories']);
    }

    $entries['products'] = $helper::cache()->get('_sitemap_products_data_');

    if ($entries['products'] == null) {
        $entries['products'] = $sitemap->get_products();
        $helper::cache()->set('_sitemap_products_data_', $entries['products']);
    }
}

# Blog
if ($helper->is_plugin('blog')) {
    $entries['blog'] = $helper::cache()->get('_sitemap_blogs_data_');

    if ($entries['blog'] == null) {
        $entries['blog'] = $sitemap->get_blog();
        $helper::cache()->set('_sitemap_blogs_data_', $entries['blog']);
    }
}

ob_start('ob_gzhandler');
include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/sitemap/layout/layout.index.phtml');
$modcontent = ob_get_clean();
include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
