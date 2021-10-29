<?php

namespace Plugins\System\Classes;

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

class Messages extends Format
{
    private $plugin;
    private $email;
    private $avoid;
    
    public function __construct($email)
    {
        $this->plugin = "system";
        $this->email = $email;
        $this->avoid = [
            'id',
            'plugin',
            'file',
            'op'
        ];
    }
    
    public function load(array $data = [])
    {
        try {
            $data['sender'] = $this->get_user_info_from_email($data['sent_from']);

            return $this->filter($data, 1);
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function list_items($type = '', $start = 0, $qty = 0, $sort = 'date', $dir = 'DESC')
    {
        try {
            $sqlQuery = "
				SELECT mess.*, (
					SELECT date 
					FROM ".$this->prefix."_messages
					WHERE parent = mess.id
					ORDER BY date DESC
					LIMIT 1
				) AS updated, (
					SELECT COUNT(*)
					FROM ".$this->prefix."_messages
					WHERE parent = mess.id
					AND readed = '1'
				) as CNT
				FROM ".$this->prefix."_messages mess
				WHERE mess.parent = '0'
				AND (
					mess.sent_from = '".$this->email."' OR
					mess.sent_to LIKE '%".$this->email."%'
				)
			";
            if (!empty($sort)) {
                $sqlQuery .= "
					ORDER BY mess.".$sort." ".$dir."
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
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    
    public function count($type = '')
    {
        try {
            $sqlQuery = "
                SELECT COUNT(*) as CNT 
                FROM ".$this->prefix."_messages
                WHERE id != ''
			";
            if (!empty($type)) {
                $sqlQuery .= "
					AND sent_from = '".$this->email."'
				";
            } else {
                $sqlQuery .= "
					AND sent_to LIKE '%".$this->email."%'
				";
            }

            $infoRows = $this->sql_fetchone($sqlQuery);
            return $infoRows['CNT'];
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    
    public function get_items_info($id = '')
    {
        try {
            $sqlQuery = "
				SELECT *
				FROM ".$this->prefix."_messages
				WHERE id='".$id."'
			";

            return $this->load($this->sql_fetchone($sqlQuery));
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function get_tree($id)
    {
        try {
            $sqlQuery = "
				SELECT *
				FROM ".$this->prefix."_messages
				WHERE id = '".$id."' 
				OR parent='".$id."'
			";

            foreach ($this->sql_fetchrow($sqlQuery) as $data) {
                $return[] = $this->load($data);
            }
            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function get_user_info_from_email($email = '')
    {
        try {
            $sqlQuery = "
				SELECT aid, name, email
				FROM ".$this->prefix."_authors
				WHERE email = '".$email."'
			";

            $infoRows = $this->sql_fetchone($sqlQuery);
            return $this->filter($infoRows, 1);
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function get_authors()
    {
        try {
            $sqlQuery = "
                SELECT name, email
                FROM ".$this->prefix."_authors 
                WHERE aid != 'jlgarcia'
                AND aid != 'gus'
                ORDER BY name ASC
            ";
            foreach ($this->sql_fetchrow($sqlQuery) as $infoRows) {
                $return[] = $this->filter($infoRows, 1);
            }

            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    
    public function update($id)
    {
        try {
            $sqlQuery = "
				UPDATE ".$this->prefix."_messages
				SET readed = '0'
				WHERE id='".$id."'
				OR parent = '".$id."'
			";
            $this->sql_query($sqlQuery);
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
        
    public function insert(array $data)
    {
        try {
            if (is_array($data)) {
                $fields = $values = [];
            
                foreach ($data as $key => $value) {
                    if (!$this->str_search($key, $this->avoid)) {
                        $fields[] = $key;
                        $values[] = "'".$value."'";
                    }
                }
                
                $fields = implode(", ", $fields);
                $values = implode(", ", $values);
                
                $sqlQuery = "
		            INSERT INTO ".$this->prefix."_messages
	                (".$fields.") VALUES (".$values.")
		        ";
                
                $this->sql_query($sqlQuery);
                $id = $this->sql_nextid();
                return $id;
            }
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    
    public function delete($id)
    {
        try {
            $sqlQuery = "
	            DELETE 
	            FROM ".$this->prefix."_messages
	            WHERE id='$id'
	        ";
            $this->sql_query($sqlQuery);
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
}
