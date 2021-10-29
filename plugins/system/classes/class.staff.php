<?php

namespace Plugins\System\Classes;

use App\Format as Format;
use App\Security\Encrypt as Encrypt;

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

class Staff extends Format
{
    public $plugin;
    
    public function __construct()
    {
        $this->plugin = "system";
    }
    
    public function load(array $data)
    {
        if (!empty($data)) {
            $return = $this->filter($data, 1);

            try {
                if ($return['aid'] == 'jlgarcia' || $return['aid'] == 'gus') {
                    throw new Exception('Selected staff does not exists.');
                }
            } catch (Exception $e) {
                die($e->getMessage());
            }

            $return['media'] = $this->get_images($data['aid']);
        
            return $return;
        }
    }
    
    public function list_items($start = 0, $qty = 0, $active = 0, $sort = 'date', $dir = 'ASC')
    {
        $sqlQuery = "
			SELECT * 
			FROM ".$this->prefix."_authors
			WHERE aid != 'jlgarcia'
			AND aid != 'gus'
		";

        if (!empty($active)) {
            $sqlQuery .= "
				AND active='1'
			";
        }
        if (!empty($sort)) {
            $sqlQuery .= "
				ORDER BY ".$sort." ".$dir."
			";
        }
        if (!empty($qty)) {
            $sqlQuery .= "
				LIMIT ".$start.", ".$qty."
			";
        }

        foreach ($this->sql_fetchrow($sqlQuery) as $data) {
            $return[] = $this->load($data);
        }

        return $return;
    }
    
    public function count($active)
    {
        $sqlQuery = "
            SELECT COUNT(*) as CNT 
            FROM ".$this->prefix."_authors
            WHERE aid != 'jlgarcia'
			AND aid != 'gus'
        ";

        if (!empty($active)) {
            $sqlQuery .= "
                AND active='1' 
            ";
        }

        $data = $this->sql_fetchone($sqlQuery);
        
        return $data['CNT'];
    }
    
    public function get_items_info($id = '')
    {
        try {
            $id = $this->filter($id, 1, 1);
                
            if (!empty($id)) {
                if ($id == 'jlgarcia' || $id == 'gus') {
                    throw new Exception('Selected staff does not exists.');
                }

                return $this->load($this->sql_get_one('authors', '*', ['aid' => $id]));
            }
            
            return false;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    
    public function exists($id = '')
    {
        $id = $this->filter($id, 1, 1);
        
        if (!empty($id)) {
            return $this->sql_count('authors', ['aid' => $id]);
        }
        
        return false;
    }

    public function manage_activation($id = '')
    {
        $id = $this->filter($id, 1, 1);
        
        if (!empty($id)) {
            return $this->sql_query("
            	UPDATE ".$this->prefix."_authors 
            	SET active = IF(active = 1, 2, 1) 
            	WHERE aid = '".$id."'
            ");
        }
        
        return false;
    }

    public function update(array $data)
    {
        try {
            if ($data['aid'] == 'jlgarcia' || $data['aid'] == 'gus') {
                throw new Exception('Selected staff does not exists.');
            }

            if (is_array($data)) {
                $data = $this->filter($data, 1, 1);
                
                $info['name'] = $data['name'];
                $info['email'] = $data['email'];
                $info['about'] = $data['about'];
                $info['radminsuper'] = $data['radminsuper'];

                if (!empty($data['active'])) {
                    $info['active'] = $data['active'];
                }

                if (!empty($data['pwd'])) {
                    $info['pwd'] = Encrypt::encryptPasswd($data['pwd']);
                }

                $this->sql_update('authors', $info, ['aid' => $data['aid']]);

                if (empty($data['radminsuper'])) {
                    $this->sql_query("
						UPDATE ".$this->prefix."_plugins
						SET admins = REPLACE(admins, '".$data['aid'].",', '')
					");

                    if (!empty($data['admin_plugins'])) {
                        foreach ($data['admin_plugins'] as $plugin_id) {
                            $this->sql_query("
								UPDATE ".$this->prefix."_plugins
								SET admins = CONCAT(admins, '".$data['aid'].",')
								WHERE id = '".(int) $plugin_id."'
							");
                        }
                    }
                } else {
                    $this->sql_query("
						UPDATE ".$this->prefix."_plugins
						SET admins = REPLACE(admins, '".$data['aid'].",', '')
					");
                }

                return true;
            }

            throw new Exception('Data should be an array.');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
        
    public function insert(array $data)
    {
        try {
            if ($data['aid'] == 'jlgarcia' || $data['aid'] == 'gus') {
                $data = [];
                throw new Exception('Selected staff does not exists.');
            }

            if (!empty($data)) {
                $data = $this->filter($data, 1, 1);
                
                $this->sql_insert('authors', [
                    'aid' => $data['aid'],
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'about' => $data['about'],
                    'pwd' => Encrypt::encryptPasswd($data['pwd']),
                    'radminsuper' => $data['radminsuper'],
                    'active' => (int) $data['active'],
                    'date' => date('Y-m-d H:i:s')
                ]);

                if (empty($data['radminsuper'])) {
                    if (!empty($data['admin_plugins'])) {
                        foreach ($data['admin_plugins'] as $plugin_id) {
                            $this->sql_update('plugins', ['admins' => "CONCAT(admins, '".$data['aid'].",')"], (int) $plugin_id);
                        }
                    }
                }

                return true;
            }
            throw new Exception('Data should be an array.');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    
    public function delete($id)
    {
        $id = $this->filter($id, 1, 1);
        
        if (!empty($id)) {
            $this->sql_delete('authors', ['aid' => $id]);
            $this->sql_query("
				UPDATE ".$this->prefix."_plugins
				SET admins = REPLACE(admins, '".$id.",', '')
			");
        }
        
        return $return;
    }
    
    // Images Handler
    public function get_images($id = '')
    {
        $id = $this->filter($id, 1, 1);
        
        if (!empty($id)) {
            return $this->sql_get_one('media', 'bid, image, imageId', ['belongs' => 'staff', 'bid' => md5((string) $id), 'media' => 'Image'], 'imageId ASC');
        }
        
        return false;
    }

    public function get_plugins()
    {
        foreach ($this->sql_get('plugins', 'id, name, admins', "name != 'system' ", 'name ASC') as $data) {
            $return[] = $this->filter($data, 1);
        }
        
        return $return;
    }
}
