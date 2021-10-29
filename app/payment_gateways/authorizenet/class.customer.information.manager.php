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
 * © 2002-2009 Web Design Enterprise Corp. All rights reserved.
 */

class Customer_Profile
{
    const HTTP_RESPONSE_OK = 200;
    
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
                $api_data['transaction_type'] = 'create_customer_profile';
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
        $this->data['transaction_type'] = $api_data['transaction_type'];
        $this->data['ref_id'] = $api_data['ref_id'];
        $this->data['profile_id'] = $api_data['profile_id'];
        $this->data['address_id'] = $api_data['address_id'];

        $this->data['full_name'] = $api_data['first_name'].' '.$api_data['last_name'];
        $this->data['cc_number'] = $api_data['cc_number'];
        $this->data['cc_exp_date'] = date('Y-m', strtotime($api_data['cc_year'].'-'.$api_data['cc_month'].'-1'));
        $this->data['cc_ccv'] = $api_data['cc_ccv'];

        $this->data['first_name'] = $api_data['first_name'];
        $this->data['last_name'] = $api_data['last_name'];
        $this->data['address'] = $api_data['address'];
        $this->data['city'] = $api_data['city'];
        $this->data['state'] = $api_data['state'];
        $this->data['zip'] = $api_data['zipcode'];
        $this->data['country'] = (!empty($api_data['country'])             ? $api_data['country']              : 'US');
    }
    
    public function get_gateway_url()
    {
        return (strtolower(_API_REQUEST_TYPE) == 'live') ? 'https://api.authorize.net/xml/v1/request.api' : 'https://apitest.authorize.net/xml/v1/request.api';
    }

    public function send_transaction()
    {
        if ($this->data['transaction_type'] == 'get_all_profiles') {
            $xml = $this->get_all_profiles();
        } elseif ($this->data['transaction_type'] == 'get_customer_profile') {
            $xml = $this->get_customer_profile();
        } elseif ($this->data['transaction_type'] == 'update_customer_profile') {
            $xml = $this->update_customer_profile();
        } elseif ($this->data['transaction_type'] == 'delete_customer_profile') {
            $xml = $this->delete_customer_profile();
        } elseif ($this->data['transaction_type'] == 'get_customer_payment_profile') {
            $xml = $this->get_customer_payment_profile();
        } elseif ($this->data['transaction_type'] == 'create_customer_payment_profile') {
            $xml = $this->create_customer_payment_profile();
        } elseif ($this->data['transaction_type'] == 'validate_customer_payment_profile') {
            $xml = $this->validate_customer_payment_profile();
        } elseif ($this->data['transaction_type'] == 'update_customer_payment_profile') {
            $xml = $this->update_customer_payment_profile();
        } elseif ($this->data['transaction_type'] == 'delete_customer_payment_profile') {
            $xml = $this->delete_customer_payment_profile();
        } elseif ($this->data['transaction_type'] == 'get_customer_shipping_address') {
            $xml = $this->get_customer_shipping_address();
        } elseif ($this->data['transaction_type'] == 'create_customer_shipping_address') {
            $xml = $this->create_customer_shipping_address();
        } elseif ($this->data['transaction_type'] == 'update_customer_shipping_address') {
            $xml = $this->update_customer_shipping_address();
        } elseif ($this->data['transaction_type'] == 'delete_customer_shipping_address') {
            $xml = $this->delete_customer_shipping_address();
        } elseif ($this->data['transaction_type'] == 'create_profile_from_transaction') {
            $xml = $this->create_profile_from_transaction();
        } else {
            $xml = $this->create_customer_profile();
        }

        if ($this->debug) {
            echo __method__.' Sending: <pre>'.$xml.'</pre>';
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
            $trans_result['answer'] = 'SUCCESS';
        } else {
            $trans_result['answer'] = 'DENIED';
            $trans_result['reason'] = $response_arr['text'];
        }

        return $trans_result;
    }
    
    public function process()
    {
        return $this->response_handler($this->send_transaction());
    }
    
    public function get_all_profiles()
    {
        ob_start('ob_gzhandler'); ?>
            <getCustomerProfileIdsRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">  
                <merchantAuthentication>
                    <name><?=$this->data['login']?></name>
                    <transactionKey><?=$this->data['tran_key']?></transactionKey>
                </merchantAuthentication>
            </getCustomerProfileIdsRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }
    public function create_customer_profile()
    {
        ob_start('ob_gzhandler'); ?>
            <createCustomerProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">  
                <merchantAuthentication>
                    <name><?=$this->data['login']?></name>
                    <transactionKey><?=$this->data['tran_key']?></transactionKey>
                </merchantAuthentication>
                <profile>
                    <merchantCustomerId><?=$this->data['subscribtion_id']?></merchantCustomerId>
                    <description><?=$this->data['description']?></description>
                    <email><?=$this->data['email']?></email>
                    <paymentProfiles>
                        <customerType>individual</customerType>
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
                            <phoneNumber><?=$this->data['phone']?></phoneNumber>
                        </billTo>
                    </paymentProfiles>
                </profile>
                <validationMode><?=(strtolower(_API_REQUEST_TYPE) == 'live') ? 'liveMode' : 'testMode'?></validationMode>
            </createCustomerProfileRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }

    public function get_customer_profile()
    {
        ob_start('ob_gzhandler'); ?>
            <getCustomerProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                <merchantAuthentication>
                    <name><?=$this->data['login']?></name>
                    <transactionKey><?=$this->data['tran_key']?></transactionKey>
                </merchantAuthentication>
                <customerProfileId><?=$this->data['ref_id']?></customerProfileId>
            </getCustomerProfileRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }
    
    public function update_customer_profile()
    {
        ob_start('ob_gzhandler'); ?>
            <updateCustomerProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                <merchantAuthentication>
                    <name><?=$this->data['login']?></name>
                    <transactionKey><?=$this->data['tran_key']?></transactionKey>
                </merchantAuthentication>
                <profile>
                    <merchantCustomerId><?=$this->data['subscribtion_id']?></merchantCustomerId>
                    <description><?=$this->data['description']?></description>
                    <email><?=$this->data['email']?></email>
                    <customerProfileId><?=$this->data['ref_id']?></customerProfileId>
                </profile>
            </updateCustomerProfileRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }
    
    public function delete_customer_profile()
    {
        ob_start('ob_gzhandler'); ?>
            <deleteCustomerProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                <merchantAuthentication>
                    <name><?=$this->data['login']?></name>
                    <transactionKey><?=$this->data['tran_key']?></transactionKey>
                </merchantAuthentication>
                <customerProfileId><?=$this->data['ref_id']?></customerProfileId>
            </deleteCustomerProfileRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }

    # Payments
    public function get_customer_payment_profile()
    {
        ob_start('ob_gzhandler'); ?>
            <getCustomerPaymentProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                <merchantAuthentication>
                    <name><?=$this->data['login']?></name>
                    <transactionKey><?=$this->data['tran_key']?></transactionKey>
                </merchantAuthentication>
                <customerProfileId><?=$this->data['ref_id']?></customerProfileId>
                <customerPaymentProfileId><?=$this->data['profile_id']?></customerPaymentProfileId>
            </getCustomerPaymentProfileRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }

    public function create_customer_payment_profile()
    {
        ob_start('ob_gzhandler'); ?>
            <createCustomerPaymentProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                <merchantAuthentication>
                    <name><?=$this->data['login']?></name>
                    <transactionKey><?=$this->data['tran_key']?></transactionKey>
                </merchantAuthentication>
                <customerProfileId><?=$this->data['ref_id']?></customerProfileId>
                <paymentProfile>
                    <billTo>
                        <firstName><?=$this->data['first_name']?></firstName>
                        <lastName><?=$this->data['last_name']?></lastName>
                        <address><?=$this->data['address']?></address>
                        <city><?=$this->data['city']?></city>
                        <state><?=$this->data['state']?></state>
                        <zip><?=$this->data['zip']?></zip>
                        <country><?=$this->data['country']?></country>
                        <phoneNumber><?=$this->data['phone']?></phoneNumber>
                    </billTo>
                    <payment>
                        <creditCard>
                            <cardNumber><?=$this->data['cc_number']?></cardNumber>
                            <expirationDate><?=$this->data['cc_exp_date']?></expirationDate>
                            <cardCode><?=$this->data['cc_ccv']?></cardCode>
                        </creditCard>
                    </payment>
                </paymentProfile>
                <validationMode><?=(strtolower(_API_REQUEST_TYPE) == 'live') ? 'liveMode' : 'testMode'?></validationMode>
            </createCustomerPaymentProfileRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }

    public function validate_customer_payment_profile()
    {
        ob_start('ob_gzhandler'); ?>
            <createCustomerPaymentProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                <merchantAuthentication>
                    <name><?=$this->data['login']?></name>
                    <transactionKey><?=$this->data['tran_key']?></transactionKey>
                </merchantAuthentication>
                <customerProfileId><?=$this->data['ref_id']?></customerProfileId>
                <customerPaymentProfileId><?=$this->data['profile_id']?></customerPaymentProfileId>
                <validationMode><?=(strtolower(_API_REQUEST_TYPE) == 'live') ? 'liveMode' : 'testMode'?></validationMode>
            </createCustomerPaymentProfileRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }

    public function update_customer_payment_profile()
    {
        ob_start('ob_gzhandler'); ?>
            <updateCustomerPaymentProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                <merchantAuthentication>
                    <name><?=$this->data['login']?></name>
                    <transactionKey><?=$this->data['tran_key']?></transactionKey>
                </merchantAuthentication>
                <customerProfileId><?=$this->data['ref_id']?></customerProfileId>
                <customerPaymentProfileId><?=$this->data['profile_id']?></customerPaymentProfileId>
                <paymentProfile>
                    <billTo>
                        <firstName><?=$this->data['first_name']?></firstName>
                        <lastName><?=$this->data['last_name']?></lastName>
                        <address><?=$this->data['address']?></address>
                        <city><?=$this->data['city']?></city>
                        <state><?=$this->data['state']?></state>
                        <zip><?=$this->data['zip']?></zip>
                        <country><?=$this->data['country']?></country>
                        <phoneNumber><?=$this->data['phone']?></phoneNumber>
                    </billTo>
                    <payment>
                        <creditCard>
                            <cardNumber><?=$this->data['cc_number']?></cardNumber>
                            <expirationDate><?=$this->data['cc_exp_date']?></expirationDate>
                            <cardCode><?=$this->data['cc_ccv']?></cardCode>
                        </creditCard>
                    </payment>
                </paymentProfile>
                <validationMode><?=(strtolower(_API_REQUEST_TYPE) == 'live') ? 'liveMode' : 'testMode'?></validationMode>
            </updateCustomerPaymentProfileRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }

    public function delete_customer_payment_profile()
    {
        ob_start('ob_gzhandler'); ?>
            <deleteCustomerPaymentProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                <merchantAuthentication>
                    <name><?=$this->data['login']?></name>
                    <transactionKey><?=$this->data['tran_key']?></transactionKey>
                </merchantAuthentication>
                <customerProfileId><?=$this->data['ref_id']?></customerProfileId>
                <customerPaymentProfileId><?=$this->data['profile_id']?></customerPaymentProfileId>
            </deleteCustomerPaymentProfileRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }

    # Shipping
    public function get_customer_shipping_address()
    {
        ob_start('ob_gzhandler'); ?>
            <getCustomerShippingAddressRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                <merchantAuthentication>
                    <name><?=$this->data['login']?></name>
                    <transactionKey><?=$this->data['tran_key']?></transactionKey>
                </merchantAuthentication>
                <customerProfileId><?=$this->data['ref_id']?></customerProfileId>
                <customerAddressId><?=$this->data['profile_id']?></customerAddressId>
            </getCustomerShippingAddressRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }

    public function create_customer_shipping_address()
    {
        ob_start('ob_gzhandler'); ?>
            <createCustomerShippingAddressRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                <merchantAuthentication>
                    <name><?=$this->data['login']?></name>
                    <transactionKey><?=$this->data['tran_key']?></transactionKey>
                </merchantAuthentication>
                <customerProfileId><?=$this->data['ref_id']?></customerProfileId>
                <address>
                    <firstName><?=$this->data['first_name']?></firstName>
                    <lastName><?=$this->data['last_name']?></lastName>
                    <address><?=$this->data['address']?></address>
                    <city><?=$this->data['city']?></city>
                    <state><?=$this->data['state']?></state>
                    <zip><?=$this->data['zip']?></zip>
                    <country><?=$this->data['country']?></country>
                    <phoneNumber><?=$this->data['phone']?></phoneNumber>
                </address>
            </createCustomerShippingAddressRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }

    public function update_customer_shipping_address()
    {
        ob_start('ob_gzhandler'); ?>
            <updateCustomerShippingAddressRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                <merchantAuthentication>
                    <name><?=$this->data['login']?></name>
                    <transactionKey><?=$this->data['tran_key']?></transactionKey>
                </merchantAuthentication>
                <customerProfileId><?=$this->data['ref_id']?></customerProfileId>
                <address>
                    <firstName><?=$this->data['first_name']?></firstName>
                    <lastName><?=$this->data['last_name']?></lastName>
                    <address><?=$this->data['address']?></address>
                    <city><?=$this->data['city']?></city>
                    <state><?=$this->data['state']?></state>
                    <zip><?=$this->data['zip']?></zip>
                    <country><?=$this->data['country']?></country>
                    <phoneNumber><?=$this->data['phone']?></phoneNumber>
                    <customerAddressId><?=$this->data['profile_id']?></customerAddressId>
                </address>
            </updateCustomerShippingAddressRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }

    public function delete_customer_shipping_address()
    {
        ob_start('ob_gzhandler'); ?>
            <deleteCustomerShippingAddressRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                <merchantAuthentication>
                    <name><?=$this->data['login']?></name>
                    <transactionKey><?=$this->data['tran_key']?></transactionKey>
                </merchantAuthentication>
                <customerProfileId><?=$this->data['ref_id']?></customerProfileId>
                <customerAddressId><?=$this->data['profile_id']?></customerAddressId>
            </deleteCustomerShippingAddressRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }

    public function create_profile_from_transaction()
    {
        ob_start('ob_gzhandler'); ?>
            <createCustomerProfileFromTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
                <merchantAuthentication>
                    <name><?=$this->data['login']?></name>
                    <transactionKey><?=$this->data['tran_key']?></transactionKey>
                </merchantAuthentication>
                <transId><?=$this->data['ref_id']?></transId>
            </createCustomerProfileFromTransactionRequest>
        <?php
        $xml = ob_get_clean();
        
        return $xml;
    }

    public function parse_results()
    {
        $return['result_code'] = $this->substring_between($this->response, '<resultCode>', '</resultCode>');
        $return['code'] = $this->substring_between($this->response, '<code>', '</code>');
        $return['text '] = $this->substring_between($this->response, '<text>', '</text>');
        $return['ref_id'] = $this->substring_between($this->response, '<customerProfileId>', '</customerProfileId>');
        $return['profile_id'] = $this->substring_between($this->response, '<customerPaymentProfileId>', '</customerPaymentProfileId>');
        $return['address_id'] = $this->substring_between($this->response, '<customerAddressId>', '</customerAddressId>');
        $return['direct_response'] = $this->substring_between($this->response, '<directResponse>', '</directResponse>');
        $return['validation_response'] = $this->substring_between($this->response, '<validationDirectResponse>', '</validationDirectResponse>');

        if (!empty($return['direct_response'])) {
            $return['direct_response'] = explode(',', $return['direct_response'])[3];
        }
        if (!empty($return['validation_response'])) {
            $return['validation_response'] = explode(',', $return['validation_response'])[3];
        }
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
