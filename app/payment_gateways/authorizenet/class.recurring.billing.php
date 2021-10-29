<?php

/**
 * @author
 * Web Design Enterprise
 * Phone: 786.234.6361
 * Website: www.webdesignenterprise.com
 * E-mail: info@webdesignenterprise.com
 *
 * @copyright
 * This work is licensed under the Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 United States License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/3.0/legalcode
 *
 * Be aware that violating this license agreement could result in the prosecution and punishment of the infractor.
 *
 * � 2002-2009 Web Design Enterprise Corp. All rights reserved.
 */

class Recurring_Billing
{
    const HTTP_RESPONSE_OK = 200;
    
    public $data;
    public $gateway_retries = 3;
    public $gateway_retry_wait = 2; //seconds
    
    private $debug = true;
    
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
        } catch (Exception $e) {
            throw $e->getMessage();
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
    
    public function load_config(array $api_data)
    {
        $this->data['login'] = $api_data['login'];
        $this->data['tran_key'] = $api_data['password'];
        $this->data['ref_id'] = $api_data['ref_id'];
        $this->data['subscribtion_id'] = $api_data['subscribtion_id'];
        $this->data['amount'] = $api_data['total'];
        $this->data['id'] = (!empty($_SESSION['user_info']['id']) ? (int) $_SESSION['user_info']['id'] : $api_data['trans']);
        $this->data['trans'] = $api_data['trans'];
        $this->data['description'] = $api_data['description'];
        $this->data['full_name'] = $api_data['first_name'].' '.$api_data['last_name'];

        $this->data['interval_length'] = (!empty($api_data['interval_length'])     ? $api_data['interval_length']      : '1');
        $this->data['interval_unit'] = (!empty($api_data['interval_unit'])       ? $api_data['interval_unit']        : 'months');
        $this->data['start_date'] = date('Y-m-d', strtotime('+'.$api_data['interval_length'].' '.$this->data['interval_unit']));
        $this->data['total_occurrences'] = (!empty($api_data['total_occurrences'])   ? $api_data['total_occurrences']    : 9999);
        $this->data['trial_occurrences'] = (!empty($api_data['trial_occurrences'])   ?  $api_data['trial_occurrences']   : 0);
        $this->data['trial_amount'] = (!empty($api_data['trial_amount'])        ?  $api_data['trial_amount']        : 0);
        $this->data['cc_number'] = $api_data['cc_number'];
        $this->data['cc_exp_date'] = date('Y-m', strtotime($api_data['cc_year'].'-'.$api_data['cc_month'].'-1'));
        $this->data['cc_ccv'] = $api_data['cc_cvv'];

        $this->data['first_name'] = $api_data['first_name'];
        $this->data['last_name'] = $api_data['last_name'];
        $this->data['address'] = $api_data['address'];
        $this->data['city'] = $api_data['city'];
        $this->data['state'] = $api_data['state'];
        $this->data['zip'] = $api_data['zipcode'];
        $this->data['email'] = $api_data['email'];
        $this->data['phone'] = $api_data['phone'];

        $this->data['country'] = (!empty($api_data['country']))            ? $api_data['country']              : 'US';
        $this->data['ship_first_name'] = (!empty($api_data['ship_first_name'])     ? $api_data['ship_first_name']      : $this->data['first_name']);
        $this->data['ship_last_name'] = (!empty($api_data['ship_last_name'])      ? $api_data['ship_last_name']       : $this->data['last_name']);
        $this->data['ship_address'] = (!empty($api_data['ship_address'])        ? $api_data['ship_address']         : $this->data['address']);
        $this->data['ship_city'] = (!empty($api_data['ship_city'])           ? $api_data['ship_city']            : $this->data['city']);
        $this->data['ship_state'] = (!empty($api_data['ship_state'])          ? $api_data['ship_state']           : $this->data['state']);
        $this->data['ship_zip'] = (!empty($api_data['ship_zip'])            ? $api_data['ship_zip']             : $this->data['zip']);
        $this->data['ship_country'] = (!empty($api_data['ship_country'])        ? $api_data['ship_country']         : $this->data['country']);
    }
    
    public function get_gateway_url()
    {
        return (strtolower(_API_REQUEST_TYPE) == 'live') ? 'https://api.authorize.net/xml/v1/request.api' : 'https://apitest.authorize.net/xml/v1/request.api';
    }

    public function send_transaction()
    {
        if ($this->data['transaction_type'] == 'get_account_status') {
            $xml = $this->get_account_status();
        } elseif ($this->data['transaction_type'] == 'update_account') {
            $xml = $this->update_account();
        } elseif ($this->data['transaction_type'] == 'delete_account') {
            $xml = $this->delete_account();
        } else {
            $xml = $this->create_account();
        }

        if ($this->debug) {
            echo __METHOD__.' Sending: <pre>'.$xml.'</pre>';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->get_gateway_url());
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: text/xml"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90); // times out after 90 secs
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
        curl_setopt($ch, CURLOPT_POSTFIELDS, '<?xml version="1.0" encoding="utf-8"?>'.$xml); //adding XML data
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //verifies ssl certificate
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true); //forces closure of connection when done
        curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST
        # Godaddy proxy connection
        //curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        //curl_setopt ($ch, CURLOPT_PROXY,"http://proxy.shr.secureserver.net:3128");

        while ($i++ <= $this->gateway_retries) {
            $result = curl_exec($ch);
            $headers = curl_getinfo($ch);

            if (array_key_exists('http_code', $headers) && $headers['http_code'] != self::HTTP_RESPONSE_OK) {
                sleep($this->gateway_retry_wait); // Let's wait to see if its a temporary network issue.
            } else {
                break; // we got a good response, drop out of loop.
            }
        }

        return $this->parse_results($result);
    }
    
    public function response_handler($response_arr)
    {
        if ($this->debug) {
            echo __method__.' response='.print_r($response_arr, true).'';
            echo __method__.' RESULT='.$result_code.'';
        }
        if ($response_arr['result_code'] == 'Ok') {
            $trans_result = $response_arr;
            $trans_result['rb_answer'] = 'SUCCESS';
        } else {
            $trans_result['rb_answer'] = 'DENIED';
            $trans_result['reason'] = $response_arr['text'];
        }

        return $trans_result;
    }
    
    public function process()
    {
        return $this->response_handler($this->send_transaction());
    }
    
    public function get_account_status()
    {
        ob_start('ob_gzhandler'); ?>
        <ARBGetSubscriptionStatusRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
        <merchantAuthentication>
            <name><?=$this->data['login']?></name>
            <transactionKey><?=$this->data['tran_key']?></transactionKey>
        </merchantAuthentication>
        <refId><?=$this->data['ref_id']?></refId>
        <subscriptionId><?=$this->data['subscribtion_id']?></subscriptionId>
        </ARBGetSubscriptionStatusRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }

    public function create_account()
    {
        ob_start('ob_gzhandler'); ?>
        <ARBCreateSubscriptionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
        <merchantAuthentication>
            <name><?=$this->data['login']?></name>
            <transactionKey><?=$this->data['tran_key']?></transactionKey>
        </merchantAuthentication>
        <refId><?=$this->data['trans']?></refId>
        <subscription>
            <name><?=$this->data['full_name']?></name>
            <paymentSchedule>
                <interval>
                    <length><?=$this->data['interval_length']?></length>
                    <unit><?=$this->data['interval_unit']?></unit>
                </interval>
                <startDate><?=$this->data['start_date']?></startDate>
                <totalOccurrences><?=$this->data['total_occurrences']?></totalOccurrences>
                <trialOccurrences><?=$this->data['trial_occurrences']?></trialOccurrences>
            </paymentSchedule>
            <amount><?=$this->data['amount']?></amount>
            <trialAmount><?=$this->data['trial_amount']?></trialAmount>
            <payment>
                <creditCard>
                    <cardNumber><?=$this->data['cc_number']?></cardNumber>
                    <expirationDate><?=$this->data['cc_exp_date']?></expirationDate>
                    <cardCode><?=$this->data['cc_ccv']?></cardCode>
                </creditCard>
            </payment>
            <customer>
                <id><?=$this->data['id']?></id>
                <email><?=$this->data['email']?></email>
                <phoneNumber><?=$this->data['phone']?></phoneNumber>
            </customer>
            <billTo>
                <firstName><?=$this->data['first_name']?></firstName>
                <lastName><?=$this->data['last_name']?></lastName>
                <address><?=$this->data['address']?></address>
                <city><?=$this->data['city']?></city>
                <state><?=$this->data['state']?></state>
                <zip><?=$this->data['zip']?></zip>
                <country><?=$this->data['country']?></country>
            </billTo>
            <shipTo>
                <firstName><?=$this->data['ship_first_name']?></firstName>
                <lastName><?=$this->data['ship_last_name']?></lastName>
                <address><?=$this->data['ship_address']?></address>
                <city><?=$this->data['ship_city']?></city>
                <state><?=$this->data['ship_state']?></state>
                <zip><?=$this->data['ship_zip']?></zip>
                <country><?=$this->data['ship_country']?></country>
            </shipTo>
        </subscription>
        </ARBCreateSubscriptionRequest>

        <?php
        $xml = ob_get_clean();

        return $xml;
    }
    
    public function update_account()
    {
        ob_start('ob_gzhandler'); ?>
        <ARBUpdateSubscriptionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
        <merchantAuthentication>
            <name><?=$this->data['login']?></name>
            <transactionKey><?=$this->data['tran_key']?></transactionKey>
        </merchantAuthentication>
        <refId><?=$this->data['ref_id']?></refId>
        <subscriptionId><?=$this->data['subscribtion_id']?></subscriptionId>
        <subscription>
            <name><?=$this->data['full_name']?></name>
            <paymentSchedule>
                <interval>
                    <length><?=$this->data['interval_length']?></length>
                    <unit><?=$this->data['interval_unit']?></unit>
                </interval>
                <startDate><?=$this->data['start_date']?></startDate>
                <totalOccurrences><?=$this->data['total_occurrences']?></totalOccurrences>
                <trialOccurrences><?=$this->data['trial_occurrences']?></trialOccurrences>
            </paymentSchedule>
            <amount><?=$this->data['amount']?></amount>
            <trialAmount><?=$this->data['trial_amount']?></trialAmount>
            <customer>
                <id><?=$this->data['id']?></id>
                <phoneNumber><?=$this->data['phone']?></phoneNumber>
                <phoneNumber><?=$this->data['cc_ccv']?></phoneNumber>
            </customer>
            <payment>
                <creditCard>
                    <cardNumber><?=$this->data['cc_number']?></cardNumber>
                    <expirationDate><?=$this->data['cc_exp_date']?></expirationDate>
                    <cardCode><?=$this->data['cc_ccv']?></cardCode>
                </creditCard>
            </payment>
            <billTo>
                <firstName><?=$this->data['first_name']?></firstName>
                <lastName><?=$this->data['last_name']?></lastName>
                <address><?=$this->data['address']?></address>
                <city><?=$this->data['city']?></city>
                <state><?=$this->data['state']?></state>
                <zip><?=$this->data['zip']?></zip>
                <country><?=$this->data['country']?></country>
            </billTo>
        </subscription>
        </ARBUpdateSubscriptionRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }
    
    public function delete_account()
    {
        ob_start('ob_gzhandler'); ?>
        <ARBCancelSubscriptionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
        <merchantAuthentication>
            <name><?=$this->data['login']?></name>
            <transactionKey><?=$this->data['tran_key']?></transactionKey>
        </merchantAuthentication>
        <refId><?=$this->data['ref_id']?></refId>
        <subscriptionId><?=$this->data['subscribtion_id']?></subscriptionId>
        </ARBCancelSubscriptionRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }

    public function parse_results($response)
    {
        $return['result_code'] = $this->substring_between($response, '<resultCode>', '</resultCode>');
        $return['code'] = $this->substring_between($response, '<code>', '</code>');
        $return['text'] = $this->substring_between($response, '<text>', '</text>');
        $return['subscription'] = $this->substring_between($response, '<subscriptionId>', '</subscriptionId>');

        return $return;
    }

    public function substring_between($haystack, $start, $end)
    {
        if (strpos($haystack, $start) === false || strpos($haystack, $end) === false) {
            return false;
        }
         
        $start_position = strpos($haystack, $start) + strlen($start);
        $end_position = strpos($haystack, $end);

        return substr($haystack, $start_position, $end_position - $start_position);
    }
}
