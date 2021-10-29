<?php

namespace App\Payments;

use \Exception as Exception;
use Omnipay\Omnipay;

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

class Payment
{
    public $return;
    private $api_data = [];
    
    public function __construct(array $api_data)
    {
        try {
            $this->api_data = $api_data;

            if (count($this->api_data) == 0) {
                throw new Exception(_EMPTY_DATA);
            }

            if ($this->api_data['transaction_type'] == 'REFUND') {
                if (empty($this->api_data['trans_id'])) {
                    throw new Exception('A transaction id must be set in order to REFUND this amount');
                }
            }
            if (empty($this->api_data['login'])) {
                throw new Exception('You must set your API Login ID');
            }
            if (empty($this->api_data['password'])) {
                throw new Exception('You must set your API Password');
            }
            if (empty($this->api_data['total'])) {
                throw new Exception(_ERROR_INVALID_AMOUNT);
            }
            if (empty($this->api_data['cc_number'])) {
                throw new Exception(_ERROR_INVALID_CARD_NUMBER);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function process()
    {
        try {
            $gateway = Omnipay::create('FirstData_Connect');
            
            $gateway->initialize([
                'storeId' => $this->api_data['login'],
                'sharedSecret' => $this->api_data['password'],
                'testMode' => _API_TEST_MODE
            ]);

            if ($this->api_data['transaction_type'] == 'REFUND') {
                $response = $gateway->refund([
                    'transactionReference' => $this->api_data['trans_id'],
                    'amount' => $this->api_data['total']
                ])->send();
            } else {
                $form_data = [
                    'type' => $this->api_data['cc_type'],
                    'number' => $this->api_data['cc_number'],
                    'expiryMonth' => $this->api_data['cc_month'],
                    'expiryYear' => $this->api_data['cc_year'],
                    'cvv' => $this->api_data['cc_cvv'],
                    'firstName' => $this->api_data['first_name'],
                    'lastName' => $this->api_data['last_name'],
                    'billingAddress1' => $this->api_data['address'].' '.$this->api_data['address2'],
                    'billingCity' => $this->api_data['city'],
                    'billingState' => $this->api_data['state'],
                    'billingPostcode' => $this->api_data['zipcode'],
                    'billingCountry' => (!empty($this->api_data['country']))        ? $this->api_data['country']          : 'US', // US (default)
                    'shippingAddress1' => $this->api_data['ship_address'].' '.$this->api_data['ship_address2'],
                    'shippingCity' => $this->api_data['ship_city'],
                    'shippingState' => $this->api_data['ship_state'],
                    'shippingPostcode' => $this->api_data['ship_zipcode'],
                    'shippingCountry' => (!empty($this->api_data['ship_country']))   ? $this->api_data['ship_country']     : 'US', // US (default)
                    'email' => $this->api_data['email'],
                    'phone' => $this->api_data['phone']
                ];

                if (!empty($this->api_data['items'])) {
                    foreach ($this->api_data['items'] as $key => $value) {
                        $items_info[] = [
                            'name' => $value['item_info']['title'],
                            'quantity' => $value['qty'],
                            'price' => $value['price'],
                            'description' => $value['variation_text'],
                            'number' => $value['sku']
                        ];
                    }
                }

                // Send purchase request
                $response = $gateway->completePurchase([
                    'capture' => 'true',
                    'amount' => $this->api_data['total'],
                    'transactionId' => $this->api_data['trans'],
                    'shippingAmount' => $this->api_data['shipping'],
                    'taxAmount' => $this->api_data['tax'],
                    'currency' => (!empty($this->api_data['currency']))        ? $this->api_data['currency']          : 'USD', // USD (default)
                    'description' => 'Ref:'.$this->api_data['trans'],
                    'card' => $form_data
                ])->setItems($items_info)->send();
            }

            // Process response
            if ($response->isSuccessful()) {
                $this->return['answer'] = 'SUCCESS';
                $this->return['trans_id'] = $response->getTransactionReference();
            } elseif ($response->isRedirect()) {
                $response->redirect();
            } else {
                $this->return['answer'] = 'DENIED';
                $this->return['reason'] = $response->getMessage();
            }

            return $this->return;
        } catch (exception $e) {
            throw $e->getMessage();
        }
    }
}
