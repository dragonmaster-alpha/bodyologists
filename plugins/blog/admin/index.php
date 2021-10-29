<?php

use App\Flash as Flash;
use App\MultiArray as MultiArray;
use App\XMLGenerator as XMLGenerator;

require_once APP_DIR.'/xml/class.sitemap.php';

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

if (!strstr($_SERVER['PHP_SELF'], "admin.php")) {
    $helper->redirect(_SITE_PATH);
}

$plugin_name = basename(str_replace('admin', '', dirname(__FILE__)));
if ($administrator->admin_access($plugin_name)) {
    # Class inclusion
    require_once DOC_ROOT.'/plugins/blog/classes/class.blog.php';
    require_once DOC_ROOT.'/plugins/comments/classes/class.comments.php';
    $blog_class = new Plugins\Blog\Classes\Blog;
    $comments = new Plugins\Comments\Classes\Comments;
    $blog_settings = $settings->get('blog');

    define('_TOTAL_TO_LOAD', 20);

    switch ($_REQUEST['op']) {
        default:
        
            if (empty($blog_settings)) {
                $helper->redirect('admin/admin.php?plugin=blog&op=settings');
            }

            $meta['title'] = 'Blog Summary';

            if (isset($_GET['q'])) {
                if (!empty($_GET['q'])) {
                    $query = $helper->filter($_GET['q'], 1, 1);
                    $items_info = $blog_class->search($query, 0, _TOTAL_TO_LOAD);

                    if (!empty($items_info)) {
                        ob_start('App\Router::mod_rewrite');
                        foreach ($items_info as $item) {
                            include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/blog/admin/layout/layout.blog.list.phtml');
                        }
                        ob_end_flush();
                    } else {
                        ob_start('App\Router::mod_rewrite');
                        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.empty.phtml');
                        ob_end_flush();
                    }
                } else {
                    $items_info = $blog_class->list_items(0, _TOTAL_TO_LOAD);

                    ob_start('App\Router::mod_rewrite');
                    foreach ($items_info as $item) {
                        include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/blog/admin/layout/layout.blog.list.phtml');
                    }
                    ob_end_flush();
                }
            } else {
                $items_info = $blog_class->list_items(0, _TOTAL_TO_LOAD);

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/blog/admin/layout/layout.blog.phtml');
                $layout = ob_get_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            }

        break;

        case 'more':

            $start = (int) $_GET['s'];
            $items_info = $blog_class->list_items($start, _TOTAL_TO_LOAD);

            if (!empty($items_info)) {
                ob_start('App\Router::mod_rewrite');
                foreach ($items_info as $item) {
                    include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/blog/admin/layout/layout.blog.list.phtml');
                }
                ob_end_flush();
            }

        break;

        case "manage_activation":
            
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $run = $blog_class->manage_activation((int) $_GET['id']);

                $administrator->record_log("Blog activation/deactivation", "Activation/Deactivation request for '".$run."' has been executed");
                
                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;

        case "manage_featured":

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                
                $run = $blog_class->manage_featured((int) $_GET['id']);

                $administrator->record_log("Blog featured/unfeatured", "Featured/Unfeatured request for '".$run."' has been executed");
                
                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }
            
        break;

        case "manage_comments":

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }

                $run = $blog_class->manage_comments((int) $_GET['id']);

                $administrator->record_log("Blog Comments", "FAllow/Not Allow Comments request for '".$run."' has been executed");

                $helper->json_response(['answer' => 'done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;

        case "edit":

            try {
                if (isset($_GET['id'])) {
                    $id = (int) $_GET['id'];
                    $item = $blog_class->get_items_info($id);
                    $available_comments = $comments->list_items(0, 0, 0, $id);
                    $item_images = $blog_class->get_images($item['id']);
                } else {
                    $item = [];
                    $item['id'] = $blog_class->insert_empty();
                }
                
                $meta['title'] = 'Blog Management';

                $available_languages = $helper->get_languages();
                $available_posters = $blog_class->get_authors();
                $available_categories = $blog_class->get_categories();
                $available_tags = $blog_class->get_tags();
        
                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/blog/admin/layout/layout.blog.edit.phtml');
                $layout = ob_get_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'back');
            }

        break;

        case "save":

            try {
                if (empty($_POST['title'])) {
                    throw new Exception('You must enter a title for this article');
                }
                if (empty($_POST['text'])) {
                    throw new Exception('You must enter the content for this article');
                }

                $data = $helper->filter($_POST, 1, 1);
                $data['text'] = $helper->filter($_POST['text'], 0, 1);

                if (empty($data['meta_description'])) {
                    $data['meta_description'] = $helper->reduce_words($helper->filter($data['text'], 1));
                }

                $result = $blog_class->save($data);

                $administrator->record_log("Blog modified", "Modification request for '".$data['title']."' has been successfully edited");

                # Generate SitemapXML
                new \App\Xml\SitemapXML;
                
                if ($data['save_and'] == 1) {
                    $helper->redirect('admin/admin.php?plugin=blog&op=edit');
                } elseif ($data['save_and'] == 2) {
                    $helper->redirect('admin/admin.php?plugin=blog&op=edit&id='.$result);
                } else {
                    $helper->redirect('admin/admin.php?plugin=blog');
                }
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'back');
            }

        break;

        case "delete":

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }
                $id = (int) $_GET['id'];
                $item_info = $blog_class->get_items_info($id);
                $blog_class->delete($id);
                
                $administrator->record_log("blog article deleted", "Deletion request for '".$item_info['title']."' has been executed");
                $administrator->remove_dir($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/uploads/blog/'.md5((string) $id));

                # Generate SitemapXML
                new \App\Xml\SitemapXML;
        
                $helper->json_response(['answer' => 'done', 'message' => 'Action successfully done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;

        # Settings Handler
        case "settings":

            $meta['title'] = 'Blog Settings';
            
            if (empty($blog_settings['display'])) {
                $blog_settings['display'] = 10;
            }
            
            if (empty($blog_settings['rss_qty'])) {
                $blog_settings['rss_qty'] = 10;
            }
            
            if (empty($blog_settings['comments_lenth'])) {
                $blog_settings['comments_lenth'] = 0;
            }
            
            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/blog/admin/layout/layout.blog.settings.phtml');
            $layout = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');

        break;

        case "save_settings":

            $settings->set('blog', $_POST);
            
            $administrator->record_log("Blog settings modification", "A new blog settings modification request has been executed");
            Flash::set('success', 'Blog settings has being successfully saved.', 'admin/admin.php?plugin=blog');
            
        break;

        # Images Handlers
        case "load_images":

            $id = (int) $_GET['id'];
            $item_images = $blog_class->get_images($id);

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/blog/admin/layout/layout.load_images.phtml');
            ob_end_flush();

        break;

        # Comments Handlers
        case "comments_approve":

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }

                $id = (int) $_GET['id'];
                $comments->approve($id);
                
                $administrator->record_log("Blog Article comment approved", "Comment with ID: '".$id."' has been approved");
                $helper->json_response(['answer' => 'done', 'message' => 'Action successfully done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;

        case "comments_delete":

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select an item to proceed with this action.');
                }

                $id = (int) $_GET['id'];
                $comments->delete($id);
                
                $administrator->record_log("Blog article comment deleted", "Deletion request for comment with ID: '".$id."' has been executed");
                $helper->json_response(['answer' => 'done', 'message' => 'Action successfully done']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;
    }
} else {
    header("Location: index.php");
    exit();
}
