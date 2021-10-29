<?php

namespace Plugins\Comments\Classes;

use App\Format as Format;

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

class Comments extends Format
{
    private $plugin;

    public function __construct()
    {
        $this->plugin = 'comments';
    }
    /**
     * List comments
     * @param  integer  $start          start from
     * @param  integer  $qty            quantity to be returned
     * @param  integer  $spam           get only non-spam rows
     * @param  string   $parent         parent id
     * @param  string   $approved_only  get only approved rows
     * @param  string   $sort           sort by element
     * @param  string   $dir            direction to sort
     * @return array
     */
    public function list_items($start = 0, $qty = 0, $spam = 0, $parent = 0, $approved_only = 0, $sort = 'date', $dir = 'DESC')
    {
        try {
            $_WHERE['plugin'] = 'blog';
            $_WHERE['spam'] = (!empty($spam)) ? 1 : 0;

            if (!empty($parent)) {
                $_WHERE['parent'] = $parent;
            }
            if (!empty($approved_only)) {
                $_WHERE['approved'] = 1;
                $_WHERE['lang'] = $_SESSION['lang'];
            }
            if (!empty($sort)) {
                $_ORDER = $sort." ".$dir;
            }
            if (!empty($qty)) {
                $_LIMIT = [$start, $qty];
            }

            foreach ($this->sql_get('comments', '*, get_avatar(owner) AS avatar', $_WHERE, $_ORDER, $_LIMIT) as $data) {
                $return[] = $this->load($data, $approved_only);
            }
            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    /**
     * List reviews
     * @param  integer  $start          start from
     * @param  integer  $qty            quantity to be returned
     * @param  integer  $spam           get only non-spam rows
     * @param  string   $parent         parent id
     * @param  string   $approved_only  get only approved rows
     * @param  string   $sort           sort by element
     * @param  string   $dir            direction to sort
     * @return array
     */
    public function list_reviews($start = 0, $qty = 0, $spam = 0, $parent = 0, $approved_only = 0, $sort = 'date', $dir = 'DESC')
    {
        try {
            $_WHERE['plugin'] = 'products';
            $_WHERE['spam'] = (!empty($spam)) ? 1 : 0;

            if (!empty($parent)) {
                $_WHERE['parent'] = $parent;
            }

            if (!empty($approved_only)) {
                $_WHERE['approved'] = 1;
                $_WHERE['lang'] = $_SESSION['lang'];
            }
            if (!empty($sort)) {
                $_ORDER = $sort." ".$dir;
            }
            if (!empty($qty)) {
                $_LIMIT = [$start, $qty];
            }

            foreach ($this->sql_get('comments', '*, get_avatar(owner) AS avatar', $_WHERE, $_ORDER, $_LIMIT) as $data) {
                $return[] = $this->load($data, $approved_only);
            }

            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    /**
     * List subcomments
     * @param  integer $parent        parent id
     * @param  integer $approved_only get only approved rows
     * @param  integer $spam          get only non-spam rows
     * @param  string  $sort          sort by element
     * @param  string  $dir           direction to sort
     * @return array
     */
    public function list_sub_comments($parent = 0, $approved_only = 0, $spam = 0, $sort = 'date', $dir = 'DESC')
    {
        try {
            $_WHERE['parent'] = $parent;
            $_WHERE['spam'] = (!empty($spam)) ? 1 : 0;

            if (!empty($approved_only)) {
                $_WHERE['approved'] = 1;
                $_WHERE['lang'] = $_SESSION['lang'];
            }
            if (!empty($sort)) {
                $_ORDER = $sort." ".$dir;
            }
            if (!empty($qty)) {
                $_LIMIT = [$start, $qty];
            }

            if (!empty($sort)) {
                $_ORDER = $sort." ".$dir;
            }
            if (!empty($qty)) {
                $_LIMIT = [$start, $qty];
            }

            foreach ($this->sql_get('comments', '*, get_avatar(owner) AS avatar', $_WHERE, $_ORDER, $_LIMIT) as $data) {
                $return[] = $this->load($data, $approved_only);
            }
            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    /**
     * Count
     * @param  integer $parent        parent id
     * @param  integer $approved_only get only approved rows
     * @return integer
     */
    public function count($parent = 0, $approved_only = 0)
    {
        try {
            $_WHERE['plugin'] = 'blog';
            
            if (!empty($parent)) {
                $_WHERE['parent'] = $parent;
            }
            if (!empty($approved_only)) {
                $_WHERE['approved'] = 1;
                $_WHERE['lang'] = $_SESSION['lang'];
            }

            $data = $this->sql_fetchone($sqlQuery);
            return $this->sql_count('comments', $_WHERE);
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    /**
     * Count reviews
     * @param  integer $parent        parent id
     * @param  integer $approved_only get only approved rows
     * @return integer
     */
    public function count_reviews($parent = 0, $approved_only = 0)
    {
        try {
            $_WHERE['plugin'] = 'products';
            
            if (!empty($parent)) {
                $_WHERE['parent'] = $parent;
            }
            if (!empty($approved_only)) {
                $_WHERE['approved'] = 1;
                $_WHERE['lang'] = $_SESSION['lang'];
            }

            $data = $this->sql_fetchone($sqlQuery);

            return $this->sql_count('comments', $_WHERE);
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    /**
     * Get comment info based on id
     * @param  integer  $id     row id
     * @return array
     */
    public function get_items_info($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->load($this->sql_get_one('comments', '*', $id));
        }
        
        return false;
    }
    /**
     * Reload comment info in cache
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
     * Get product information
     * @param  integer $id row id
     * @return array
     */
    public function get_product_info($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->sql_get_one('items', 'id, title, url, get_products_image(id) AS image', $id);
        }

        return false;
    }
    /**
     * Get blog information
     * @param  integer $id row id
     * @return array
     */
    public function get_blog_info($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->sql_get_one('blog', 'id, title, url', $id);
        }

        return false;
    }
    /**
     * Get comments from user
     * @param  integer $owner         user id
     * @param  integer $approved_only get only approved rows
     * @return array
     */
    public function get_from_owner($owner = 0, $approved_only = 0)
    {
        $owner = (int) $owner;
        $approved_only = (int) $$approved_only;

        if (!empty($owner)) {
            $_WHERE['owner'] = $owner;

            if (!empty($approved_only)) {
                $_WHERE['approved'] = 1;
            }

            foreach ($this->sql_get('comments', '*', $_WHERE, 'date DESC') as $data) {
                $return[] = $this->load($data, $approved_only);
            }
            
            return $return;
        }
        
        return false;
    }
    /**
     * Search comments
     * @param  [type]  $query         phrase / word to search
     * @param  integer $start         start from
     * @param  integer $qty           quantity to be returned
     * @param  integer $parent        parent
     * @param  integer $approved_only get only approved rows
     * @param  string  $sort          sort by element
     * @param  string  $dir           direction to sort
     * @return array
     */
    public function search($query, $start = 0, $qty = 10, $parent = 0, $approved_only = 0, $sort = 'date', $dir = 'DESC')
    {
        $sqlQuery = "
            SELECT *
            FROM ".$this->prefix."_comments
            WHERE plugin = 'blog' 
        ";

        $_arguments = ['email', 'name', 'text', 'ip'];

        if (!empty($query)) {
            $sqlQuery .= " 
                AND (".implode(" LIKE '%".$query."%' OR ", $_arguments)."  LIKE '%".$query."%')
            ";
        }

        if (!empty($parent)) {
            $sqlQuery .= "
                AND parent = '".$parent."' 
            ";
        }

        if (!empty($approved_only)) {
            $sqlQuery .= "
                AND approved = '1' 
                AND lang = '".$_SESSION['lang']."'
            ";
        }

        if (!empty($sort)) {
            $sqlQuery .= "
                ORDER BY ".$sort." ".$dir."
            ";
        }

        if (!empty($qty)) {
            $sqlQuery .= "
                LIMIT ".$start." , ".$qty."
            ";
        }

        foreach ($this->sql_fetchrow($sqlQuery) as $data) {
            $return[] = $this->load($data);
        }

        return $return;
    }
    /**
     * Search reviews
     * @param  [type]  $query         phrase / word to search
     * @param  integer $start         start from
     * @param  integer $qty           quantity to be returned
     * @param  integer $parent        parent
     * @param  integer $approved_only get only approved rows
     * @param  string  $sort          sort by element
     * @param  string  $dir           direction to sort
     * @return array
     */
    public function search_reviews($query, $start = 0, $qty = 10, $parent = 0, $approved_only = 0, $sort = 'date', $dir = 'DESC')
    {
        try {
            $sqlQuery = "
                SELECT *
                FROM ".$this->prefix."_comments
                WHERE plugin = 'products' 
            ";

            $_arguments = ['email', 'name', 'text', 'ip'];

            if (!empty($query)) {
                $sqlQuery .= " 
                    AND (".implode(" LIKE '%".$query."%' OR ", $_arguments)."  LIKE '%".$query."%')
                ";
            }

            if (!empty($parent)) {
                $sqlQuery .= "
                    AND parent = '".$parent."' 
                ";
            }
            if (!empty($approved_only)) {
                $sqlQuery .= "
                    AND approved = '1' 
                    AND lang = '".$_SESSION['lang']."'
                ";
            }
            if (!empty($sort)) {
                $sqlQuery .= "
                    ORDER BY ".$sort." ".$dir."
                ";
            }
            if (!empty($qty)) {
                $sqlQuery .= "
                    LIMIT ".$start." , ".$qty."
                ";
            }

            foreach ($this->sql_fetchrow($sqlQuery) as $data) {
                $return[] = $this->load($data);
            }

            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    /**
     * Save comment
     * @param  array  $data data content to be saved
     * @return integer
     */
    public function save(array $data)
    {
        if (!empty($data)) {
            $id = $this->sql_insert_empty('comments');
            unset($data['id']);
            
            $data['date'] = date('Y-m-d H:i:s');
            $data['ip'] = $_SERVER['REMOTE_ADDR'];
            $data['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
            $data['lang'] = $_SESSION['lang'];

            $return = $this->sql_update('comments', $data, (int) $id);
            $this->reload_cache_item((int) $id);

            return $return;
        }
        
        return false;
    }
    /**
     * Alias for save
     */
    public function update(array $data)
    {
        $this->save($data);
    }
    /**
     * Alias for save
     */
    public function insert(array $data)
    {
        $this->save($data);
    }
    /**
     * Approve comment
     * @param  integer $id comment id
     * @return boolean
     */
    public function approve($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $item_info = $this->get_items_info($id);
            $action = (!empty($item_info['approved']) ? 0 : 1);
            $this->sql_update('comments', ['text' => str_replace('[POSSIBLE SPAM] ', '', $item_info['text']), 'approved' => $action], $id);
            $this->reload_cache_item($id);

            return true;
        }
        
        return false;
    }
    /**
     * Mark comment as spam
     * @param  integer $id comment id
     * @return boolean
     */
    public function spam($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $this->sql_set('comments', $id, 'spam');
            $this->reload_cache_item($id);

            return true;
        }
        
        return false;
    }
    /**
     * Collect visitors votes
     * @param  integer $id   comment id
     * @param  string  $vote vote type
     * @return boolean
     */
    public function vote($id = 0, $vote = '')
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->sql_update('comments', ['votes' => $vote], $id);
        }
        
        return false;
    }
    /**
     * Delete comment
     * @param  integer $id  comment id
     * @return [type]      [description]
     */
    public function delete($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $this->sql_delete('comments', $id);
            parent::cache()->delete('_cache_'.$this->plugin.'_'.$id);
        }

        return false;
    }
    /**
     * Load and format comment information
     * @param  array  $data     data to be formatted
     * @param mixed $approved_only
     * @return array
     */
    private function load(array $data = [], $approved_only = 0)
    {
        try {
            if (count($data) == 0) {
                throw new Exception('Submitted data is empty', 1);
            }

            $return = parent::cache()->get('_cache_'.$this->plugin.'_'.$data['id']);

            if ($return == null) {
                $return = $this->filter($data, 1);
                $return['text'] = nl2br($data['text']);
                $return['plain_text'] = $data['text'];

                if (!empty($data['parent'])) {
                    $return['item'] = ($data['plugin'] == 'products') ? $this->get_product_info($data['parent']) : $this->get_blog_info($data['parent']);
                }

                $return['sub_comments'] = $this->list_sub_comments($data['plugin'], $data['id'], $approved_only);
                
                if (!empty($return)) {
                    parent::cache()->set('_cache_'.$this->plugin.'_'.$data['id'], $return);
                }
            }

            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
}
