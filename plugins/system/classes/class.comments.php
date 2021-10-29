<?php

namespace Plugins\System\Classes;

use Kernel\Classes\Format as Format;

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
    private $avoid;

    public function __construct()
    {
        $this->avoid = [
            'id',
            'counter',
            'save_and',
            'vote',
            'op'
        ];
    }

    public function list_items($start = 0, $qty = 0, $plugin = '', $parent = 0, $approved_only = 0, $sort = 'date', $dir = 'DESC')
    {
        try {
            $sqlQuery = "
                SELECT *
                FROM ".$this->prefix."_comments
                WHERE id != '0' 
            ";
            if (!empty($plugin)) {
                $sqlQuery .= "
                    AND plugin = '".$plugin."' 
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
                    AND lang = '".$this->language."'
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
        
            foreach ($this->sql_fetchrow($sqlQuery) as $infoRows) {
                $return[] = $this->load($infoRows, $approved_only);
            }
            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function list_sub_comments($plugin = '', $in_response = 0, $approved_only = 0, $sort = 'date', $dir = 'DESC')
    {
        try {
            $sqlQuery = "
                SELECT *
                FROM ".$this->prefix."_comments
                WHERE id != '0' 
            ";
            if (!empty($plugin)) {
                $sqlQuery .= "
                    AND plugin = '".$plugin."' 
                ";
            }
            if (!empty($in_response)) {
                $sqlQuery .= "
                    AND parent = '".$in_response."' 
                ";
            }
            if (!empty($approved_only)) {
                $sqlQuery .= "
                    AND approved = '1' 
                    AND lang = '".$this->currentlang."'
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

            foreach ($this->sql_fetchrow($sqlQuery) as $infoRows) {
                $return[] = $this->load($infoRows, $approved_only);
            }
            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function count($plugin = '', $parent = 0, $approved_only = 0)
    {
        try {
            $sqlQuery = "
                SELECT COUNT(*) as CNT 
                FROM ".$this->prefix."_comments
                WHERE id != '0'
            ";
            if (!empty($plugin)) {
                $sqlQuery .= "
                    AND plugin = '".$plugin."' 
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
                    AND lang = '".$this->currentlang."'
                ";
            }

            $infoRows = $this->sql_fetchone($sqlQuery);
            return $infoRows['CNT'];
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function get_items_info($id = 0)
    {
        try {
            if (empty($id)) {
                throw new Exception('You must provide an item to proceed', 1);
            }

            $sqlQuery = "
                SELECT *
                FROM ".$this->prefix."_comments
                WHERE id = '".$id."'
            ";

            return $this->load($this->sql_fetchone($sqlQuery));
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function get_from_owner($owner = 0, $approved_only = 0)
    {
        try {
            if (empty($owner)) {
                throw new Exception('You must provide an item to proceed', 1);
            }

            $sqlQuery = "
                SELECT *
                FROM ".$this->prefix."_comments
                WHERE owner = '".$owner."'
            ";
            if (!empty($approved_only)) {
                $sqlQuery .= "
                    AND approved = '1' 
                ";
            }
            $sqlQuery .= "
                ORDER BY date DESC
            ";

            return $this->load($this->sql_fetchone($sqlQuery));
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function search($query = '', $start = 0, $qty = 0, $plugin = '', $parent = 0, $approved_only = 0, $sort = 'date', $dir = 'DESC')
    {
        try {
            $sqlQuery = "
                SELECT *
                FROM ".$this->prefix."_comments
                WHERE id != '0' 
                AND (
                    email LIKE '%".$query."%' OR
                    name LIKE '%".$query."%' OR
                    text LIKE '%".$query."%' OR
                    ip LIKE '%".$query."%'
                )
            ";
            if (!empty($plugin)) {
                $sqlQuery .= "
                    AND plugin = '".$plugin."' 
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
                    AND lang = '".$this->currentlang."'
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
            
            foreach ($this->sql_fetchrow($sqlQuery) as $infoRows) {
                $return[] = $this->load($infoRows);
            }
            $this->sql_freeresult($collectInfo);
            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    // Comments Management
    public function update(array $data)
    {
        try {
            foreach ($data as $key => $value) {
                if (!$this->str_search($key, $this->avoid)) {
                    $fields[] = $key." = '".$value."'";
                }
            }

            $fields = implode(",", $fields);

            $sqlQuery = "
                UPDATE ".$this->prefix."_comments
                SET ".$fields."
                WHERE id = '".$data['id']."'
            ";

            echo '<pre>';
            print_r($sqlQuery);
            echo '</pre>';
            die;
            
            $this->sql_query($sqlQuery);

            return $data['id'];
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            return false;
        }
    }

    public function vote($id = 0, $vote = '')
    {
        try {
            $sqlQuery = "
                UPDATE ".$this->prefix."_comments
                SET votes = votes ".$vote."
                WHERE id = '".$id."'
            ";

            $this->sql_query($sqlQuery);

            return $id;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            return false;
        }
    }

    public function insert(array $data)
    {
        try {
            $fields = $values = [];

            # Set created on date
            $data['ip'] = $_SERVER['REMOTE_ADDR'];
            $data['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
            $data['date'] = date('Y-m-d H:i:s', time());
            $data['lang'] = $this->language;

            foreach ($data as $key => $value) {
                if (!$this->str_search($key, $this->avoid)) {
                    $fields[] = $key;
                    $values[] = "'".$value."'";
                }
            }
            
            $fields = implode(", ", $fields);
            $values = implode(", ", $values);
            
            $sqlQuery = "
                INSERT INTO ".$this->prefix."_comments
                (".$fields.") VALUES (".$values.")
            ";

            $this->sql_query($sqlQuery);

            return $this->sql_nextid();
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function approve($id = 0)
    {
        try {
            $item_info = $this->get_items_info($id);

            $sqlQuery = "
                UPDATE ".$this->prefix."_comments
                SET 
                    text = '".str_replace('[POSSIBLE SPAM] ', '', $item_info['text'])."',
                    approved = '1'
                WHERE id = '".$id."'
            ";

            $this->sql_query($sqlQuery);
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function delete($id = 0)
    {
        try {
            $sqlQuery = "
                DELETE 
                FROM ".$this->prefix."_comments
                WHERE id = '".$id."'
            ";
            $this->sql_query($sqlQuery);
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    private function load(array $data = [], $approved_only = 0)
    {
        try {
            if (count($data) == 0) {
                throw new Exception('Submitted data is empty', 1);
            }

            $return = $this->filter($data, 1);
            $return['text'] = nl2br($data['text']);
            $return['plain_text'] = $data['text'];
            //$return['sub_comments']                         = $this->list_sub_comments($data['plugin'], $data['id'], $approved_only);

            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
}
