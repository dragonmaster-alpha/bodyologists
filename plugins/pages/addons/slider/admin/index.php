<?php

use Plugins\Pages\Addons\Slider\Classes\Slider;

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
    $slider = new Plugins\Pages\Addons\Slider\Classes\Slider;
    $slider_settings = $settings->get('Slider');
    
    switch ($_REQUEST['op']) {
    
        default:
            
            $meta['title'] = 'Slider Images';
            
            $items_info = $slider->list_items();
            
            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/pages/addons/slider/admin/layout/layout.slider.phtml');
            $layout = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            
        break;
        
        case "config":
            
            try {
                ob_start('App\Router::mod_rewrite');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/pages/addons/slider/admin/layout/layout.slider.config.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                die($e->getMessage());
            }
            
        break;
        
        case "save":
            
            try {
                foreach ($_POST as $key => $value) {
                    if ($key != 'op' && $key != 'addon' && $key != 'plugin') {
                        $_save_array[$key] = $value;
                    }
                }

                $settings->set_settings('Slider', $_save_array);
                $administrator->record_log("Slider Settings modified", "A new slider settings modification request has been executed");
                $helper->redirect('admin/admin.php?plugin=pages&addon=slider');
            } catch (Exception $e) {
                die($e->getMessage());
            }
            
        break;
        
        case "edit":
            
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $id = (int) $_GET['id'];
                $item = $slider->get_item_info($id);
                
                ob_start('App\Router::mod_rewrite');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/pages/addons/slider/admin/layout/layout.slider.edit.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                die($e->getMessage());
            }
            
        break;
        
        case "upload":
        
            try {
                ob_start('App\Router::mod_rewrite');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/pages/addons/slider/admin/layout/layout.slider.upload.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                die($e->getMessage());
            }
            
        break;
        
        case "update":
            
            try {
                $data = $helper->filter($_POST, 1, 1);

                $slider->update($data);
                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }
            
        break;
        
        case "delete":
            
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }

                $id = (int) $_GET['id'];
                $item_info = $slider->get_item_info($id);
                $slider->delete($id);
                
                foreach (glob($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/uploads/slider/c4ca4238a0b923820dcc509a6f75849b/*'.$item_info['image']) as $file) {
                    @unlink($file);
                }

                $helper->json_response(['answer' => 'done', 'message' => 'Action successfully done.']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }
            
        break;
        
        case "reorder":

            $slider->reorder($_GET['item']);
            $helper->json_response(['answer' => 'done', 'message' => 'Action successfully done.']);
            
        break;
    
    }
} else {
    header("Location: index.php");
    exit();
}
