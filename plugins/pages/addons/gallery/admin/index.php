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

$plugin_name = 'pages';

if ($administrator->admin_access($plugin_name)) {
    # Class inclusion
    $gallery = new Plugins\Pages\Addons\Gallery\Classes\Gallery;

    switch ($_REQUEST['op']) {
        default:
            
            if (!isset($_GET['album'])) {
                $meta['title'] = 'Albums';
                
                $items_info = $gallery->list_albums();

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/pages/addons/gallery/admin/layout/layout.albums.phtml');
                $layout = ob_get_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            } else {
                $meta['title'] = 'Galleries';
                
                $id = (int) $_GET['album'];

                $album_info = $gallery->get_album_info($id);
                $items_info = $gallery->list_items(0, 0, $id);

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/pages/addons/gallery/admin/layout/layout.gallery.phtml');
                $layout = ob_get_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            }
            
        break;
        
        case "create_album":
        
            try {
                if (empty($_GET['album'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $album_name = $helper->filter($_GET['album']);
                $album_id = $gallery->insert_album($album_name);
                $helper->json_response(['answer' => 'done', 'redirect' => 'pages/gallery/?album='.$album_id]);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }
            
        break;
        
        case "delete_album":
            
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $id = (int) $_GET['id'];

                $gallery->delete_album($id);
                $administrator->remove_dir($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/uploads/gallery/'.md5($id));
                $helper->json_response(['answer' => 'reload']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }
            
        break;
        
        case "edit_images":
            
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $id = (int) $_GET['id'];
                $item = $gallery->get_item_info($id);
                
                ob_start('App\Router::mod_rewrite');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/pages/addons/gallery/admin/layout/layout.gallery.edit.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                die($e->getMessage());
            }
            
        break;
        
        case "upload_images":
        
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $id = (int) $_GET['id'];

                ob_start('App\Router::mod_rewrite');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/pages/addons/gallery/admin/layout/layout.gallery.upload.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                die($e->getMessage());
            }
            
        break;

        case "add_youtube":
            
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $id = (int) $_GET['id'];

                ob_start('App\Router::mod_rewrite');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/pages/addons/gallery/admin/layout/layout.gallery.videos.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                die($e->getMessage());
            }
            
        break;
        
        case "update_images":
            
            try {
                $data = $helper->filter($_POST, 1, 1);
                
                $gallery->update_images($data);
                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }
            
        break;
        
        case "delete_image":
            
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }

                $id = (int) $_GET['id'];
                $item_info = $gallery->get_item_info($id);
                $gallery->delete_image($id);
                
                foreach (glob($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/uploads/'.$item_info['belongs'].'/'.$item_info['bid'].'/*'.$item_info['image']) as $file) {
                    @unlink($file);
                }
                
                $administrator->record_log("Image deleted", "Deletion request for 'uploads/".$item_info['belongs']."/".$item_info['bid']."/".$item_info['image']."' has been executed");
                $helper->json_response(['answer' => 'done', 'message' => 'uploads/'.$item_info['belongs'].'/'.$item_info['bid'].'/'.$item_info['image']]);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }
            
        break;
        
        case "reorder":
            
            $gallery->reorder_images($_GET['item']);
            $helper->json_response(['answer' => 'done', 'message' => 'Action successfully done.']);
            
        break;

        case 'gallery_dialog':

            $albums_info = $gallery->list_albums();
            if (!empty($albums_info)) {
                foreach ($albums_info as $album) {
                    $result[] = ['id' => $album['id'], 'name' => $album['name']];
                }

                $helper->json_response($result);
            }
            
        break;
    }
} else {
    header("Location: index.php");
    exit();
}
