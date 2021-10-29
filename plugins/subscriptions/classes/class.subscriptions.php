<?php

namespace Plugins\Subscriptions\Classes;

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

class Subscriptions extends Format
{
    private $plugin;

    public function __construct()
    {
        $this->plugin = 'subscriptions';
    }

    public function list_items($start = 0, $qty = 0, $sort = 'date', $dir = 'ASC')
    {
        foreach ($this->sql_get('subscriptions', '*', "id != '0'", $sort." ".$dir, [$start, $qty]) as $data) {
            $return[] = $this->load($data);
        }

        return $return;
    }

    public function count($active_only = 0)
    {
        $active_only = (int) $active_only;
        $_WHERE = [];

        if (!empty($active_only)) {
            $_WHERE['active'] = 1;
        }

        return $this->sql_count('subscriptions', $_WHERE);
    }

    public function get_items_info($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->load($this->sql_get_one('subscriptions', '*', $id));
        }

        return false;
    }

    public function search($query, $start = 0, $qty = 0, $sort = 'date', $dir = 'DESC')
    {
        try {
            $sqlQuery = "
                SELECT *
                FROM ".$this->prefix."_subscriptions
                WHERE id != '0' 
            ";

            $_arguments = [
                'name',
                'email',
                'lang'
            ];

            if (!empty($query)) {
                $sqlQuery .= " 
                    AND (".implode(" LIKE '%".$query."%' OR ", $_arguments)."  LIKE '%".$query."%')
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

    public function get_categories()
    {
        foreach ($this->sql_get('subscriptions', 'DISTINCT(category)', 0, 'category ASC') as $data) {
            $return[] = $this->filter($data['category'], 1);
        }

        return $return;
    }

    public function get_id_from_email($email)
    {
        $email = $this->check_email($this->filter($email, 1, 1));

        if (!empty($email)) {
            return $this->sql_get_one('subscriptions', 'id', ['email' => $email])['id'];
        }
        
        return false;
    }

    public function get_info_from_email()
    {
        return $this->sql_count('subscriptions', ['email' => $email]);
    }

    # Management
    public function manage_activation($id = 0)
    {
        $item_info = $this->get_items_info($id);
        $this->sql_activate('subscriptions', (int) $id);
        return $item_info['title'];
    }

    public function save(array $data)
    {
        $data = $this->filter($data, 1, 1);

        if (!empty($data)) {
            if (!empty($data['id'])) {
                $id = $data['id'];
                unset($data['id']);
            } else {
                $id = $this->sql_insert_empty('subscriptions');
                $data['date'] = date('Y-m-d H:i:s');
            }

            $this->sql_update('subscriptions', $data, (int) $id);
            return (int) $id;
        }

        return false;
    }

    public function delete($id)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $this->sql_delete('subscriptions', (int) $id);
        }
        
        return false;
    }

    public function export()
    {
        try {
            $sqlQuery = "
                SELECT *
                FROM ".$this->prefix."_items
                WHERE alive = '0'
                ORDER BY id ASC
            ";

            $counter = 0;
            $csv = [];
            
            $csv[$counter][] = 'ID';
            $csv[$counter][] = 'Category';
            $csv[$counter][] = 'Name';
            $csv[$counter][] = 'Email';
            $csv[$counter][] = 'Subcription Date';
            $csv[$counter][] = 'Language';
            $counter++;
        
            foreach ($this->sql_get('subscriptions', '*', "id != '0'", 'date DESC') as $data) {
                $csv[$counter][] = $data['id'];
                $csv[$counter][] = $data['category'];
                $csv[$counter][] = $data['name'];
                $csv[$counter][] = $data['email'];
                $csv[$counter][] = $data['date'];
                $csv[$counter][] = $data['lang'];
                $counter++;
            }
            return $csv;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    private function load(array $data = [])
    {
        try {
            if (count($data) == 0) {
                throw new Exception('Submitted data is empty', 1);
            }

            $return = $this->filter($data, 1);

            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
}
