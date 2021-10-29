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
    public $gateway_retries = 3;
    public $gateway_retry_wait = 2; //seconds

    private $debug = false;
    
    public function __construct(array $api_data)
    {
        try {
            if (count($api_data) == 0) {
                throw new Exception(_EMPTY_DATA);
            }
            if (empty($api_data['login'])) {
                throw new Exception('You must set your API Login ID');
            }
            if (empty($api_data['password'])) {
                throw new Exception('You must set your API Transaction Key');
            }
            if (empty($api_data['transaction_type'])) {
                $api_data['transaction_type'] = 'create_account';
            }

            $this->load_config($api_data);
            // CreateRecurringPaymentsProfile			Create a recurring payments profile.
            // GetRecurringPaymentsProfileDetails		Obtain information about a recurring payments profile.
            // ManageRecurringPaymentsProfileStatus		Cancel, suspend, or reactivate a recurring payments profile.
            // BillOutstandingAmount					Bill the buyer for the outstanding balance associated with a recurring payments profile.
            // UpdateRecurringPaymentsProfile			Update a recurring payments profile.
            // DoReferenceTransaction					Process a payment from a buyer's account, which is identified by a previous transaction.
        } catch (Exception $e) {
            throw $e;
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
        $this->data['USER'] = $api_data['login'];
        $this->data['PWD'] = $api_data['password'];
        $this->data['SIGNATURE'] = $api_data['extra'];
        $this->data['TOKEN'] = $api_data['cc_number'];
        $this->data['PROFILEID'] = $api_data['ref_id'];
        $this->data['AMT'] = $api_data['total'];
        $this->data['PROFILEREFERENCE'] = $api_data['trans'];
        $this->data['DESC'] = $api_data['description'];
        $this->data['SUBSCRIBERNAME'] = $api_data['first_name'].' '.$api_data['last_name'];
        $this->data['MAXFAILEDPAYMENTS'] = 1;

        $this->data['TOTALBILLINGCYCLES'] = (!empty($api_data['interval_length'])     ? $api_data['interval_length']      : '1');
        $this->data['BILLINGPERIOD'] = (!empty($api_data['interval_unit'])       ? $api_data['interval_unit']        : 'Month');
        $this->data['PROFILESTARTDATE'] = date('Y-m-d', strtotime('+'.$api_data['interval_length'].' '.$this->data['interval_unit']));

        $this->data['TOTALBILLINGCYCLES'] = (!empty($api_data['total_occurrences'])   ? $api_data['total_occurrences']    : 9999);
        $this->data['TRIALTOTALBILLINGCYCLES'] = (!empty($api_data['trial_occurrences'])   ?  $api_data['trial_occurrences']   : 0);
        $this->data['TRIALAMT'] = (!empty($api_data['trial_amount'])        ?  $api_data['trial_amount']        : 0);
        $this->data['CURRENCYCODE'] = (!empty($api_data['currency'])        	?  $api_data['currency']        	: 'USD');
        $this->data['CREDITCARDTYPE'] = $api_data['cc_type'];
        $this->data['ACCT'] = $api_data['cc_number'];
        $this->data['EXPDATE'] = date('mY', strtotime($api_data['cc_year'].'-'.$api_data['cc_month'].'-1'));
        $this->data['CVV2'] = $api_data['cc_cvv'];

        $this->data['FIRSTNAME'] = $api_data['first_name'];
        $this->data['LASTNAME'] = $api_data['last_name'];
        $this->data['STREET'] = $api_data['address'];
        $this->data['CITY'] = $api_data['city'];
        $this->data['STATE'] = $api_data['state'];
        $this->data['ZIP'] = $api_data['zipcode'];
        $this->data['EMAIL'] = $api_data['email'];
        $this->data['COUNTRYCODE'] = (!empty($api_data['country']))            ? $api_data['country']              : 'US';

        $this->data['SHIPTONAME'] = (!empty($api_data['ship_first_name'])     ? $api_data['ship_first_name'].' '.$api_data['ship_last_name']      : $this->data['first_name'].' '.$this->data['last_name']);
        $this->data['SHIPTOSTREET'] = (!empty($api_data['ship_address'])        ? $api_data['ship_address']         : $this->data['address']);
        $this->data['SHIPTOCITY'] = (!empty($api_data['ship_city'])           ? $api_data['ship_city']            : $this->data['city']);
        $this->data['SHIPTOSTATE'] = (!empty($api_data['ship_state'])          ? $api_data['ship_state']           : $this->data['state']);
        $this->data['SHIPTOZIP'] = (!empty($api_data['ship_zip'])            ? $api_data['ship_zip']             : $this->data['zip']);
        $this->data['SHIPTOCOUNTRY'] = (!empty($api_data['ship_country'])        ? $api_data['ship_country']         : $this->data['country']);
        $this->data['SHIPTOPHONENUM'] = (!empty($api_data['ship_phone'])        	? $api_data['ship_phone']         	: $this->data['phone']);
    }

    public function get_gateway_url()
    {
        return (strtolower(_API_REQUEST_TYPE) == 'live') ? 'https://api-3t.paypal.com/nvp' : 'https://api-3t.sandbox.paypal.com/nvp';
    }

    public function get_data_string()
    {
        foreach ($this->data as $key => $value) {
            if ($this->debug) {
                echo $key.' = '.$value;
            }
            $query[] = strtoupper($key).'['.strlen($value).']='.$value;
        }
        return implode('&', $query);
    }

    public function send_transaction()
    {
        $data_string = $this->get_data_string();
        $headers[] = "Content-Type: text/namevalue"; //or text/xml if using XMLPay.
        $headers[] = "Content-Length: ".strlen($data_string); // Length of data to be passed
        $headers[] = "X-VPS-Timeout: {45}";
        $headers[] = "X-VPS-Request-ID:".uniqid(rand(), true);
        $headers[] = "X-VPS-VIT-Client-Type: PHP/cURL"; // What you are using
        
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
                break;
            }
        }
        if (!array_key_exists('http_code', $headers) || $headers['http_code'] != self::HTTP_RESPONSE_OK) {
            throw new Exception('Invalid credentials');
        }

        return $result;
    }

    public function response_handler($response_arr)
    {
        if ($this->debug) {
            echo __method__.' response='.print_r($response_arr, true).'';
            echo __method__.' RESULT='.$result_code.'';
        }

        if ($response_arr['PROFILESTATUS'] == 'ActiveProfile' || $response_arr['PROFILESTATUS'] == 'PendingProfile') {
            $trans_result = $response_arr;
            $trans_result['answer'] = 'SUCCESS';
        } else {
            $trans_result['answer'] = 'DENIED';
            $trans_result['reason'] = $response_arr['RESPMSG'];
        }
        
        return $trans_result;
    }

    public function process()
    {
        return $this->response_handler($this->send_transaction());
    }

    public function get_account_status()
    {
        $this->data['METHOD'] = 'GetRecurringPaymentsProfileDetails';
    }

    public function create_account()
    {
        $this->data['METHOD'] = 'CreateRecurringPaymentsProfile';
    }
    
    public function update_account()
    {
        $this->data['METHOD'] = 'UpdateRecurringPaymentsProfile';
    }
    
    public function delete_account()
    {
        $this->data['METHOD'] = 'ManageRecurringPaymentsProfileStatus';
        $this->data['ACTION'] = 'Cancel';
    }
}
