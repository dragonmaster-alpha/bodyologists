<?php
require_once 'app/class.flash.php';
require_once 'app/class.breadcrumbs.php';
require_once 'app/class.log.php';
require_once 'app/class.pagenav.php';
require_once 'plugins/blog/classes/class.blog.php';

use App\Flash as Flash;
use App\Log as Log;
use App\PageNav as PageNav;
use Plugins\Blog\Classes\Blog as Blog;

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
    exit();
}

$plugin_name = basename(dirname(__FILE__));
$helper->get_plugin_lang($plugin_name);

$blogs = new Blog;
$blog_settings = $settings->get('blog');

if (!$members->is_user()) {
    $_SESSION['referer'] = 'members/articles';
    $helper->redirect('members');
}

$display = 20;
$start = (isset($_GET['start'])) ? (int) $_GET['start'] : 0;

$meta['name'] = 'Articles';

switch ($_REQUEST['op']) {
    default:

        if (!empty($_REQUEST['url'])) {
            unset($_REQUEST['op']);
            $item = $blogs->get_items_from_url($_REQUEST['url']);
            $id = (int) $item['id'];

            if (empty($item)) {
                Flash::set('error', 'The article you are trying to see is not available at this time, please try again later.', 'back');
            }

            $blogs->update_visits($id);
            
            # Meta Information
            $meta['name'] = $item['title'];
            $meta['title'] = $item['title'];
            $meta['description'] = $item['meta_description'];
            $meta['canonical'] = $item['url'];

            if (!empty($item['media'])) {
                $meta['image'] = '/uploads/blog/'.$item['media']['bid'].'/'.$item['media']['image'];
            }
            $breadcrumbs = new Breadcrumbs([$item['url'] => $item['title']]);

            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/articles/layout.articles.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        } else {
            $meta['title'] = 'Articles';
            $meta['name'] = 'Articles';

            $items_info = $blogs->list_items($start, $display, 1, '', $_SESSION['user_info']['id']);

            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/articles/layout.articles.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        }

    break;

    case 'add':

        if (!empty($_POST)) {
            if (isset($_REQUEST['honey']) && !empty($_REQUEST['honey'])) {
                break;
            }
                        
            $data = $helper->filter($_POST, '1', 1);
            $data['poster'] = (int) $_SESSION['user_info']['id'];
            $data['active'] = 1;
            $data['alive'] = 0;

            $data['title'] = ucfirst(strtolower($data['title']));

            $data['text'] = htmlentities($helper->filter(str_replace('contenteditable', 'data-tx', $_POST['text']), 0, 1));
            $data['date'] = (empty($data['date'])) ? date('Y-m-d') : $data['date'];
            $data['url'] = $data['id'].'-'.$frm->gen_url($data['meta_title'], 'blog');

            $result = $blogs->save($data);

            try {
                new Log('[Article Added] New Article has been added by '.$_SESSION['user_info']['full_name'].' titled '.$data['name']);
                Flash::set('success', 'Your Article has been added.', 'members/articles');
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'back');
            }
        } else {
            if (!empty($_GET['id'])) {
                $item = $blogs->get_items_info($_GET['id']);
            } else {
                $item['id'] = $blogs->insert_empty();
            }

            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/articles/layout.articles.edit.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        }

    break;

    case 'delete':

        try {
            if (empty($_GET['id'])) {
                throw new Exception('You must select an item to proceed with this action.');
            }

            # Check if it is the owner
            $blogs->delete($_GET['id']);
            Flash::set('success', 'Your article has been deleted.', 'back');
        } catch (Exception $e) {
            Flash::set('error', $e->getMessage(), 'back');
        }
        
    break;
}
