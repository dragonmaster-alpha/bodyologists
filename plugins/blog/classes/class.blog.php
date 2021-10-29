<?php

namespace Plugins\Blog\Classes;

use App\Format;
use App\Settings;
use Plugins\Members\Classes\Members;

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
 * Be aware, violating this license agreement could result in the prosecut ion and punishment of the infractor.
 *
 * @copyright 2002- date('Y') Web Design Enterprise Corp. All rights reserved.
 */

require_once DOC_ROOT.'/plugins/members/classes/class.members.php';

class Blog extends Format
{
    public $cache;

    private $plugin;
    private $_seo_settings;
    private $_blog_settings;

    public function __construct()
    {
        $this->plugin = 'blog';
        $this->_seo_settings = \App\Settings::get('SEO');
        $this->_blog_settings = \App\Settings::get('blog');
        $this->members = new \Plugins\Members\Classes\Members;
    }
    /**
     * List blog articles in database
     * @param  integer  $start          start from
     * @param  integer  $qty            quantity to be returned
     * @param  integer  $active_only    get only active rows
     * @param  string   $category       get only belonging to certain category
     * @param  string   $poster         get only belonging to certain author
     * @param  string   $tags           get only belonging to certain tags
     * @param  string   $sort           sort by element
     * @param  string   $dir            direction to sort
     * @return array
     */
    public function list_items($start = 0, $qty = 0, $active_only = 0, $category = '', $poster = '', $tags = '', $sort = 'date', $dir = 'DESC')
    {
        $this->_destroy_old();
        
        $_WHERE = [];
        $_WHERE = "alive = '0'";

        if (!empty($poster)) {
            $_WHERE .= "
                AND poster = '".$poster."' 
            ";
        }
        if (!empty($active_only)) {
            $_WHERE .= "
                AND active = '1' 
                AND lang = '".$_SESSION['lang']."'
            ";
        }
        if (!empty($tags)) {
            $_WHERE .= "
                AND tags LIKE '%".$tags."%' 
            ";
        }
        if (!empty($category)) {
            $_WHERE .= "
                AND category LIKE '%".$category."%' 
            ";
        }
        if (!empty($sort)) {
            $_ORDER = (string) $sort." ".(string) $dir;
        }
        if (!empty($qty)) {
            $_LIMIT = [(int) $start, (int) $qty];
        }

        foreach ($this->sql_get($this->plugin, "*, count_comments(id, '".$this->plugin."') AS comments_count", $_WHERE, $_ORDER, $_LIMIT) as $data) {
            $return[] = $this->load($data);
        }

        return $return;
    }
    /**
     * blog articles counter
     * @param  int  $active     count only active rows
     * @param mixed $category
     * @param mixed $poster
     * @param mixed $tags
     * @return integral
     */
    public function count($active = '', $category = 0, $poster = '', $tags = '')
    {
        $_WHERE = [];
        $_WHERE = "alive = '0'";

        if (!empty($active)) {
            $_WHERE .= "
                AND active = '1' 
            ";
        }
        if (!empty($category)) {
            $_WHERE .= "
                AND category LIKE '%".$category."%' 
            ";
        }
        if (!empty($poster)) {
            $_WHERE .= "
                AND poster = '".$poster."' 
            ";
        }
        if (!empty($tags)) {
            $_WHERE .= "
                AND tags LIKE '%".$tags."%' 
            ";
        }

        return $this->sql_count($this->plugin, $_WHERE);
    }
    /**
     * Get blog article info based on id
     * @param  integer  $id     row id
     * @return array
     */
    public function get_items_info($id = 0)
    {
        $id = (int) $id;
        
        if (!empty($id)) {
            return $this->load($this->sql_get_one($this->plugin, "*", $id));
        }
        
        return false;
    }
    /**
     * Reload blog article's info in cache
     * @param  integer  $id     row id
     * @return boolean
     */
    public function reload_cache_item($id = 0)
    {
        $id = (int) $id;
        
        if (!empty($id)) {
            parent::cache()->delete('_cache_'.$this->plugin.'_'.$id);
            $this->get_items_info($id);
            return true;
        }

        return false;
    }
    /**
     * Get blog article from URL
     * @param  string   $url blog article URL
     * @return array
     */
    public function get_items_from_url($url = '')
    {
        $url = $this->filter($url, 1, 1);

        if (!empty($url)) {
            $data = $this->sql_get_one($this->plugin, 'id', ['url' => $url, 'active' => 1, 'alive' => 0]);

            if (!empty($data['id'])) {
                return $this->get_items_info((int) $data['id']);
            }
        }

        return false;
    }
    /**
     * Get minimum info from latest rows
     * @param  integer $start       start on
     * @param  integer $qty         quantity to collect
     * @param  integer $active_only define is all items should be active
     * @return array
     */
    public function get_latest_items($start = 0, $qty = 0, $active_only = 0)
    {
        $_WHERE = [];
        $_WHERE['alive'] = 0;

        if (!empty($active_only)) {
            $_WHERE['active'] = 1;
        }

        $_ORDER = 'date DESC';
        $_LIMIT = [(int) $start, (int) $qty];

        foreach ($this->sql_get($this->plugin, '*', $_WHERE, $_ORDER, $_LIMIT) as $data) {
            $data['url'] = $this->plugin.'/'.$data['url'];
            $return[] = $this->load($data);
        }

        return $return;
    }
    /**
     * Get blog article title
     * @param  integer  $id      row id
     * @return string
     */
    public function get_items_title($id = 0)
    {
        $id = (int) $id;
        
        if (!empty($id)) {
            $data = $this->sql_get_one($this->plugin, 'title', $id);
            return $this->filter($data['title'], 1);
        }
    }
    /**
     * Get blog article url
     * @param  integer  $id     row id
     * @return string
     */
    public function get_items_url($id = 0)
    {
        $id = (int) $id;
        
        if (!empty($id)) {
            $data = $this->sql_get_one($this->plugin, 'url', $id);
            return $this->plugin.'/'.$this->filter($data['url'], 1);
        }
    }
    /**
     * List blogs articles categories
     * @return array
     */
    public function list_categories()
    {
        foreach ($this->sql_get($this->plugin, 'DISTINCT(category) AS categories') as $data) {
            $return[] = $data['categories'];
        }

        return $return;
    }
    /**
     * Update blog article visits
     * @param  integer $id row id
     * @return boolean
     */
    public function update_visits($id = 0)
    {
        $id = (int) $id;
        
        if (!empty($id)) {
            $this->sql_sum($this->plugin, ['visits' => 1], $id);
        }
        
        return false;
    }
    /**
     * Get featured items
     * @param  integer  $qty quantity of items to collect
     * @return array
     */
    public function get_featured($qty = 5)
    {
        if (!empty($qty)) {
            $_LIMIT = [(int) $qty];
        }

        foreach ($this->sql_get($this->plugin, '*', ['featured' => 1, 'active' => 1], 'date ASC', $_LIMIT) as $data) {
            $return[] = $this->load($data);
        }

        return $return;
    }
    /**
     * Get blogs categories
     * @return array     categories information
     */
    public function get_categories()
    {
        return @json_decode($this->sql_get_one('blog_extras', 'value', ['name' => 'categories']), true);
    }
    /**
     * Get blogs tags
     * @return array     tags information
     */
    public function get_tags()
    {
        return @json_decode($this->sql_get_one('blog_extras', 'value', ['name' => 'tags']), true);
    }
    /**
     * Get blogs tags
     * @return array     tags information
     */
    public function get_all_tags()
    {
        // $data                                               = $this->filter($this->sql_get('blog', 'tags'),1);

        foreach ($this->sql_get($this->plugin, 'tags') as $data) {
            $return .= !empty($data['tags']) ? $data['tags'].',' : '' ;
        }

        return $return;
    }
    /**
     * List all blogs articles authors
     * @return array
     */
    public function get_authors()
    {
        return $this->sql_get('customers', 'id, last_name, first_name', 'grouped = 5', 'last_name, first_name ASC');


//        foreach ($this->sql_get('blog', 'DISTINCT(poster) as name', '', 'poster ASC') as $data) {
//            $return[] = $this->filter($data['name'], 1);
//        }

        return array_unique($return);
    }
    /**
     * List all blogs articles authors
     * @param mixed $author
     * @return array
     */
    public function get_author_info($author)
    {

        // foreach($this->sql_get_one('customers', '*', ['id' => $author], 'id ASC') as $data)
        // {
        //     $return[]                                       = $this->filter($data, 1);
        // }

        // $return[0]['media']                             = $this->sql_get_one('media', 'bid, image, imageId', ['belongs' => 'members', 'bid' => md5($data['id']), 'media' => 'Image'], 'imageId ASC');

        $return = $this->sql_get_one('customers', '*', ['id' => $author], 'id ASC');

        return $return;
    }
    /**
     * Get related blog articles
     * @param  integer $id  row id
     * @param  integer $qty quantity to collect
     * @return array
     */
    public function get_related($id = 0, $qty = 5)
    {
        $id = (int) $id;
        $qty = (int) $qty;

        if (!empty($id)) {
            foreach ($this->sql_get($this->plugin, '*', "id != '".$id."' AND categories = (SELECT categories FROM ".$this->prefix."_blog WHERE id = '".$id."')", 'date ASC', [$qty]) as $data) {
                $return[] = $this->load($data);
            }

            return $return;
        }
        
        return false;
    }
    /**
     * Get archives
     * @return array dates that include active blog articles
     */
    public function get_archives()
    {
        try {
            $check_month = '';

            foreach ($this->sql_get($this->plugin, 'date', ['active' => 1, 'lang' => $_SESSION['lang']], 'date DESC') as $data) {
                $formated_date = date('Y/m/d', strtotime($data['date']));
                preg_match("/([0-9]{4})\/([0-9]{2})\/([0-9]{2})/", $formated_date, $getdate);

                if ($getdate[2] == "01") {
                    $month_name = _JANUARY;
                } elseif ($getdate[2] == "02") {
                    $month_name = _FEBRUARY;
                } elseif ($getdate[2] == "03") {
                    $month_name = _MARCH;
                } elseif ($getdate[2] == "04") {
                    $month_name = _APRIL;
                } elseif ($getdate[2] == "05") {
                    $month_name = _MAY;
                } elseif ($getdate[2] == "06") {
                    $month_name = _JUNE;
                } elseif ($getdate[2] == "07") {
                    $month_name = _JULY;
                } elseif ($getdate[2] == "08") {
                    $month_name = _AUGUST;
                } elseif ($getdate[2] == "09") {
                    $month_name = _SEPTEMBER;
                } elseif ($getdate[2] == "10") {
                    $month_name = _ORTOBER;
                } elseif ($getdate[2] == "11") {
                    $month_name = _NOVEMBER;
                } elseif ($getdate[2] == "12") {
                    $month_name = _DECEMBER;
                }

                if ($month_name != $check_month) {
                    $return[] = [
                        'year' => $getdate[1],
                        'month' => $getdate[2],
                        'month_name' => $month_name
                    ];
                    
                    $check_month = $month_name;
                }
            }
            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    /**
     * count blog articles based on dates
     * @param  integer $year  Year
     * @param  integer $month Month
     * @param  integer $start Start search from
     * @param  integer $qty   Limit
     * @param  string  $sort  Sort by
     * @param  string  $dir   Sort direction
     * @return array
     */
    public function count_blog_within_dates($year = 0, $month = 0)
    {
        $year = $this->int($year);
        $month = $this->int($month);

        if (!empty($year) && !empty($month)) {
            $start_date = date('Y-m-d 00:00:00', strtotime($year.'-'.$month.'-01'));
            $end_date = date('Y-m-t 23:59:59', strtotime($year.'-'.$month.'-01'));

            return $this->sql_count($this->plugin, "date BETWEEN '".$start_date."' AND '".$end_date."' AND active ='1'");
        }
        
        return false;
    }
    /**
     * Get blog articles based on dates
     * @param  integer $year  Year
     * @param  integer $month Month
     * @param  integer $start Start search from
     * @param  integer $qty   Limit
     * @param  string  $sort  Sort by
     * @param  string  $dir   Sort direction
     * @return array
     */
    public function get_blog_within_dates($year = 0, $month = 0, $start = 0, $qty = 10, $sort = 'date', $dir = 'ASC')
    {
        $year = $this->int($year);
        $month = $this->int($month);

        if (!empty($year) && !empty($month)) {
            $start_date = date('Y-m-d 00:00:00', strtotime($year.'-'.$month.'-01'));
            $end_date = date('Y-m-t 23:59:59', strtotime($year.'-'.$month.'-01'));
        
            if (!empty($qty)) {
                $_limit = [$start, $qty];
            }

            foreach ($this->sql_get($this->plugin, '*', "date BETWEEN '".$start_date."' AND '".$end_date."' AND active ='1'", $sort.' '.$dir, $_limit) as $data) {
                $return[] = $this->load($data);
            }
            return $return;
        }
        
        return false;
    }
    /**
     * Search blog articles
     * @param  [type] $query       phrase / word to search
     * @param  [type] $active_only get only active blog articles
     * @param mixed $start
     * @param mixed $qty
     * @param mixed $sort
     * @param mixed $dir
     * @return array
     */
    public function search($query, $start = '0', $qty = 0, $active_only = 0, $sort = 'date', $dir = 'DESC')
    {
        $_arguments = ['category','title','tags','text'];
        $_WHERE = "alive = '0'";

        if (!empty($query)) {
            $_WHERE .= " 
                AND (".implode(" LIKE '%".$query."%' OR ", $_arguments)."  LIKE '%".$query."%')
            ";
        }

        if (!empty($active_only)) {
            $_WHERE .= "
                AND active = '1' 
                AND lang = '".(string) $_SESSION['lang']."'
            ";
        }
        if (!empty($sort)) {
            $_ORDER = (string) $sort." ".(string) $dir;
        }
        if (!empty($qty)) {
            $_LIMIT = [(int) $start, (int) $qty];
        }

        foreach ($this->sql_get($this->plugin, "*, count_comments(id, '".$this->plugin."') AS comments_count", $_WHERE, $_ORDER, $_LIMIT) as $data) {
            $return[] = $this->load($data);
        }

        return $return;
    }
    /**
     * Count search blog articles
     * @param  [type] $query       phrase / word to search
     * @param  [type] $active_only get only active blog articles
     * @return array
     */
    public function search_count($query, $active_only)
    {
        $_arguments = ['category','title','poster','tags','text'];
        $_WHERE = "alive = '0'";

        if (!empty($query)) {
            $_WHERE .= " 
                AND (".implode(" LIKE '%".$query."%' OR ", $_arguments)."  LIKE '%".$query."%')
            ";
        }
        
        if (!empty($active_only)) {
            $_WHERE .= "
                AND active = '1' 
                AND lang = '".$_SESSION['lang']."'
            ";
        }

        return $this->sql_count($this->plugin, $_WHERE);
    }
    /**
     * save blogs articles infomration
     * @param  array            $data data containing article information
     * @return integer          updated row id
     */
    public function save(array $data)
    {
        if (!empty($data)) {
            # Begin Transaction
            $this->begin_transaction();

            if (!empty($data['id'])) {
                $id = $data['id'];
                $data['modified'] = date('Y-m-d H:i:s');
            } else {
                $id = $this->sql_insert_empty($this->plugin);
            }
            
            $data['url'] = (!empty($data['url']) ? $this->gen_url($data['url'], $this->plugin) : $this->gen_url($data['meta_title'], $this->plugin));

            if (!empty($data['date'])) {
                $data['date'] = date('Y-m-d', strtotime($data['date']));
            } else {
                $data['date'] = date('Y-m-d H:i:s');
            }

            if (!empty($data['time'])) {
                $data['date'] .= date(' H:i:s', strtotime($data['time']));
            }

            if (!empty($data['extras'])) {
                $data['extras'] = json_encode($data['extras']);
            }
            
            if (empty($data['meta_keywords']) && !empty($data['tags'])) {
                $data['meta_keywords'] = $data['tags'];
            }

            if (empty($data['tags']) && !empty($data['meta_keywords'])) {
                $data['tags'] = $data['meta_keywords'];
            }

            if (!empty($data['categories'])) {
                $old_categories = $this->get_categories();
                $new_categories = explode(',', $data['categories']);
                $all_categories = (!empty($old_categories)) ? array_unique(array_merge($old_categories, $new_categories)) : array_unique($new_categories);

                $this->sql_update('blog_extras', ['value' => json_encode($all_categories)], ['name' => 'categories']);
            }

            if (!empty($data['tags'])) {
                $old_tags = $this->get_tags();
                $new_tags = explode(',', $data['tags']);

                if (!empty($old_categories)) {
                    $old_tags = array_unique(array_merge($old_tags, $new_tags));
                } else {
                    $all_tags = array_unique($new_tags);
                }

                $this->sql_update('blog_extras', ['value' => json_encode($all_tags)], ['name' => 'tags']);
            }

            # Save data
            $this->sql_update($this->plugin, $data, (int) $id);

            # Commit Transaction
            $this->commit();
            
            $this->reload_cache_item($id);

            return $id;
        }
        
        return false;
    }
    /**
     * Insert empty row
     * @return integer         id of newly created row
     */
    public function insert_empty()
    {
        return $this->sql_insert_empty($this->plugin);
    }
    /**
     * Save content edited from the front end
     * @param  array  $data data to be saved
     * @return [type]       [description]
     */
    public function save_content(array $data = [])
    {
        $id = (int) $data['id'];
        unset($data['id']);

        $data['title'] = $this->filter($data['title'], 1, 1);
        $data['text'] = $this->filter($data['text'], 0, 1);

        $this->sql_update($this->plugin, $data, $id);
        $this->reload_cache_item($id);

        return $id;
    }
    /**
     * Delete blog article from database
     * @param  integer  $id     row id
     * @return boolean
     */
    public function delete($id = 0)
    {
        $id = (int) $id;
        
        if (!empty($id)) {
            $this->sql_delete($this->plugin, $id);
            $this->sql_delete('comments', ['parent' => $id, 'plugin' => $this->plugin]);
            parent::cache()->delete('_cache_'.$this->plugin.'_'.$id);

            return true;
        }
        
        return false;
    }
    /**
     * Delete old record from database
     */
    public function _destroy_old()
    {
        $this->sql_delete($this->plugin, "alive = '1' AND date < '".date('Y-m-d H:i:s', strtotime('-1 hour'))."'");
    }
    /**
     * Get article images
     * @param  integer $id      row id
     * @param  integer $qty     amount of images to collect
     * @return array
     */
    public function get_images($id = 0, $qty = '')
    {
        $id = (int) $id;

        if (!empty($id)) {
            if ($qty == 1) {
                return $this->filter($this->sql_get_one('media', 'id, bid, image, imageId, title, text', ['belongs' => $this->plugin, 'bid' => md5((string) $id), 'media' => 'Image'], 'imageId ASC'));
            }
            
            if (!empty($qty)) {
                $_LIMIT = [(int) $qty];
            }

            foreach ($this->sql_get('media', 'id, bid, image, imageId, title, text', ['belongs' => $this->plugin, 'bid' => md5((string) $id), 'media' => 'Image'], 'imageId ASC', $_LIMIT) as $data) {
                $return[] = $this->filter($data, 1, 1);
            }

            return $return;
        }
        
        return false;
    }
    /**
     * Activate/ deactivate blog article
     * @param  integer  $id     row id
     * @return string           modified article title
     */
    public function manage_activation($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $this->sql_activate($this->plugin, $id);
            $this->reload_cache_item($id);
            return $this->get_items_title($id);
        }

        return false;
    }
    /**
     * Set / Unset blog article as featured
     * @param  integer $id      row id
     * @return string           modified article title
     */
    public function manage_featured($id = 0)
    {
        $id = (int) $id;
        
        if (!empty($id)) {
            $this->sql_set($this->plugin, $id, 'featured');
            $this->reload_cache_item($id);
            return $this->get_items_title($id);
        }

        return false;
    }
    /**
     * Allow / Disallow visitors to comment on blog article
     * @param  integer  $id     row id
     * @return string           modified article title
     */
    public function manage_comments($id = 0)
    {
        $id = (int) $id;
        
        if (!empty($id)) {
            $this->sql_set($this->plugin, $id, 'allow_comments');
            $this->reload_cache_item($id);
            return $this->get_items_title($id);
        }

        return false;
    }
    /**
     * count blog article comments that had not being approved
     * @param  integer  $id     row id
     * @return integer          amount of unapproved comments
     */
    public function check_unapproved_comments($id = 0)
    {
        $id = (int) $id;
        
        if (!empty($id)) {
            return ($this->sql_count('comments', ['parent' => $id, 'plugin' => $this->plugin, 'approved' => 0]) > 0 ? true : false);
        }

        return false;
    }
    /**
     * Get blog article comments
     * @param  integer $id          row id
     * @param  integer $active_only get only approved comments
     * @return array
     */
    public function get_comments($id = 0, $active_only = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $_WHERE['parent'] = $id;
            $_WHERE['plugin'] = $this->plugin;

            if (!empty($active_only)) {
                $_WHERE['approved'] = 1;
            }
            
            foreach ($this->sql_get('comments', '*', $_WHERE, 'date ASC') as $data) {
                $return[] = $this->filter($data, 1);
            }

            return $return;
        }

        return false;
    }
    /**
     * Get single comment
     * @param  integer $id comment id
     * @return array
     */
    public function get_comment($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->filter($this->sql_get_one('comments', '*', $id), 1);
        }

        return false;
    }

    # Generate json schema for google
    public function get_schema($data)
    {
        if (!empty($data)) {
            list($blog_image_width, $blog_image_height) = @getimagesize($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/uploads/blog/'.$data['media']['bid'].'/'.$data['media']['image']);

            $return['ld_json'] = [
                '@context' => 'http://schema.org',
                '@type' => 'Article',
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id' => $this->real_host()._SITE_PATH.'/'.$data['url']
                ],
                'headline' => $data['title'],
                'image' => [
                    '@type' => 'ImageObject',
                    'url' => $this->real_host().'/uploads/blog/'.$data['media']['bid'].'/'.$data['media']['image'],
                    'height' => $blog_image_height,
                    'width' => $blog_image_width,
                ],
                'datePublished' => date('c', strtotime($data['date'])),
                'dateModified' => date('c', strtotime($data['modified'])),
                'author' => [
                    '@type' => 'Person',
                    'name' => $data['poster']
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => $this->_seo_settings['sitename'],
                    'url' => $this->real_host(),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => $this->real_host().$this->_seo_settings['company_logo'],
                        'width' => 600,
                        'height' => 60
                    ]
                ],
                'description' => $data['meta_description']
            ];
    
            return json_encode($return['ld_json']);
        }

        return false;
    }
    /**
     * Load and format blog article information
     * @param  array  $data     data to be formatted
     * @return array
     */
    private function load(array $data = [])
    {
        if (!empty($data)) {
            $return = $this->filter($data, 1);
            $return['id'] = (int) $data['id'];
            $return['url'] = $this->plugin.'/'.str_replace($this->plugin.'/', '', $data['url']);
            $return['extras'] = json_decode($data['extras'], 1);
            $return['text'] = $this->filter($data['text']);
            $return['media'] = $this->get_images($data['id'], 1);
            $return['ranked'] = @round(substr((int) $data['votes'] / (int) $data['rates'], 0, 4));
            $return['comments_count'] = (int) $data['comments_count'];
            $return['comments_unapproved'] = $this->check_unapproved_comments($return['id']);

            $return['author'] = $this->members->get_items_info($return['poster']);

            if (!empty($data['tags'])) {
                foreach (explode(',', $data['tags']) as $tag) {
                    $return['tags_link'][] = '<a href="index.php?plugin=blog&amp;tag='.urlencode(strtolower($tag)).'" rel="keywords">'.strtolower($tag).'</a>';
                }
            }

            if (!empty($data['categories'])) {
                foreach (explode(',', $data['categories']) as $category) {
                    $return['categories_link'][] = '<a href="index.php?plugin=blog&amp;category='.urlencode(strtolower($category)).'" rel="category">'.strtolower($category).'</a>';
                }
            }

            $return['ld_json'] = $this->get_schema($return);
        
            return $return;
        }

        return false;
    }
}
