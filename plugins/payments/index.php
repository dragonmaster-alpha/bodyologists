<?php

use App\Breadcrumbs as Breadcrumbs;

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

global $helper, $meta, $settings;

$plugin_name = basename(dirname(__FILE__));
$helper->get_plugin_lang($plugin_name);

# Class inclusion
$credit_card = new App\Creditcards;
$validate = new App\Validator;
$payments = new Plugins\Payments\Classes\Payments;

switch ($_REQUEST['op']) {
    default:

        try {
            $meta['title'] = 'Payment Area';

            $country = (!empty($_SESSION['payments']['country'])) ? $_SESSION['payments']['country'] : 'US';
            
            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/payments/layout/layout.payments.new.phtml');
            $modcontent = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/layout.php');
        } catch (Exception $e) {
            App\Flash::set('error', $e->getMessage(), 'index.php?plugin=payments');
        }

    break;

    case "save":

        try {
            $data = $helper->filter($_POST, 1, 1);
            $api_data = [];

            $data['cc_type'] = $credit_card->get_card_type($data['cc_number']);

            foreach ($data as $k => $v) {
                $_SESSION['payments'][$k] = $api_data[$k] = $v;
            }
            
            # Validate data
            $validation_rules = [];
            $validation_rules = [
                'email' => ['type' => 'email', 'msg' => _INCORRECT_EMAIL_ADDRESS, 'required' => true, 'trim' => true],
                'first_name' => ['type' => 'name', 'msg' => _INCORRECT_BILLING_FIRST_NAME, 'required' => true, 'min' => 1, 'max' => 64, 'trim' => true],
                'last_name' => ['type' => 'name', 'msg' => _INCORRECT_BILLING_LAST_NAME, 'required' => true, 'min' => 1, 'max' => 64, 'trim' => true],
                'phone' => ['type' => 'phone', 'msg' => _INCORRECT_BILLING_PHONE, 'required' => true, 'min' => 4, 'max' => 12, 'trim' => true],
                'agree' => ['type' => 'bool', 'msg' => _ERROR_AUTORIZE_FOR_CHARGE, 'required' => true, 'trim' => true]
            ];

            if (!empty($data['country'])) {
                if ($data['country'] != 'US' && $data['country'] != 'CA') {
                    $validation_rules['address'] = ['type' => 'address', 'msg' => _INCORRECT_BILLING_ADDRESS, 'required' => true, 'min' => 4, 'max' => 255, 'trim' => true];
                    $validation_rules['city'] = ['type' => 'name', 'msg' => _INCORRECT_BILLING_CITY, 'required' => true, 'min' => 2, 'max' => 120, 'trim' => true];
                } else {
                    $validation_rules['address'] = ['type' => 'address', 'msg' => _INCORRECT_BILLING_ADDRESS, 'required' => true, 'min' => 4, 'max' => 255, 'trim' => true];
                    $validation_rules['city'] = ['type' => 'name', 'msg' => _INCORRECT_BILLING_CITY, 'required' => true, 'min' => 2, 'max' => 120, 'trim' => true];
                    $validation_rules['state'] = ['type' => 'string', 'msg' => _INCORRECT_BILLING_STATE, 'required' => true, 'min' => 2, 'trim' => true];
                    $validation_rules['zipcode'] = ['type' => 'string', 'msg' => _INCORRECT_BILLING_ZIPCODE, 'required' => true, 'trim' => true];
                }
            } else {
                if ($data['country'] == 'US' && $data['country'] == 'CA') {
                    $validation_rules['zipcode'] = ['type' => 'string', 'msg' => _INCORRECT_BILLING_ZIPCODE, 'required' => true, 'trim' => true];
                }
            }

            # Set payment method
            $data['payment_method'] = $data['payment_method'];

            # Payment verification
            if ($data['payment_type'] == 1) {
                if ($data['payment_method'] == 'ECHECK') {
                    $data['method'] = 'ECHECK';
                    $data['account_type'] = $helper->filter($_POST['account_type']);
                    $data['routing_number'] = $helper->int($_POST['routing_number']);
                    $data['account_number'] = $helper->int($_POST['account_number']);
                    $data['bank_name'] = $helper->filter($_POST['bank_name']);

                    $validation_rules['account_type'] = ['type' => 'string', 'msg' => _ERROR_INVALID_BANK_ACCOUNT, 'required' => true, 'trim' => true];
                    $validation_rules['routing_number'] = ['type' => 'numeric', 'msg' => _ERROR_INVALID_ROUTING_NUMBER, 'required' => true, 'trim' => true];
                    $validation_rules['account_number'] = ['type' => 'numeric', 'msg' => _ERROR_INVALID_ACCOUNT_NUMBER, 'required' => true, 'trim' => true];
                    $validation_rules['bank_name'] = ['type' => 'string', 'msg' => _ERROR_INVALID_BANK_NAME, 'required' => true, 'trim' => true];
                } else {
                    $data['payment_method'] = 'Credit Card';
                    $data['method'] = 'CC';
                    $data['cc_number'] = $helper->int($_POST['cc_number']);
                    $data['cc_month'] = $helper->int($_POST['cc_month']);
                    $data['cc_year'] = $helper->int($_POST['cc_year']);
                    $data['cc_cvv'] = $helper->int($_POST['cc_cvv']);
                    $data['cc_type'] = $data['cc_type'];
                    $data['cc_date'] = $data['cc_year'].'-'.$data['cc_month'];

                    $_accept_cc_types = [];

                    if (!empty($helper->config['we_accept_visa'])) {
                        $_accept_cc_types[] = 'visa';
                    }
                    if (!empty($helper->config['we_accept_mastercard'])) {
                        $_accept_cc_types[] = 'mastercard';
                    }
                    if (!empty($helper->config['we_accept_discover'])) {
                        $_accept_cc_types[] = 'discover';
                    }
                    if (!empty($helper->config['we_accept_amex'])) {
                        $_accept_cc_types[] = 'amex';
                    }
        
                    $validation_rules['cc_type'] = ['type' => 'set', 'msg' => _ERROR_NOT_ACCEPTED_CARD_TYPE, 'required' => true, 'compare' => $_accept_cc_types];
                    $validation_rules['cc_number'] = ['type' => 'cc_number', 'msg' => _ERROR_INVALID_CARD_NUMBER, 'required' => true, 'trim' => true];
                    $validation_rules['cc_month'] = ['type' => 'numeric', 'msg' => _ERROR_INVALID_CARD_EXP_DATE, 'required' => true, 'min' => 2, 'trim' => true];
                    $validation_rules['cc_year'] = ['type' => 'numeric', 'msg' => _ERROR_INVALID_CARD_EXP_DATE, 'required' => true, 'min' => 2, 'max' => 4, 'trim' => true];
                    $validation_rules['cc_date'] = ['type' => 'cc_date', 'msg' => _ERROR_INVALID_CARD_EXP_DATE, 'required' => true];
                    $validation_rules['cc_cvv'] = ['type' => 'numeric', 'msg' => _ERROR_INVALID_CVV_NUMBER, 'required' => true, 'min' => 3, 'max' => 4, 'trim' => true];
                }
            }

            $validate->add_source($data);
            $validate->add_rules($validation_rules);
            $validate->run();

            if (!empty($validate->errors)) {
                throw new Exception(reset($validate->errors), 1);
            }
            
            unset($data['op'], $data['agree'], $data['save_and'], $data['id'], $data['modified'], $data['alive']);
            
            $_SESSION['payments']['id'] = $helper->generate_order_id('payments');
            $_SESSION['payments']['trans'] = $api_data['trans'] = $helper->generate_order_number($_SESSION['payments']['id']);

            # Site info
            $api_data['order_date'] = date('M jS, Y g:i A');
            $api_data['domain'] = $helper->site_domain();
            $api_data['sitename'] = $helper->config['sitename'];
            $api_data['contactname'] = $helper->config['contactname'];
            $api_data['contactemail'] = $helper->config['contactemail'];

            $api_data['phone'] = $_SESSION['payments']['phone'] = $helper->phone($data['phone']);

            # Amounts info
            $api_data['subtotal'] = $helper->number($_SESSION['payments']['subtotal'], 1);
            $_SESSION['payments']['tax'] = $api_data['tax'] = '0.00';
            $_SESSION['payments']['total'] = $api_data['total'] = $api_data['subtotal'];
            $api_data['plugin'] = 'payments';

            if (empty($_SESSION['payments']['total']) || $_SESSION['payments']['total'] == '0.00') {
                $_SESSION['error']['message'] = 'Your session expired, please try again.';
                $helper->redirect('index.php?plugin=payments');
            }

            # Submit Payment
            $pay = new App\Processor($api_data);
            $payment_result = $pay->submit_payment();

            # Check Payment Answer
            if ($payment_result['answer'] == 'SUCCESS') {
                $_SESSION['payments']['merchand_trans_id'] = $payment_result['trans_id'];
                
                $result = $payments->insert($_SESSION['payments']);
                $helper->redirect('index.php?plugin=payments&op=done&trans='.$_SESSION['payments']['trans']);
            } else {
                if (!empty($payment_result['reason'])) {
                    throw new Exception($payment_result['reason'], 1);
                }
                
                throw new Exception($payment_result, 1);
            }
        } catch (Exception $e) {
            App\Flash::set('error', $e->getMessage(), 'index.php?plugin=payments');
        }

    break;

    case 'done':

        $meta['title'] = 'Transaction Approved';
        
        $id = (int) $_SESSION['payments']['id'];
        $trans = $_GET['trans'];

        unset($_SESSION['payments']);

        ob_start('ob_gzhandler');
        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/payments/layout/layout.payments.done.phtml');
        $modcontent = ob_get_clean();
        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/layout.php');

    break;

    case 'print':

        try {
            if (empty($_GET['id'])) {
                throw new Exception('You must select an order to proceed', 1);
            }

            $id = (int) $_GET['id'];
            $item = $payments->get_items_info($id);

            ob_start('\App\Router::mod_rewrite');
            include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/payments/layout/emails/layout.invoice.phtml');
            ob_end_flush(); ?>
                <script type="text/javascript">
                    window.setTimeout("window.print();", 1000);
                </script>
            <?php
        } catch (Exception $e) {
            App\Flash::set('error', $e->getMessage(), 'back');
        }

    break;

    case 'set':

        if (!empty($_GET['k'] && !empty($_GET['v']))) {
            $k = $helper->filter($_GET['k'], 1, 1);
            $v = $helper->filter($_GET['v'], 1, 1);

            $_SESSION['payments'][$k] = $v;

            if ($k == 'cc_number') {
                $_SESSION['payments']['cc_type'] = $credit_card->get_card_type($v);
                echo $_SESSION['payments']['cc_type'];
            }
        }

    break;
}
