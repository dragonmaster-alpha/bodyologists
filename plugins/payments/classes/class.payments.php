<?php

namespace Plugins\Payments\Classes;

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

class Payments extends Format
{
    private $plugin;
 
    public function __construct()
    {
        $this->plugin = 'payments';
    }

    public function list_items($start = 0, $qty = 0, $sort = 'date', $dir = 'DESC')
    {
        try {
            if (!empty($sort)) {
                $_ORDER = $sort.' '.$dir;
            }
            if (!empty($qty)) {
                $_LIMIT = [(int) $start, (int) $qty];
            }
    
            foreach ($this->sql_get('payments', '*', "trans != ''", $_ORDER, $_LIMIT) as $data) {
                $return[] = $this->load($data);
            }

            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    
    public function count()
    {
        return $this->sql_count('payments');
    }
    
    public function get_items_info($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->load($this->sql_get_one('payments', '*', $id));
        }
        
        return false;
    }
    /**
     * Reload order's info in cache
     * @param  integer  $id     user id
     * @return boolean
     */
    public function reload_cache_item($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            parent::cache()->delete('_cache_'.$this->plugin.'_'.$id);
            $this->get_items_info($id);
        }
    }
    /**
     * Search orders
     * @param  string   $query          phrase / word to search for
     * @param  integer  $start          start from
     * @param  integer  $qty            quantity to be returned
     * @param  string   $sort           sort by element
     * @param  string   $dir            direction to sort
     * @return array
     */
    public function search($query = '', $start = '0', $qty = 0, $sort = 'date', $dir = 'DESC')
    {
        $sqlQuery = "
            SELECT *
            FROM ".$this->prefix."_payments 
            WHERE id!='0'
        ";

        $_arguments = ['trans','merchand_trans_id','email','first_name','last_name','address','city','state','zip','country','phone'];

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
                LIMIT ".(int) $start.", ".(int) $qty." 
            ";
        }

        foreach ($this->sql_fetchrow($sqlQuery) as $infoRows) {
            $return[] = $this->load($infoRows);
        }
        return $return;
    }

    // Manage payments
    public function insert(array $data)
    {
        try {
            $data['date'] = date('Y-m-d H:i:s');

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

            if (!empty($data['extras'])) {
                $data['extras'] = json_encode($data['extras']);
            }

            $data['date'] = date('Y-m-d H:i:s');

            $this->sql_save('payments', $data);
            
            # Create order barcode
            //$this->barcode($data['trans'], 'payments');

            # Handle affiliates / referrals
            if ($this->table_exists('affiliates')) {
                $reffered = (!empty($_SESSION['referred']) ? (int) $_SESSION['referred'] : (int) $_SESSION['user_info']['referred']);

                if (!empty($reffered)) {
                    $reffered_info = $this->sql_get_one('affiliates', 'id, commission', ['code' => $reffered]);

                    if (!empty($reffered_info['commission'])) {
                        $referal['owner'] = (int) $reffered_info['id'];
                        $referal['order_no'] = $data['trans'];
                        $referal['amount'] = $this->calc_percent((int) $reffered_info['commission'], $data['total']);
                        $referal['description'] = 'Payment Commission';
                        $referal['status'] = 0;
                        $referal['date'] = date('Y-m-d H:i:s');

                        $this->sql_insert('affiliates_commissions', $referal);
                    }
                }
            }

            return $data['trans'];
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            return false;
        }
    }

    public function insert_empty()
    {
        return $this->sql_insert_empty('payments');
    }

    public function delete($id)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->sql_delete('payments', $id);
            
            # Delete cache
            parent::cache()->delete('_cache_'.$this->plugin.'_'.$id);
        }

        return false;
    }
    
    public function notes_format($notes = '', $id = 0, $author = '')
    {
        try {
            if (empty($notes)) {
                throw new Exception('You must enter the content of your notes to proceed.', 1);
            }

            $return = '';
            $id = (int) $id;

            if (!empty($id)) {
                $data = $this->sql_get_one('payments', 'notes', $id);
                $return .= $data['notes'];
            }
            if (!empty($notes)) {
                $return .= '
                    <li><div><b>From '.$author.' on '.date('M jS g:iA').'</b><br />'.$notes.'</div></li>
                ';
            }
            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function save_notes(array $data = [])
    {
        $id = (int) $data['id'];
        $notes = $this->filter($data['notes'], 1, 1);

        if (!empty($id) && !empty($notes)) {
            $notes = $this->notes_format($notes, $id, $data['author']);
            return $this->sql_update('payments', ['notes' => $notes], $id);
        }
        
        return false;
    }

    public function export()
    {
        try {
            $sqlQuery = "
                SELECT *
                FROM ".$this->prefix."_payments
                WHERE id > 0
                ORDER BY id DESC
            ";

            $counter = 0;
            $csv = [];
            $csv[$counter][] = 'Trans No';
            $csv[$counter][] = 'Name';
            $csv[$counter][] = 'Phone';
            $csv[$counter][] = 'Email';
            $csv[$counter][] = 'Payment Method';
            $csv[$counter][] = 'Payment Info';
            if (is_array($item['payment_info']) && count($item['payment_info']) > 0) {
                foreach ($item['payment_info'] as $payment_info_key => $payment_info_value) {
                    if (!empty($payment_info_value)) {
                        $csv[$counter][] = ''.ucwords(str_replace('_', ' ', $payment_info_key)).'';
                    }
                }
            }
            
            $csv[$counter][] = 'Tax';
            $csv[$counter][] = 'Total';
            $csv[$counter][] = 'Placed on';
            $csv[$counter][] = 'IP';

            $counter++;
            foreach ($this->sql_fetchrow($sqlQuery) as $data) {
                $payment_info_data = [];

                $csv[$counter][] = $data['trans'];
                $csv[$counter][] = $data['first_name'].' '.$data['last_name'];
                $csv[$counter][] = $data['phone'];
                $csv[$counter][] = $data['email'];
                $csv[$counter][] = $data['payment_method'];

                if (!empty($data['payment_info'])) {
                    $payment_info = json_decode($data['payment_info'], 1);

                    foreach ($payment_info as $payment_info_key => $payment_info_value) {
                        if (!empty($payment_info_value)) {
                            $payment_info_data[] = str_replace('_', ' ', $payment_info_key).': '.$payment_info_value;
                        }
                    }

                    $csv[$counter][] = @implode(', ', $payment_info_data);
                }
                $csv[$counter][] = '$'.$this->number($data['taxes']);
                $csv[$counter][] = '$'.$this->number($data['total']);
                $csv[$counter][] = $data['date'];
                $csv[$counter][] = $data['ip'];
                
                $counter++;
            }
            return $csv;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    /**
     * Load and format orders information
     * @param  array  $data data to be formatted
     * @return array
     */
    private function load(array $data = [])
    {
        if (!empty($data)) {
            if (count($data) == 0) {
                throw new Exception('Submitted data is empty', 1);
            }

            $return = parent::cache()->get('_cache_'.$this->plugin.'_'.$data['id']);

            if ($return == null) {
                $return = $this->filter($data, 1);
                $return['full_name'] = $return['first_name'].' '.$return['last_name'];
                $return['subtotal'] = $this->number($data['subtotal']);
                $return['tax'] = $this->number($data['tax']);
                $return['total'] = $this->number($data['total']);
                $return['notes'] = $this->filter($data['notes']);
                $return['count_notes'] = $this->count_notes($data['notes']);
                $return['extras'] = json_decode($data['extras'], 1);
                $return['payment_info'] = json_decode($data['payment_info'], 1);

                if (!empty($return)) {
                    parent::cache()->set('_cache_'.$this->plugin.'_'.$data['id'], $return);
                }
            }
        
            return $return;
        }
        
        return false;
    }
}
