<?php

namespace Plugins\Pages\Addons\Gallery\Classes;

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

class Gallery extends Format
{
    private $plugin;
    private $addon;

    public function __construct()
    {
        $this->plugin = 'pages';
        $this->addon = 'gallery';
    }

    public function list_items($start = '0', $qty = '', $id = '', $belongs = 'gallery', $owner = '', $sort = 'imageId', $dir = 'ASC')
    {
        if (!empty($belongs)) {
            $_WHERE['belongs'] = $belongs;
        }
        if (!empty($id)) {
            $_WHERE['bid'] = md5((string) $id);
        }
        if (!empty($owner)) {
            $_WHERE['owner'] = $owner;
        }
        if (!empty($sort)) {
            $_ORDER = $sort.' '.$dir;
        }
        if (!empty($qty)) {
            $_LIMIT = [$start, $qty];
        }
        foreach ($this->sql_get('media', '*', $_WHERE, $_ORDER, $_LIMIT) as $data) {
            $return[] = $this->load($data, 1);
        }

        return $return;
    }

    public function get_item_info($id)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->filter($this->sql_get_one('media', '*', $id), 1);
        }
        
        return false;
    }
    
    public function reload_cache_item($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            parent::cache()->delete('_GALLERY_'.$id);
            $this->get_item_info($id);
            return true;
        }

        return false;
    }

    public function update_images($data)
    {
        if (!empty($data)) {
            if (!empty($data['id'])) {
                $id = $data['id'];
                unset($data['id']);
            }

            $this->sql_update('media', $data, (int) $id);
            $this->reload_cache_item($id);
            
            return $id;
        }
        
        return false;
    }
    
    public function delete_image($id)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $_image_info = $this->get_item_info($id);

            @unlink($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/uploads/'.$_image_info['belongs'].'/'.$_image_info['bid'].'/'.$_image_info['image']);
            @unlink($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/uploads/'.$_image_info['belongs'].'/'.$_image_info['bid'].'/thumb-'.$_image_info['image']);
            @unlink($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/uploads/'.$_image_info['belongs'].'/'.$_image_info['bid'].'/small-'.$_image_info['image']);
            
            parent::cache()->delete('_GALLERY_'.$id);
            return $this->sql_delete('media', $id);
        }
        
        return false;
    }
    
    public function reorder_images(array $items)
    {
        if (!empty($items)) {
            foreach ($items as $ordering => $id) {
                $this->sql_update('media', ['imageId' => $ordering], (int) $id);
                parent::cache()->delete('_GALLERY_'.$id);
            }
        }
        
        return false;
    }

    public function list_albums()
    {
        try {
            $sqlQuery = "
                SELECT a.id, a.name, (
                    SELECT COUNT(*)
                    FROM ".$this->prefix."_media
                    WHERE bid = MD5(a.id) 
                    AND belongs = 'gallery'
                ) AS count, (
                    SELECT MAX(image)
                    FROM ".$this->prefix."_media
                    WHERE bid = MD5(a.id) 
                    AND belongs = 'gallery'
                ) AS album_cover
                FROM ".$this->prefix."_media_albums a
                ORDER BY a.id DESC
            ";
                
            foreach ($this->sql_fetchrow($sqlQuery) as $data) {
                $return[] = $this->filter($data, 1);
            }

            return $return;
        } catch (PDOException $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    
    public function get_album_info($id)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->filter($this->sql_get_one('media_albums', '*', $id), 1);
        }
        
        return false;
    }
    
    public function insert_album($album_name)
    {
        $album_name = $this->filter($album_name, 1, 1);

        if (!empty($album_name)) {
            return $this->sql_insert('media_albums', ['media_albums' => 'Images', 'name' => $album_name]);
        }
        
        return false;
    }
    
    public function delete_album($id)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $this->sql_delete('media_albums', $id);
            $this->sql_delete('media', ['belongs' => 'gallery', 'bid' => md5((string) $id)]);
        }
        
        return false;
    }

    private function load(array $data = [])
    {
        if (!empty($data)) {
            $return = parent::cache()->get('_GALLERY_'.(int) $data['id']);

            if ($return == null) {
                $return = $this->filter($data);
                $return['meta_info'] = json_decode($data['meta_info'], 1);

                if (!empty($return)) {
                    parent::cache()->set('_GALLERY_'.$data['id'], $return);
                }
            }

            return $return;
        }

        return false;
    }
}
