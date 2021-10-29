<?php

use App\Breadcrumbs as Breadcrumbs;
use App\Flash as Flash;
use App\ObjectType;
use App\PageNav as PageNav;
use App\Security\Captcha as Captcha;
use App\Stats;
use App\Validator as Validator;
use Plugins\Blog\Classes\Blog as Blog;
use Plugins\Comments\Classes\Comments as Comments;

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
    Header("Location: /index.php");
    exit();
}

require_once APP_DIR.'/class.object_type.php';

$plugin_name = basename(dirname(__FILE__));
$helper->get_plugin_lang($plugin_name);

require_once('plugins/blog/classes/class.blog.php');
require_once('plugins/comments/classes/class.comments.php');
$blogs = new Blog;
$blog_settings = $settings->get('blog');
$comments = new Comments();

$display = (!empty($blog_settings['display']))   ? $blog_settings['display'] : 10;
$start = (isset($_GET['start']))               ? (int) $_GET['start']       : 0;

$meta['name'] = 'Blog';

switch ($_REQUEST['op']) {
    default:
        
        if (!empty($_REQUEST['url'])) {
            unset($_REQUEST['op']);
            $item = $blogs->get_items_from_url($_REQUEST['url']);
            $id = (int) $item['id'];

            if (empty($item)) {
                $_SESSION['error']['message'] = 'The article you are trying to see is not available at this time, please try again later.';
                $helper->redirect();
            }

            // if(!isset($_SESSION['blogs']['views']))
            // {
            //     $_SESSION['blogs']['views'][]                   = $id;
            //     $blogs->update_visits($id);
            // }
            // else
            // {
            //     if(!in_array($id, $_SESSION['blogs']['views']))
            //     {
            //         $blogs->update_visits($id);
            //     }
            // }

            // Track page view
            (new Stats())->track(ObjectType::ARTICLE, $id);
            
            # Meta Information
            $meta['name'] = $item['title'];
            $meta['title'] = "Blog";//$item['title'];
            $meta['keywords'] = $item['tags'];
            $meta['description'] = $item['meta_description'];
            $meta['canonical'] = $item['url'];

            if (!empty($item['media'])) {
                $meta['image'] = '/uploads/blog/'.$item['media']['bid'].'/'.$item['media']['image'];
            }
            
            $breadcrumbs = new Breadcrumbs([$item['url'] => $item['title']]);

            // if(!empty($blog_settings['related_articles']))
            // {
            //     $related_articles                               = ($blog_settings['related_articles']) ? $blog_settings['related_articles'] : 5;
            //     $related                                        = $blogs->get_related($id, $related_articles);
            //     $allowRelated                                   = TRUE;
            // }
            
            if ($item['allow_comments']) {
                $allowComments = true;
                $comments_list = $comments->list_items(0, 0, 0, $id, 1);
            }

            $item['text'] = $helper->format_addons($item['text']);

            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/blog/layout/layout.article.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        } else {
            $data = $helper->filter($_REQUEST, 1, 1);

            if (!empty($_REQUEST['category'])) {
                $category = $data['category'];
                $items_info = $blogs->list_items($start, $display, 1, $data['category']);
                $countAll = $blogs->count(1, $data['category']);

                $meta['name'] = $meta['title'] = _LATEST_NEWS.' '._ON.' '._CATEGORY.': '.$data['category'];

                $breadcrumbs = new Breadcrumbs(['blog/?category='.$data['category'] => $data['category']]);
            } elseif (!empty($_REQUEST['author'])) {
                $data['author'] = urldecode($data['author']);
                $items_info = $blogs->list_items($start, $display, 1, '', $data['author']);
                $countAll = $blogs->count(1, 0, $data['author']);

                $meta['name'] = $meta['title'] = _LATEST_NEWS.' '._FROM.' '.$data['author'];

                $breadcrumbs = new Breadcrumbs(['blog/?author='.$data['author'] => $data['author']]);
            } elseif (!empty($_REQUEST['tag'])) {
                $data['tag'] = urldecode($data['tag']);
                $items_info = $blogs->list_items($start, $display, 1, 0, 0, $data['tag']);
                $countAll = $blogs->count(1, 0, 0, $data['tag']);

                $meta['name'] = $meta['title'] = _LATEST_NEWS.' '._FROM.' '.$data['tag'];
                $breadcrumbs = new Breadcrumbs(['blog/?tag='.$data['tag'] => $data['tag']]);
            } else {
                $items_info = $blogs->list_items($start, $display, 1);
                $countAll = $blogs->count(1);

                $meta['name'] = $meta['title'] = "Blog";
                $breadcrumbs = new Breadcrumbs();
            }

            if ($blog_settings['display'] && $countAll > $display) {
                $havePagination = true;
                $pagenav = new PageNav($countAll, $display, $start, 'start', $helper->cleanQueryString());
                $paginator = $pagenav->renderNav();
            }

            ob_start();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/blog/layout/layout.index.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        }

    break;

    case 'archives':

        $year = $helper->int($_REQUEST['year']);
        $month = $helper->int($_REQUEST['month']);
        $meta['name'] = $meta['title'] = _LATEST_NEWS.' '._ARCHIVES.': '.$year.'/'.$month;
        $breadcrumbs = new Breadcrumbs([$_REQUEST['url'] => $meta['title']]);
        
        # Getting info from class
        $items_info = $blogs->get_blog_within_dates($year, $month);
        $countAll = $blogs->count_blog_within_dates($year, $month);
        
        if ($blog_settings['display'] && $countAll > $display) {
            $havePagination = true;
            $pagenav = new PageNav($countAll, $display, $start, 'start', $helper->cleanQueryString());
            $paginator = $pagenav->renderNav();
        }

        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/blog/layout/layout.index.phtml');
        $modcontent = ob_get_clean();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');

    break;

    case 'add_comment':

        try {
            if (isset($_REQUEST['honey']) && !empty($_REQUEST['honey'])) {
                break;
            }

            $id = (int) $_POST['id'];
            $item_info = $blogs->get_items_info($id);
            $data = $helper->filter($_POST, 1, 1);
            
            foreach ($data as $key => $value) {
                $_SESSION['comments'][$key] = $value;
            }

            if (empty($_SESSION['comments']['name'])) {
                throw new Exception(_INCORRECT_NAME_NAME);
            }
            if (empty($_SESSION['comments']['email'])) {
                throw new Exception(_INCORRECT_EMAIL_ADDRESS);
            }
            if (!$helper->check_email($_SESSION['comments']['email'])) {
                throw new Exception(_INCORRECT_EMAIL_ADDRESS);
            }
            if (empty($_SESSION['comments']['text'])) {
                throw new Exception(_ERROR_EMPTY_COMMENT);
            }
            if (empty($item_info['id'])) {
                throw new Exception(_ERROR_EMPTY_ITEM_INFO);
            }

            if ($item_info['allow_comments']) {
                if (empty($blog_settings['allowed_comments']) || !empty($blog_settings['allowed_comments']) && $members->is_user()) {
                    $_SESSION['comments']['approved'] = $blog_settings['auto_approve'];
                    
                    if (!empty($blog_settings['comments_lenth'])) {
                        $_SESSION['comments']['text'] = $helper->reduce_words($_SESSION['comments']['text'], $blog_settings['comments_lenth']);
                    }

                    $possible_spam = $helper->check_spam($_SERVER['REMOTE_ADDR'], $_SESSION['comments']['name'], $_SESSION['comments']['email'], $_SESSION['comments']['text'], $_SERVER["HTTP_REFERER"]);

                    if ($possible_spam) {
                        $_SESSION['comments']['text'] = '[POSSIBLE SPAM] '.$_SESSION['comments']['text'];
                        
                        if (!empty($blog_settings['span_handler'])) {
                            if ($blog_settings['span_handler'] == 1) {
                                throw new Exception(_ERROR_NO_SPAM);
                            } elseif ($blog_settings['span_handler'] == 2) {
                                $_SESSION['comments']['approved'] = 0;
                            }
                        }

                        $_SESSION['comments']['span'] = 1;
                    }

                    $_SESSION['comments']['plugin'] = 'blog';
                    $_SESSION['comments']['parent'] = $id;
                    $_SESSION['comments']['owner'] = $_SESSION['user_info']['id'];
                    $_SESSION['comments']['email'] = (!empty($_SESSION['user_info']['email']) ? $_SESSION['user_info']['email'] : $_SESSION['comments']['email']);
                    $_SESSION['comments']['name'] = ($_SESSION['user_info']['first_name']) ? $_SESSION['user_info']['first_name'].' '.$_SESSION['user_info']['last_name'][0] : $_SESSION['comments']['name'];
                    $_SESSION['comments']['votes'] = 1;

                    unset($_SESSION['comments']['id']);

                    $comments->save($_SESSION['comments']);

                    # send admin email in case is needed
                    if (!empty($blog_settings['email_admin'])) {
                        if ($blog_settings['notify_on'] == 'on_spam' && $possible_spam || $blog_settings['notify_on'] == 'all') {
                            ob_start();
                            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/blog/layout/emails/layout.comments.email.phtml');
                            $mssg = ob_get_clean();
                            $helper->send_emails('New comment submitted on '.$helper->config['sitename'].' blog', $mssg, '', 'Comments Manager', $blog_settings['notification_email']);
                        }
                    }
                }
            }
            
            unset($_SESSION['comments']['text']);

            if ($_SESSION['comments']['approved'] == 0) {
                $_SESSION['success']['message'] = _COMMENT_WILL_BE_APPROVED_SOON;
            }

            # Redirect
            $helper->redirect($item_info['url'].'#comments');
        } catch (Exception $e) {
            Kernel_Classes_Flash::set('error', $e->getMessage(), $item_info['url'].'#enter_comment');
        }

    break;

    case 'comment_vote':

        try {
            $id = (int) $_GET['id'];
            $article = (int) $_GET['article'];
            $item_info = $blogs->get_items_info($id);

            if (empty($id)) {
                throw new Exception(_ERROR_EMPTY_ITEM_INFO);
            }
            if (empty($article)) {
                throw new Exception(_ERROR_EMPTY_ITEM_INFO);
            }

            if (!empty($_GET['action']) && !in_array($id, $_SESSION['comments_votes'])) {
                if ($_GET['action'] == 'unlike') {
                    $comments->vote($id, '-1');
                } else {
                    $comments->vote($id, '+1');
                }

                //$_SESSION['comments_votes'][]                   = $id;
            }
            # Redirect
            $helper->redirect($item_info['url']."#comment_".$id);
        } catch (Exception $e) {
            Kernel_Classes_Flash::set('error', $e->getMessage(), $item_info['url'].'#comment_'.$id);
        }

    break;

    case 'search':

        $query = $helper->filter(urldecode($_REQUEST['q']), 1, 1);

        $search_results['blog'] = $blogs->search($query, $start, $display, 1);
        $countAll = $blogs->search_count($query, 1);

        if ($blog_settings['display'] && $countAll > $display) {
            $havePagination = true;
            $pagenav = new PageNav($countAll, $display, $start, 'start', $helper->cleanQueryString());
            $paginator = $pagenav->renderNav();
        }

        $meta['name'] = $meta['title'] = "Blog";

        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/blog/layout/layout.search.phtml');
        $modcontent = ob_get_clean();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');

    break;

    case 'rss':

        $items_info = $blogs->list_items($start, $display, 1);
        $rss2_feed = new App\Xml\FeedWriter('RSS2');

        $rss2_feed->setTitle($helper->config['sitename'].' RSS Feed');
        $rss2_feed->setLink('http://www.'.$helper->site_domain().'/blog/rss');
        $rss2_feed->setDescription($helper->config['sitename'].' Latest news and articles RSS Feed ');
        $rss2_feed->setImage($helper->config['sitename'].' RSS Feed', 'http://www.'.$helper->site_domain().'/blog/rss', 'http://www.'.$helper->site_domain().'/images/logo.jpg');
        $rss2_feed->setChannelElement('language', 'en-us');
        $rss2_feed->setChannelElement('pubDate', date('D, d W Y H:i:s P'));
        foreach ($items_info as $item) {
            $rss_item = $rss2_feed->createNewItem();
            $rss_item->setTitle($item['title']);
            $rss_item->setLink('http://www.'.$helper->site_domain().'/'.$item['url']);
            $rss_item->setDate($item['date']);
            if (!empty($blog_settings['header'])) {
                $rss_item->setDescription($helper->reduce_words($item['text'], $blog_settings['header']));
            } else {
                $rss_item->setDescription($item['text']);
            }
            $rss_item->addElement('author', $item['poster']);
            $rss_item->addElement('guid', $item['url'], ['isPermaLink' => 'true']);
            $rss2_feed->addItem($rss_item);
        }
        
        $rss2_feed->genarateFeed();

    break;

    case 'atom':
        
        $items_info = $blogs->list_items($start, $display, 1);
        $atom_feed = new App\Xml\FeedWriter('ATOM');

        $atom_feed->setTitle($helper->config['sitename'].' Atom Feed');
        $atom_feed->setLink('http://www.'.$helper->site_domain().'/blog/atom');
        $atom_feed->setChannelElement('updated', date('Y-m-d\TH:i:sP', time()));
        $atom_feed->setChannelElement('author', ['name' => $helper->config['sitename']]);

        foreach ($items_info as $item) {
            $atom_item = $atom_feed->createNewItem();
            $atom_item->setTitle($item['title']);
            $atom_item->setLink('http://www.'.$helper->site_domain().'/'.$item['url']);
            $atom_item->setDate(time());
            if (!empty($blog_settings['header'])) {
                $atom_item->setDescription($helper->reduce_words($item['text'], $blog_settings['header']));
            } else {
                $atom_item->setDescription($item['text']);
            }
            $atom_feed->addItem($atom_item);
        }

        $atom_feed->genarateFeed();

    break;

    case 'categories':

        $items_info = $blogs->list_categories();

        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/blog/layout/layout.categories.phtml');
        $modcontent = ob_get_clean();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');

    break;

    case 'save':

        if ($administrator->admin_access($plugin_name)) {
            $blogs->save_content($_POST);
            $administrator->record_log("Blog article modified", "Modification request for '".$helper->filter($_POST['title'], 1)."' has been successfully edited");
        }

    break;
//
    case 'search2':
        $blogs = new Plugins\Blog\Classes\Blog;
        $search_results['blog'] = $blogs->search($query, 0, _MULTIPLE_SEARCH_RESULTS, 1);
        break;
}
