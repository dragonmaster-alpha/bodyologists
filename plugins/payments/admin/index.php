<?php

use App\Flash as Flash;

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

if (!strstr($_SERVER['PHP_SELF'], "admin.php")) {
    header("Location: index.php");
    exit();
}

$plugin_name = basename(str_replace('admin', '', dirname(__FILE__)));
if ($administrator->admin_access($plugin_name)) {
    # Class inclusion
    $credit_card = new App\Creditcards;
    $validate = new App\Validator;
    $payments = new Plugins\Payments\Classes\Payments;
        
    switch ($_REQUEST['op']) {
        default:

            $meta['title'] = 'Payments Summary';
            if (isset($_GET['q'])) {
                if (!empty($_GET['q'])) {
                    $query = $helper->filter($_GET['q'], 1, 1);
                    $items_info = $payments->search($query, 0, _TOTAL_TO_LOAD);

                    if (!empty($items_info)) {
                        ob_start('App\Router::mod_rewrite');
                        foreach ($items_info as $item) {
                            include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/payments/admin/layout/layout.payments.list.phtml');
                        }
                        ob_end_flush();
                    } else {
                        ob_start('App\Router::mod_rewrite');
                        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.empty.phtml');
                        ob_end_flush();
                    }
                } else {
                    $items_info = $payments->list_items(0, _TOTAL_TO_LOAD);

                    ob_start('App\Router::mod_rewrite');
                    foreach ($items_info as $item) {
                        include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/payments/admin/layout/layout.payments.list.phtml');
                    }
                    ob_end_flush();
                }
            } else {
                $items_info = $payments->list_items(0, _TOTAL_TO_LOAD);

                ob_start('ob_gzhandler');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/payments/admin/layout/layout.payments.phtml');
                $layout = ob_get_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
            }

        break;

        case 'more':

            $start = (int) $_GET['s'];
            $status = (!empty($_GET['status'])) ? (int) $_GET['status'] : 0;
            $items_info = $payments->list_items($start, _TOTAL_TO_LOAD, $status);

            if (!empty($items_info)) {
                ob_start('App\Router::mod_rewrite');
                foreach ($items_info as $item) {
                    include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/payments/admin/layout/layout.payments.list.phtml');
                }
                ob_end_flush();
            }

        break;

        case 'new':

            $bill_country = 'US';

            if (isset($_SESSION['payments'])) {
                $item = $_SESSION['payments'];
            }
            
            $meta['title'] = 'New Transaction';

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/payments/admin/layout/layout.payments.new.phtml');
            $layout = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');

        break;

        case "edit":

            if (isset($_GET['id'])) {
                $item = $payments->get_items_info((int) $_GET['id']);
                $bill_country = $item['country'];
            }
            
            $meta['title'] = 'Payment Information';

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/payments/admin/layout/layout.payments.edit.phtml');
            ob_end_flush();

        break;

        case "save":

            try {
                $data = $frm->filter($_POST, 1, 1);
                $api_data = [];

                unset($data['op'], $data['save_and'], $data['id'], $data['modified'], $data['alive']);

                foreach ($data as $k => $v) {
                    $_SESSION['payments'][$k] = $api_data[$k] = $v;
                }
                
                if (!empty($data['phone'])) {
                    $data['phone'] = $frm->phone($data['phone']);
                }

                if (!$frm->check_email($data['email'])) {
                    throw new Exception("Entered email is not valid", 1);
                }

                $_SESSION['payments']['id'] = $frm->sql_insert('payments', [
                    'ip' => $_SERVER['REMOTE_ADDR']
                ]);

                $_SESSION['payments']['trans'] = $api_data['trans'] = $frm->generate_order_number($_SESSION['shopping_cart']['order_id']);

                # Site info
                $api_data['order_date'] = date('M jS, Y g:i A');
                $api_data['domain'] = $frm->site_domain();
                $api_data['sitename'] = $frm->config['sitename'];
                $api_data['contactname'] = $frm->config['contactname'];
                $api_data['contactemail'] = $frm->config['contactemail'];
                # Amounts info
                $api_data['subtotal'] = $frm->number($_SESSION['payments']['subtotal'], 1);

                $api_data['phone'] = $_SESSION['payments']['phone'] = $frm->phone($data['phone']);

                if (!empty($api_data['charge_tax'])) {
                    $_SESSION['payments']['taxes'] = $api_data['taxes'] = $settings->get_taxes($api_data['state'], $api_data['subtotal']);
                    $_SESSION['payments']['total'] = $api_data['amount'] = $frm->number($api_data['subtotal'] + $frm->number($api_data['taxes'], 1), 1);
                } else {
                    $_SESSION['payments']['total'] = $api_data['amount'] = $api_data['subtotal'];
                }

                # Submit Payment
                $pay = new App\Processor($api_data);
                $payment_result = $pay->submit_payment();

                # Check Payment Answer
                if ($payment_result['answer'] == 'SUCCESS') {
                    $_SESSION['payments']['merchand_trans_id'] = $payment_result['trans_id'];
                                        
                    # Insert order
                    $result = $payments->insert($_SESSION['payments']);
                    $administrator->record_log("Payment Transaction", "Payment with transaction number: '".$_SESSION['payments']['trans']."' has been successfully charged.");

                    # Redirect
                    Flash::set('success', 'Payment with transaction number: '.$_SESSION['payments']['trans'].' has been successfully charged..', 'admin/admin.php?plugin=payments');
                } else {
                    throw new Exception($payment_result['reason'], 1);
                }
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'back');
            }

        break;

        case "delete":

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select a transaction to proceed with this action.');
                }
                
                $id = (int) $_GET['id'];
                $item_info = $payments->get_items_info($id);
                $payments->delete($id);
                
                $administrator->record_log("Payment transaction deleted", "Deletion request for transaction '".$item_info['trans']."' has been executed");
                $helper->json_response(['answer' => 'done', 'message' => 'Action successfully done.']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;

        case 'done':

            $meta['title'] = 'Payment Approved';
            
            $id = $_GET['id'];
            unset($_SESSION['payments']);

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/payments/admin/layout/layout.payments.done.phtml');
            $layout = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');

        break;

         # Notes
        case "notes":

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select a transaction to proceed', 1);
                }
                $id = (int) $_GET['id'];
                $item = $payments->get_items_info($id);

                ob_start('App\Router::mod_rewrite');
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/payments/admin/layout/layout.payments.notes.phtml');
                ob_end_flush();
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'back');
            }

        break;

        case "save_notes":

            try {
                if (empty($_POST['id'])) {
                    throw new Exception('You must select a transaction to proceed', 1);
                }
                $data = $frm->filter($_POST, 1, 1);
                $data['author'] = $administrator->admin_info['name'];
                
                $payments->save_notes($data);
                $administrator->record_log("Payment notes", "Notes for transaction: '".$data['transaction']."' has been successfully saved");
                $helper->json_response(['answer' => 'done', 'message' => 'Notes successfully saved.']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }

        break;

        case 'receipt':

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select a transaction to proceed', 1);
                }

                $id = (int) $_GET['id'];
                $item = $payments->get_items_info($id);

                ob_start('App\Router::mod_rewrite');
                include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/payments/layout/emails/layout.invoice.phtml');
                $mail_body = ob_get_clean();
                
                $frm->send_emails('Receipt for '.$frm->site_domain(), $mail_body, 'transactions@'.$frm->site_domain(), '', $item['email'], $item['first_name'].' '.$item['last_name']);
                $administrator->record_log("Payment Receipt", "Payment receipt for transaction number : '".$data['transaction']."' has been successfully sent");
                Flash::set('success', 'Receipt successfully sent.', 'back');
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'back');
            }

        break;

        case 'print':

            try {
                if (empty($_GET['id'])) {
                    throw new Exception('You must select a transaction to proceed', 1);
                }

                $id = (int) $_GET['id'];
                $item = $payments->get_items_info($id);

                ob_start('ob_gzhandler');
                include($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/payments/admin/layout/layout.payments.receipt.phtml');
                $layout = ob_get_clean();
                require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout_popup.php');
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'back');
            }

        break;

        # Payments Export
        case "export":

            try {
                $today = date('m-d-y');
                $csv = $payments->export();
                
                # Record log
                $administrator->record_log("Payments exported", "Payments database has being successfully exported.");

                $export = new App\Formatters\CSVWriter($csv);
                $export->headers();
                $export->output();
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'back');
            }

        break;
    }
} else {
    header("Location: index.php");
    exit();
}
