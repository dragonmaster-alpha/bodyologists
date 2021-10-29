<?php

namespace Plugins\Pages\Classes;

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

class Pages extends Format
{
    private $plugin;
    
    public function __construct()
    {
        $this->plugin = 'pages';
    }

    public function list_items($start = 0, $qty = 0, $active_only = 0, $parent = 0, $in_menu = 0, $only_members = 0, $sort = 'ordering', $dir = 'ASC')
    {
        $_WHERE = [];

        if (!empty($parent)) {
            $_WHERE['parent'] = $parent;
        }
        if (!empty($in_menu)) {
            $_WHERE['in_menu'] = 1;
        }
        if (!empty($only_members)) {
            $_WHERE['only_members'] = 1;
        }
        if (!empty($active_only)) {
            $_WHERE['active'] = 1;
            $_WHERE['lang'] = $_SESSION['lang'];
        }

        if (!empty($sort)) {
            $_ORDER = $sort.' '.$dir;
        }

        if (!empty($qty)) {
            $_LIMIT = [(int) $start, (int) $qty];
        }

        foreach ($this->sql_get('pages', '*', $_WHERE, $_ORDER, $_LIMIT) as $data) {
            $return[] = $this->load($data);
        }
        
        return $return;
    }
    
    public function count($active_only = 0, $parent = 0)
    {
        $_WHERE = [];
        
        if (!empty($active_only)) {
            $_WHERE['active'] = 1;
        }

        if (!empty($parent)) {
            $_WHERE['parent'] = (int) $parent;
        }
        
        return $this->count('pages', $_WHERE);
    }

    public function get_items_info($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->load($this->sql_get_one('pages', '*', $id));
        }
        
        return false;
    }

    public function get_items_from_url($url = '')
    {
        if ($url === 'favicon.ico') {
            return false;
        }

        $url = $this->filter($url, 1, 1);

        if (!empty($url)) {
            $data = $this->sql_get_one('pages', 'id', ['page_type' => 1, 'url' => $url, 'active' => 1]);

            if (!empty($data['id'])) {
                return $this->get_items_info((int) $data['id']);
            }
        }

        return false;
    }

    public function reload_cache_item($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            parent::cache()->delete('_PAGES_'.$id);
            $this->get_items_info($id);
            return true;
        }

        return false;
    }

    public function get_index()
    {
        try {
            return $this->load($this->sql_get_one('pages', '*', "page_type = '1' AND url LIKE 'index' AND lang='".$_SESSION['lang']."'"));
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    
    public function reorder(array $items)
    {
        foreach ($items as $ordering => $id) {
            $id = (int) $id;
            $this->sql_update('pages', ['ordering' => $ordering], $id);
            parent::cache()->delete('_PAGES_'.$id);
        }
    }
    
    public function check_url($id = 0, $url = '')
    {
        $_WHERE = [];

        $_WHERE['url'] = $url;

        if (!empty($id)) {
            $_WHERE['id'] = $id;
        }

        return $this->sql_count('pages', $_WHERE);
    }
    
    public function get_menu()
    {
        foreach ($this->sql_get('pages', 'id, parent AS parent_id, name, url', ['active' => 1, 'in_menu' => 1, 'lang' => $_SESSION['lang']], 'ordering ASC') as $data) {
            $return[] = $this->filter($data, 1);
        }

        return $return;
    }

    public function get_page_by_url($url = '')
    {
        if (!empty($url) && $this->check_url($url)) {
            $data = $this->sql_get_one('pages', '*', ['url' => $url, 'active' => 1]);
            
            if (!empty($data)) {
                return $data;
            }
        }
        
        return false;
    }

    public function update_visits($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $this->sql_sum('pages', ['visits' => 1], $id);
        }
        
        return false;
    }

    public function search($query = '', $start = 0, $qty = 0, $active_only = 0, $sort = 'ordering', $dir = 'ASC')
    {
        try {
            $_WHERE = "
                id != '0'
            ";

            if (!empty($query)) {
                $search_arr = explode(' ', $query);
                $_WHERE .= "
                    AND (
                ";
                $_WHERE .= "
                    name LIKE '%".implode("%' OR name LIKE '%", $search_arr)."%'
                ";
                $_WHERE .= " 
                    OR 
                ";
                $_WHERE .= "
                    title LIKE '%".implode("%' OR title LIKE '%", $search_arr)."%'
                ";
                $_WHERE .= " 
                    OR 
                ";
                $_WHERE .= "
                    meta_keywords LIKE '%".implode("%' OR meta_keywords LIKE '%", $search_arr)."%'
                ";
                $_WHERE .= " 
                    OR 
                ";
                $_WHERE .= "
                    meta_description LIKE '%".implode("%' OR meta_description LIKE '%", $search_arr)."%'
                ";
                $_WHERE .= " 
                    OR 
                ";
                $_WHERE .= "
                    text LIKE '%".implode("%' OR text LIKE '%", $search_arr)."%'
                ";
                $_WHERE .= " 
                    )
                ";
            }

            if (!empty($active_only)) {
                $_WHERE .= "
                    AND active = '1' 
                    AND lang = '".$_SESSION['lang']."'
                ";
            }
            if (!empty($sort)) {
                $_ORDER = $sort.' '.$dir;
            }
            if (!empty($qty)) {
                $_LIMIT = [(int) $start, (int) $qty];
            }
            
            foreach ($this->sql_get('pages', '*', $_WHERE, $_ORDER, $_LIMIT) as $data) {
                $return[] = $data = $this->load($data);
            }
            
            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function search_count($query = '', $active_only = 0)
    {
        try {
            $_WHERE = "
                id != '0'
            ";

            if (!empty($query)) {
                $search_arr = explode(' ', $query);

                $_WHERE .= "
                    AND (
                ";
                $_WHERE .= "
                    name LIKE '%".implode("%' OR name LIKE '%", $search_arr)."%'
                ";
                $_WHERE .= " 
                    OR 
                ";
                $_WHERE .= "
                    title LIKE '%".implode("%' OR title LIKE '%", $search_arr)."%'
                ";
                $_WHERE .= " 
                    OR 
                ";
                $_WHERE .= "
                    meta_keywords LIKE '%".implode("%' OR meta_keywords LIKE '%", $search_arr)."%'
                ";
                $_WHERE .= " 
                    OR 
                ";
                $_WHERE .= "
                    meta_description LIKE '%".implode("%' OR meta_description LIKE '%", $search_arr)."%'
                ";
                $_WHERE .= " 
                    OR 
                ";
                $_WHERE .= "
                    text LIKE '%".implode("%' OR text LIKE '%", $search_arr)."%'
                ";
                $_WHERE .= " 
                    )
                ";
            }
            
            if (!empty($active_only)) {
                $_WHERE .= "
                    AND active = '1' 
                    AND lang = '".$_SESSION['lang']."'
                ";
            }

            return $this->sql_count('pages', $_WHERE);
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function manage_activation($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $item_info = $this->get_items_info($id);
            $this->sql_activate('pages', $id);
            $this->reload_cache_item($id);
            
            return $item_info['title'];
        }
        
        return false;
    }

    public function manage_members_only($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $item_info = $this->get_items_info($id);
            $this->sql_set('pages', $id, 'only_members');
            $this->reload_cache_item($id);

            return $item_info['title'];
        }

        return false;
    }

    public function manage_in_menu($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $item_info = $this->get_items_info($id);
            $this->sql_set('pages', $id, 'in_menu');
            $this->reload_cache_item($id);

            return $item_info['title'];
        }
        
        return false;
    }

    public function save(array $data)
    {
        if (!empty($data)) {
            if (!empty($data['id'])) {
                $id = $data['id'];
            } else {
                $id = $this->sql_insert_empty('pages');
                $data['ordering'] = $this->sql_count('pages');
                $data['date'] = date('Y-m-d H:i:s');
            }

            if ($data['page_type'] == 1) {
                $data['url'] = (!empty($data['url'])             ? $this->gen_url($data['url']) : $this->gen_url($data['id'].'-'.$data['name']));
            }

            $data['active'] = (!empty($data['active'])          ? 1 : 2);
            $data['in_menu'] = (!empty($data['in_menu'])         ? 1 : 0);
            $data['only_members'] = (!empty($data['only_members'])    ? 1 : 0);
            
            unset($data['id']);

            $this->sql_update('pages', $data, (int) $id);
            $this->reload_cache_item((int) $id);

            return $id;
        }
        
        return false;
    }

    public function save_content(array $data)
    {
        if (!empty($data)) {
            $id = (int) $data['id'];
            $data['text'] = $this->filter($data['text']);

            $this->sql_update('pages', $data, $id);
            $this->reload_cache_item($id);

            return $id;
        }
        
        return false;
    }

    public function delete($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $this->sql_delete('pages', $id);
            parent::cache()->delete('_PAGES_'.$id);
        }
        
        return false;
    }

    /* DELETE THIS AND YOU ARE STUPID ! */
    public function get_sorting_pages()
    {
        foreach ($this->sql_get('pages', ['id', 'parent AS parent_id', 'name', 'url'], ['active' => 1, 'in_menu' => 1, 'lang' => $_SESSION['lang']], 'ordering ASC') as $data) {
            $return[] = $this->filter($data, 1);
        }

        return $return;
    }

    private function load(array $data)
    {
        if (!empty($data)) {
            $return = parent::cache()->get('_PAGES_'.(int) $data['id']);

            if ($return == null) {
                $return = $this->filter($data, 1);
                $return['text'] = $this->filter($data['text']);
                $return['media'] = $this->get_images_from_html($return['text']);
                $return['target'] = (stristr($data['url'], 'http://') || stristr($data['url'], 'https://')) ? '_blank' : '_self';

                if (!empty($return)) {
                    parent::cache()->set('_PAGES_'.$data['id'], $return);
                }
            }

            return $return;
        }
        
        return false;
    }
}
