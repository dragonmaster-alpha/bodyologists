<?php

use Plugins\Pages\Addons\Gallery\Classes\Gallery;

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

$galleries = new Gallery;

switch ($_REQUEST['op']) {
    default:

        if (!empty($_REQUEST['id'])) {
            $id = (int) $_REQUEST['id'];
            $album_info = $galleries->get_album_info($id);
            $items_info = $galleries->list_items(0, 0, $id, 'gallery');

            $meta['name'] = $album_info['name'];
            $meta['title'] = $album_info['name'];
            $meta['top_name'] = $album_info['name'];
            
            if (empty($items_info)) {
                $_SESSION['error']['message'] = 'The gallery you are trying to see is not available at this time, please try again later.';
                $helper->redirect();
            }

            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/pages/addons/gallery/layout/layout.gallery.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        } else {
            $items_info = $galleries->list_albums();

            $meta['name'] = 'Photo Gallery';
            $meta['title'] = 'Photo Gallery';
            $meta['top_name'] = 'Photo Gallery';
        
            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/pages/addons/gallery/layout/layout.index.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        }
        
    break;
}
