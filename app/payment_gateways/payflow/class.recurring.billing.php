<?php

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

class Recurring_Billing
{
    const HTTP_RESPONSE_OK = 200;
    const KEY_MAP_ARRAY = 'map';
    
    public $data;
    public $headers = [];
    public $gateway_retries = 3;
    public $gateway_retry_wait = 2; //seconds
    
    public $vps_timeout = 45;

    public $avs_addr_required = 0;
    public $avs_zip_required = 0;
    public $cvv2_required = 0;
    public $fraud_protection = false;
    public $raw_response;
    public $trans_id = null;
    
    //public $response;
    public $response_arr = [];
    public $txn_successful = null;
    public $raw_result;
    public $debug = false;

    public function __construct(array $api_data)
    {
        try {
            if (count($api_data) == 0) {
                throw new Exception(_EMPTY_DATA);
            }
            if ($api_data['transaction_type'] == 'REFUND' || $api_data['transaction_type'] == 'VOID') {
                if (empty($api_data['trans_id'])) {
                    throw new Exception('A transaction id must be set in order to VOID or REFUND this amount');
                }
            }
            if (empty($api_data['total'])) {
                throw new Exception(_ERROR_INVALID_AMOUNT);
            }
            if (empty($api_data['cc_number'])) {
                throw new Exception(_ERROR_INVALID_CARD_NUMBER);
            }
            if (empty($api_data['cc_exp_date'])) {
                throw new Exception(_ERROR_INVALID_CARD_EXP_DATE);
            }
            if (empty($api_data['cc_ccv'])) {
                throw new Exception(_ERROR_INVALID_CVV_NUMBER);
            }
            if (empty($api_data['first_name'])) {
                throw new Exception(_INCORRECT_BILLING_FIRST_NAME);
            }
            if (empty($api_data['last_name'])) {
                throw new Exception(_INCORRECT_BILLING_LAST_NAME);
            }
            if (empty($api_data['address'])) {
                throw new Exception(_INCORRECT_BILLING_ADDRESS);
            }
            if (empty($api_data['city'])) {
                throw new Exception(_INCORRECT_BILLING_CITY);
            }
            if (empty($api_data['state'])) {
                throw new Exception(_INCORRECT_BILLING_STATE);
            }
            if (empty($api_data['zipcode'])) {
                throw new Exception(_INCORRECT_BILLING_ZIPCODE);
            }
            $this->load_config($api_data);
        } catch (Exception $e) {
            die($e->getMessage());
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
        return null;
    }

    public function load_config(array $api_data)
    {
        /**
         * TRXTYPE Options
         * S = Sale transaction (default),
         * C = Credit,
         * A = Authorization,
         * D = Delayed Capture,
         * V = Void,
         * N = Duplicate transaction
         */
        if ($api_data['transaction_type'] == 'CHANGE') {
            $this->data['ACTION'] = 'M';
        } elseif ($api_data['transaction_type'] == 'DELETE') {
            $this->data['ACTION'] = 'C';
        } else {
            $this->data['ACTION'] = 'A';
        }
        $this->data['TRXTYPE'] = 'R';
        $this->data['PROFILENAME'] = 'RegularSubscription';
        $this->data['PARTNER'] = $api_data['partner'];
        $this->data['USER'] = $api_data['user'];
        $this->data['PWD'] = $api_data['password'];
        $this->data['VENDOR'] = (!empty($api_data['vendor']))             ? $api_data['vendor']               : $api_data['user'];
        $this->data['IP'] = $_SERVER["REMOTE_ADDR"];
        // TENDER Options A = Automated clearinghouse, C = Credit card, D = Pinless debit, K = Telecheck, P = PayPal.
        $this->data['TENDER'] = 'C';
        $this->data['AMT'] = $api_data['total'];
        $this->data['FIRSTNAME'] = $api_data['first_name'];
        $this->data['LASTNAME'] = $api_data['last_name'];
        $this->data['STREET'] = $api_data['address'];
        $this->data['CITY'] = $api_data['city'];
        $this->data['STATE'] = $api_data['state'];
        $this->data['ZIP'] = $api_data['zipcode'];
        $this->data['BILLTOCOUNTRY'] = (!empty($api_data['country']))            ? $api_data['country']              : 'US';

        $this->data['EMAIL'] = $api_data['email'];
        $this->data['PHONENUM'] = $api_data['phone'];

        $this->data['START'] = date('mdY', strtotime($api_data['start_date']));
        $this->data['PAYPERIOD'] = strtoupper(substr($api_data['interval_length'], 0, 4));
        $this->data['TERM'] = (!empty($api_data['total_occurrences']))  ? $api_data['total_occurrences']    : 0;
        
        $this->data['DESC'] = (!empty($api_data['description']))        ? $api_data['description']          : 'Transaction accepted from '.$api_data['first_name'].' '.$api_data['last_name'].' on '.date('m/d/Y h:i A').' email: '.$api_data['email'].' phone: '.$api_data['phone'];
        
        // Only needed for VOID or REFUND transactions
        if ($api_data['transaction_type'] == 'REFUND' || $api_data['transaction_type'] == 'VOID') {
            $this->data['ORIGID'] = $api_data['trans_id']; // Mandatory
        }
        
        // Credit Card Information
        $this->data['ACCT'] = $api_data['cc_number'];
        $this->data['EXPDATE'] = $api_data['cc_exp_date'];
        $this->data['CVV2'] = $api_data['cc_ccv'];
    }

    public function get_gateway_url()
    {
        if (strtolower(_API_REQUEST_TYPE) == 'live') {
            return 'https://payflowpro.paypal.com';
        }
        
        return 'https://pilot-payflowpro.paypal.com';
    }

    public function get_data_string()
    {
        $query = [];
        if (!isset($this->data['VENDOR']) || !$this->data['VENDOR']) {
            $this->data['VENDOR'] = $this->data['USER'];
        }
        foreach ($this->data as $key => $value) {
            if ($this->debug) {
                echo $key.' = '.$value;
            }
            $query[] = strtoupper($key).'['.strlen($value).']='.$value;
        }
        return implode('&', $query);
    }

    public function before_send_transaction()
    {
        $this->txn_successful = false;
        $this->raw_response = null; //reset raw result
        $this->response_arr = [];
    }

    public function reset()
    {
        $this->txn_successful = null;
        $this->raw_response = null; //reset raw result
        $this->response_arr = [];
        $this->data = [];
        $this->load_config();
    }

    public function send_transaction()
    {
        try {
            $this->before_send_transaction();
            $data_string = $this->get_data_string();
            $headers[] = "Content-Type: text/namevalue"; //or text/xml if using XMLPay.
            $headers[] = "Content-Length: ".strlen($data_string); // Length of data to be passed
            $headers[] = "X-VPS-Timeout: {$this->vps_timeout}";
            $headers[] = "X-VPS-Request-ID:".uniqid(rand(), true);
            $headers[] = "X-VPS-VIT-Client-Type: PHP/cURL"; // What you are using
            $headers = array_merge($headers, $this->headers);
            
            if ($this->debug) {
                echo __method__.' Sending: '.$data_string.'';
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->get_gateway_url());
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
            curl_setopt($ch, CURLOPT_TIMEOUT, 90); // times out after 90 secs
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string); //adding POST data
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
            curl_setopt($ch, CURLOPT_FORBID_REUSE, true); //forces closure of connection when done
            curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST
            # Godaddy proxy connection
            //curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            //curl_setopt ($ch, CURLOPT_PROXY,"http://proxy.shr.secureserver.net:3128");
            $i = 0;
            
            while ($i++ <= $this->gateway_retries) {
                $result = curl_exec($ch);
                $headers = curl_getinfo($ch);
                if (array_key_exists('http_code', $headers) && $headers['http_code'] != self::HTTP_RESPONSE_OK) {
                    sleep($this->gateway_retry_wait); // Let's wait to see if its a temporary network issue.
                } else {
                    // we got a good response, drop out of loop.
                    break;
                }
            }
            if (!array_key_exists('http_code', $headers) || $headers['http_code'] != self::HTTP_RESPONSE_OK) {
                throw new Exception('Invalid credentials');
            }
            
            $this->raw_response = $result;
            $result = strstr($result, "RESULT");
            $ret = [];

            while (strlen($result) > 0) {
                $keypos = strpos($result, '=');
                $keyval = substr($result, 0, $keypos);
                // value
                $valuepos = strpos($result, '&') ? strpos($result, '&') : strlen($result);
                $valval = substr($result, $keypos + 1, $valuepos - $keypos - 1);
                // decoding the respose
                $ret[$keyval] = $valval;
                $result = substr($result, $valuepos + 1, strlen($result));
            }
            return $ret;
        } catch (exception $e) {
            curl_close($ch);
            die($e->getMessage());
        }
    }

    public function response_handler($response_arr)
    {
        try {
            $result_code = $response_arr['RESULT']; // get the result code to validate.

            if ($this->debug) {
                echo __method__.' response='.print_r($response_arr, true).'';
                echo __method__.' RESULT='.$result_code.'';
            }
            if ($result_code == 0) {
                if ($this->avs_addr_required) {
                    $err_msg = "Your billing (street) information does not match.";
                    if (isset($response_arr['AVSADDR'])) {
                        if ($response_arr['AVSADDR'] != "Y") {
                            throw new Exception($err_msg);
                        }
                    } else {
                        if ($this->avs_addr_required == 2) {
                            throw new Exception($err_msg);
                        }
                    }
                }

                if ($this->avs_zip_required) {
                    $err_msg = "Your billing (zipcode) information does not match. Please re-enter.";
                    if (isset($nvpArray['AVSZIP'])) {
                        if ($nvpArray['AVSZIP'] != "Y") {
                            throw new Exception($err_msg);
                        }
                    } else {
                        if ($this->avs_zip_required == 2) {
                            throw new Exception($err_msg);
                        }
                    }
                }
                if ($this->require_cvv2_match) {
                    $err_msg = "Your card code is invalid. Please re-enter.";
                    if (array_key_exists('CVV2MATCH', $response_arr)) {
                        if ($response_arr['CVV2MATCH'] != "Y") {
                            throw new Exception($err_msg);
                        }
                    } else {
                        if ($this->require_cvv2_match == 2) {
                            throw new Exception($err_msg);
                        }
                    }
                }
                //
                // Return code was 0 and no AVS exceptions raised
                //
                $this->txn_successful = true;
                $this->trans_id = $response_arr['PNREF'];
                parse_str($this->raw_response, $this->response_arr);
                return $this->response_arr;
            } elseif ($result_code == 1 || $result_code == 26) {
                throw new Exception("Invalid API Credentials");
            } elseif ($result_code == 12) {
                // Hard decline from bank.
                throw new Exception("Your transaction was declined.");
            } elseif ($result_code == 13) {
                // Voice authorization required.
                throw new Exception("Your Transaction is pending. Contact Customer Service to complete your order.");
            } elseif ($result_code == 23 || $result_code == 24) {
                // Issue with credit card number or expiration date.
                $msg = 'Invalid credit card information: '.$response_arr['RESPMSG'];
                throw new Exception($msg);
            }

            // Using the Fraud Protection Service.
            // This portion of code would be is you are using the Fraud Protection Service, this is for US merchants only.
            if ($this->fraud_protection) {
                if ($result_code == 125) {
                    // 125 = Fraud Filters set to Decline.
                    throw new Exception("Your Transaction has been declined. Contact Customer Service to place your order.");
                } elseif ($result_code == 126) {
                    throw new Exception("Your Transaction is Under Review. We will notify you via e-mail if accepted.");
                } elseif ($result_code == 127) {
                    throw new Exception("Your Transaction is Under Review. We will notify you via e-mail if accepted.");
                }
            }
            //
            // Throw generic response
            //
            throw new Exception($response_arr['RESPMSG']);
        } catch (exception $e) {
            die($e->getMessage());
        }
    }

    public function process()
    {
        try {
            return $this->response_handler($this->send_transaction());
        } catch (exception $e) {
            die($e->getMessage());
        }
    }

    public function apply_associative_array($arr, $options = [])
    {
        try {
            $map_array = [];
            if (isset($options[self::KEY_MAP_ARRAY])) {
                $map_array = $options[self::KEY_MAP_ARRAY];
            }

            foreach ($arr as $cur_key => $val) {
                if (isset($map_array[$cur_key])) {
                    $cur_key = $map_array[$cur_key];
                } else {
                    if (isset($options['require_map']) && $options['require_map']) {
                        continue;
                    }
                }
                $this->data[strtoupper($cur_key)] = $val;
            }
        } catch (exception $e) {
            die($e->getMessage());
        }
    }
}
