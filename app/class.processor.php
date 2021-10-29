<?php

namespace App;

use Omnipay\Omnipay;

require_once('vendors/autoload.php');
require_once('app/class.router.php');

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

class Processor extends Format
{
    public $gateway;
    public $data;
    public $return = [];
    public $api_data = [];
    
    public $trans = 0;
    public $formatted_cc_number = 'xxxx-xxxx-xxxx-xxxx';
    public $recurring = null;
    public $trans_returned_info;
    
    public function __construct(array $api_data, $recurring = null)
    {
        try {
            $this->config = parent::get_config();
            $this->api_data = $api_data;
            $this->recurring = $recurring;
            
            if (empty($this->config['merchant'])) {
                throw new \Exception('You must select a gateway to continue.');
            }
            if ($this->empty_array($this->api_data)) {
                throw new \Exception(_EMPTY_DATA);
            }

            if (!empty($this->config['merchant_info']['user'])) {
                $this->api_data['login'] = $this->config['merchant_info']['user'];
            }
            if (!empty($this->config['merchant_info']['password'])) {
                $this->api_data['password'] = $this->config['merchant_info']['password'];
            }
            if (!empty($this->config['merchant_info']['extra'])) {
                $this->api_data['extra'] = $this->config['merchant_info']['extra'];
            }
        } catch (\Exception $e) {
            $_SESSION['error']['message'] = $e->getMessage();
            return;
        }
    }
        
    public function __set($key, $val)
    {
        $this->data[$key] = $val;
    }

    public function __get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
    }

    public function submit_payment()
    {
        try {
            require_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/app/payment_gateways/'.$this->config['merchant'].'/class.payment.gateway.php');

            $trans = new \App\Payments\Payment($this->api_data);
            $trans_returned_info = $trans->process();
            
            if ($trans_returned_info['answer'] == 'SUCCESS') {
                if (!empty($this->recurring)) {
                    require_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/kernel/classes/payment_gateways/'.$this->config['merchant'].'/class.recurring.billing.php');
                    $rb = new Recurring_Billing($api_data);
                    $this->return['rb'] = $rb->process();
                }

                if (!empty($data['create_customer_account'])) {
                    if (!empty($_SESSION['user_info']['payments_profile_id'])) {
                        $api_data['transaction_type'] = 'create_profile_from_transaction';
                        $api_data['ref_id'] = $_SESSION['user_info']['payments_profile_id'];
                    } else {
                        $api_data['transaction_type'] = 'create_profile_from_transaction';
                        $api_data['ref_id'] = $trans_returned_info['trans_id'];
                    }

                    require_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/kernel/classes/payment_gateways/'.$this->config['merchant'].'/class.customer.information.manager.php');
                    $cp = new Customer_Profile($api_data);
                    $this->return['cp'] = $cp->process();
                }
                
                $this->return['answer'] = 'SUCCESS';
                $this->return['trans_id'] = $trans_returned_info['trans_id'];
                $this->barcode($api_data['trans']);
                
                if ($this->api_data['transaction_type'] == 'VOID') {
                    $this->mail_void_order();
                } elseif ($this->api_data['transaction_type'] == 'REFUND') {
                    $this->mail_refunded_order();
                } else {
                    $this->mail_order();
                }
            } else {
                $this->return['answer'] = 'DENIED';
                $this->return['reason'] = $trans_returned_info['reason'];
            }

            return $this->return;
        } catch (Exception $e) {
            return 'Process Report: '.$e->getMessage();
        }
    }
    
    public function update_recurring_billing()
    {
        try {
            require_once('kernel/classes/payment_gateways/'.$this->config['merchant'].'/class.recurring.billing.php');

            $rb = new Recurring_Billing($api_data);
            $rb->update_account();

            if (!$rb->txn_successful) {
                throw new Exception('Recurring billing failed, you must check your recurring billing provider and make sure it is all set up right.');
            }

            $this->mail_updated_recurring_billing();
        } catch (Exception $e) {
            $_SESSION['error']['message'] = $e->getMessage();
            return;
        }
    }
    
    public function delete_recurring_billing()
    {
        try {
            require_once('kernel/classes/payment_gateways/'.$this->config['merchant'].'/class.recurring.billing.php');
            $rb = new Recurring_Billing($api_data);

            $rb->delete_account();
            if (!$rb->txn_successful) {
                throw new Exception('Recurring billing failed, you must check your recurring billing provider and make sure it is all set up right.');
            }

            $this->mail_deleted_recurring_billing();
        } catch (Exception $e) {
            $_SESSION['error']['message'] = $e->getMessage();
            return;
        }
    }

    public function mail_order()
    {
        ob_start('App\Router::mod_rewrite');
        include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/layout/emails/layout.invoice.phtml');
        $mail_body = ob_get_clean();
        
        $this->send_emails('Order confirmation from '.$this->site_domain(), $mail_body, 'transactions@'.$this->site_domain(), '', $this->api_data['email'], $this->api_data['first_name'].' '.$this->api_data['last_name']);
        $this->send_emails('New order on '.$this->site_domain(), $mail_body, 'transactions@'.$this->site_domain());
    }
    
    public function mail_void_order()
    {
        ob_start('App\Router::mod_rewrite');
        include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.$this->api_data['plugin'].'/layout/emails/layout.void.order.phtml');
        $mail_body = ob_get_clean();
        
        $this->send_emails('Order voided on '.$this->site_domain(), $mail_body, 'transactions@'.$this->site_domain(), '', $this->api_data['email'], $this->api_data['first_name'].' '.$this->api_data['last_name']);
        $this->send_emails('Order voided on '.$this->site_domain(), $mail_body, 'transactions@'.$this->site_domain());
    }
    
    public function mail_refunded_order()
    {
        ob_start('App\Router::mod_rewrite');
        include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.$this->api_data['plugin'].'/layout/emails/layout.refunded.order.phtml');
        $mail_body = ob_get_clean();
        
        $this->send_emails('Order refunded on '.$this->site_domain(), $mail_body, 'transactions@'.$this->site_domain(), '', $this->api_data['email'], $this->api_data['first_name'].' '.$this->api_data['last_name']);
        $this->send_emails('Order refunded on '.$this->site_domain(), $mail_body, 'transactions@'.$this->site_domain());
    }
    
    public function mail_updated_recurring_billing()
    {
        ob_start('App\Router::mod_rewrite');
        include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.$this->api_data['plugin'].'/layout/emails/layout.user.updated.recurring.billing.phtml');
        $mail_body = ob_get_clean();
        
        $this->send_emails('Updated recurring billing on '.$this->site_domain(), $mail_body, '', '', $this->api_data['email'], $this->api_data['first_name'].' '.$this->api_data['last_name']);
        $this->send_emails('Updated recurring billing on '.$this->site_domain(), $mail_body, 'transactions@'.$this->site_domain());
    }
    
    public function mail_deleted_recurring_billing()
    {
        ob_start('App\Router::mod_rewrite');
        include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.$this->api_data['plugin'].'/layout/emails/layout.deleted.recurring.billing.phtml');
        $mail_body = ob_get_clean();
        
        $this->send_emails('Recurring account deleted on '.$this->site_domain(), $mail_body, '', '', $this->api_data['email'], $this->api_data['first_name'].' '.$this->api_data['last_name']);
        $this->send_emails('Recurring account deleted on '.$this->site_domain(), $mail_body, 'transactions@'.$this->site_domain());
    }
}
