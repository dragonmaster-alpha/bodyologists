<?php

namespace Plugins\Members\Addons\Packages\Classes;

use \Plugins\Affiliates\Classes\Affiliates;
use \Plugins\Members\Classes\Members;
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

class Packages extends Format
{
    private $plugin;
    private $addon;
    
    public function __construct()
    {
        $this->plugin = 'members';
        $this->addon = 'packages';
    }
    /**
     * List packages
     * @param  integer  $start          start from
     * @param  integer  $qty            quantity to be returned
     * @param  string   $active_only    get only active rows
     * @param  string   $sort           sort by element
     * @param  string   $dir            direction to sort
     * @return array
     */
    public function list_items($start = 0, $qty = 0, $active_only = 0, $sort = 'date', $dir = 'DESC')
    {
        try {
            if (!empty($active_only)) {
                $_WHERE['active'] = 1;
            }

            if (!empty($sort)) {
                $_ORDER = $sort.' '.$dir;
            }
            
            if (!empty($qty)) {
                $_LIMIT = [(int) $start, (int) $qty];
            }

            foreach ($this->sql_get('customers_packages', "*, users_in_plan(id) AS customers_in_plan", $_WHERE, $_ORDER, $_LIMIT) as $data) {
                $return[] = $this->load($data);
            }
        
            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    /**
     * Count
     * @param  integer $active_only get only active rows
     * @return integer
     */
    public function count($active_only = 0)
    {
        if (!empty($active_only)) {
            $_WHERE['active'] = 1;
        }

        return $this->sql_count('customers_packages', $_WHERE);
    }
    /**
     * Get package info based on id
     * @param  integer  $id     row id
     * @return array
     */
    public function get_items_info($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->load($this->sql_get_one('customers_packages', "*, users_in_plan(id) AS customers_in_plan", $id));
        }
        
        return false;
    }
    /**
     * Get packages categories
     * @return array
     */
    public function get_categories()
    {
        foreach ($this->sql_get('customers_packages', 'DISTINCT(category) AS category', '', 'category ASC') as $data) {
            $return[] = $this->filter($data['category'], 1);
        }

        return $return;
    }
    /**
     * update last time sold
     * @param  integer $id      row id
     * @return boolean
     */
    public function get_updated($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->sql_update('customers_packages', ['last_sold' => date('Y-m-d H:i:s'), 'sold' => 'sold+1'], $id);
        }
        
        return false;
    }
    /**
     * Search packages
     * @param  string  $query query to search by
     * @param  integer  $start          start from
     * @param  integer  $qty            quantity to be returned
     * @param  string   $sort           sort by element
     * @param  string   $dir            direction to sort
     * @return array
     */
    public function search($query = '', $start = 0, $qty = 0, $sort = 'date', $dir = 'DESC')
    {
        $query = $this->filter($query, 1, 1);

        if (!empty($query)) {
            $sql = "
                SELECT *
                FROM ".$this->prefix."_customers_packages
                WHERE id != '0'
            ";

            $_arguments = [
                'category',
                'name',
                'description'
            ];

            if (!empty($query)) {
                $sql .= " 
                    AND (".implode(" LIKE '%".$query."%' OR ", $_arguments)."  LIKE '%".$query."%')
                ";
            }
            if (!empty($sort)) {
                $sql .= "
                    ORDER BY ".$sort." ".$dir." 
                ";
            }
            if ($start > 0 || !empty($qty)) {
                $sql .= "
                    LIMIT ".$start.", ".$qty." 
                ";
            }

            foreach ($this->sql_fetchrow($sql) as $data) {
                $return[] = $this->load($data);
            }

            return $return;
        }

        return false;
    }
    /**
     * Get package image
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function get_images($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $data = $this->sql_get_one('media', 'image', ['belongs' => $this->addon, 'bid' => md5((string) $id), 'media' => 'Image'], 'imageId ASC');
            return $this->filter($data['image'], 1);
        }
        
        return false;
    }
    /**
     * Manage package activation
     * @param  integer  $id id of the row to modify
     * @return string   package title
     */
    public function manage_activation($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $item_info = $this->get_items_info($id);
            $this->sql_activate('customers_packages', $id);
            return $item_info['title'];
        }
        
        return false;
    }

    /**
     * Save package
     * @param  array  $data data content to be saved
     * @return boolean
     */
    public function save(array $data = [])
    {
        if (!empty($data)) {
            if (!empty($data['id'])) {
                $id = $data['id'];
            } else {
                $id = $this->sql_insert_empty('customers_packages');
                $data['date'] = date('Y-m-d H:i:s');
            }
            
            if (!empty($data['price'])) {
                $data['price'] = $this->number($data['price']);
            }
            if (!empty($data['period'])) {
                $data['period'] = (int) $data['period'];
            }

            unset($data['id']);
            
            return $this->sql_update('customers_packages', $data, (int) $id);
        }

        return false;
    }
    /**
     * Delete package
     * @param  integer $id  package id
     * @return [type]      [description]
     */
    public function delete($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->sql_delete('customers_packages', $id);
        }
        
        return false;
    }
    /**
     * Get transaction information
     * @param  integer $id transaction id
     * @return array    transaction information
     */
    public function get_trans_info($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->sql_get_one('payments', '*', (int) $id);
        }

        return false;
    }
    /**
     * Save successful transaction information
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    public function save_payment(array $data = [])
    {
        try {
            if (!empty($data)) {
                $id = $data['id'];
                unset($data['id']);

                if ($data['payment_method'] == 'Credit Card') {
                    $pay_info['first_name'] = $data['first_name'];
                    $pay_info['last_name'] = $data['last_name'];
                    $pay_info['card_type'] = $data['cc_type'];
                    $pay_info['card_number'] = $this->format_cc_number($data['cc_number']);
                    $pay_info['card_date'] = $data['cc_month'].'/'.$data['cc_year'];
                    $pay_info['card_cvv'] = $data['cc_cvv'];
                } elseif ($data['payment_method'] == 'eCheck') {
                    $pay_info['first_name'] = $data['first_name'];
                    $pay_info['last_name'] = $data['last_name'];
                    $pay_info['account_name'] = $data['check_account_name'];
                    $pay_info['bank_name'] = $data['check_bank_name'];
                    $pay_info['routing_number'] = $data['check_routing_number'];
                    $pay_info['account_number'] = $data['check_account_number'];
                } else {
                    $pay_info['first_name'] = $data['first_name'];
                    $pay_info['last_name'] = $data['last_name'];
                }

                $data['payment_info'] = json_encode($pay_info);
                $data['date'] = date('Y-m-d H:i:s');

                # Update user information
                $this->sql_update('customers', ['plan' => $data['plan'], 'next_payment' => $data['next_payment']], $data['owner']);
                $_SESSION['user_info']['plan'] = $data['plan'];
                $_SESSION['user_info']['next_payment'] = $data['next_payment'];

                $this->sql_update('payments', $data, (int) $id);
                
                # Create order barcode
                //$this->barcode($data['trans'], 'payments');

                # Handle affiliates / referrals
                if ($this->table_exists('affiliates')) {
                    $affiliates = new \Plugins\Affiliates\Classes\Affiliates;
                    $affiliates->referral_reward($data['trans'], $data['total'], $type = 1);
                }

                $members = new \Plugins\Members\Classes\Members;
                $members->reload_cache_item((int) $data['owner']);

                return $id;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    /**
     * Load and format information
     * @param  array  $data     data to be formatted
     * @return array
     */
    private function load(array $data = [])
    {
        try {
            if (count($data) == 0) {
                throw new Exception('Submitted data is empty', 1);
            }

            $return = $this->filter($data, 1);
            $return['price'] = $this->number($data['price']);
            $return['image'] = $this->get_images($data['id']);

            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
}
