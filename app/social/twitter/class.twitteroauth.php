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

require_once('class.OAuth.php');

class Kernel_Classes_Social_Twitter_Twitteroauth
{
    public $http_code;
    public $url;
    public $host = "https://api.twitter.com/1.1/";
    public $timeout = 30;
    public $connecttimeout = 30;
    public $ssl_verifypeer = false;
    public $format = 'json';
    public $decode_json = true;
    public $http_info;
    public $useragent = 'TwitterOAuth v0.2.0-beta2';

    public function __construct($consumer_key, $consumer_secret, $oauth_token = null, $oauth_token_secret = null)
    {
        $this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
        $this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);

        if (!empty($oauth_token) && !empty($oauth_token_secret)) {
            $this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
        } else {
            $this->token = null;
        }
    }

    public function accessTokenURL()
    {
        return 'https://api.twitter.com/oauth/access_token';
    }

    public function authenticateURL()
    {
        return 'https://twitter.com/oauth/authenticate';
    }

    public function authorizeURL()
    {
        return 'https://twitter.com/oauth/authorize';
    }

    public function requestTokenURL()
    {
        return 'https://api.twitter.com/oauth/request_token';
    }

    public function lastStatusCode()
    {
        return $this->http_status;
    }

    public function lastAPICall()
    {
        return $this->last_api_call;
    }

    public function getRequestToken($oauth_callback = null)
    {
        $parameters = [];

        if (!empty($oauth_callback)) {
            $parameters['oauth_callback'] = $oauth_callback;
        }

        $request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters);
        $token = OAuthUtil::parse_parameters($request);
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }

    public function getAuthorizeURL($token, $sign_in_with_twitter = true)
    {
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }

        if (empty($sign_in_with_twitter)) {
            return $this->authorizeURL()."?oauth_token={$token}";
        }
         
        return $this->authenticateURL()."?oauth_token={$token}";
    }

    public function getAccessToken($oauth_verifier = false)
    {
        $parameters = [];

        if (!empty($oauth_verifier)) {
            $parameters['oauth_verifier'] = $oauth_verifier;
        }

        $request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters);
        $token = OAuthUtil::parse_parameters($request);
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }

    public function getXAuthToken($username, $password)
    {
        $parameters = [];
        $parameters['x_auth_username'] = $username;
        $parameters['x_auth_password'] = $password;
        $parameters['x_auth_mode'] = 'client_auth';
        $request = $this->oAuthRequest($this->accessTokenURL(), 'POST', $parameters);
        $token = OAuthUtil::parse_parameters($request);
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);

        return $token;
    }

    public function get($url, $parameters = [])
    {
        $response = $this->oAuthRequest($url, 'GET', $parameters);

        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response);
        }
        return $response;
    }

    public function post($url, $parameters = [])
    {
        $response = $this->oAuthRequest($url, 'POST', $parameters);

        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response);
        }
        return $response;
    }

    public function delete($url, $parameters = [])
    {
        $response = $this->oAuthRequest($url, 'DELETE', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response);
        }
        return $response;
    }

    public function oAuthRequest($url, $method, $parameters)
    {
        if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
            $url = "{$this->host}{$url}.{$this->format}";
        }
        $request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);

        $request->sign_request($this->sha1_method, $this->consumer, $this->token);
        switch ($method) {
            case 'GET':

                return $this->http($request->to_url(), 'GET');

            break;

            default:

                return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());

            break;
        }
    }

    public function http($url, $method, $postfields = null)
    {
        $this->http_info = [];
        $ci = curl_init();
        
        /* Curl settings */
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_HTTPHEADER, ['Expect:']);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, [$this, 'getHeader']);
        curl_setopt($ci, CURLOPT_HEADER, false);
        
        switch ($method) {
            case 'POST':

                curl_setopt($ci, CURLOPT_POST, true);

                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                }

            break;

            case 'DELETE':

                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');

                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }

            break;
        }
        
        curl_setopt($ci, CURLOPT_URL, $url);
        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;
        curl_close($ci);

        return $response;
    }

    public function getHeader($ch, $header)
    {
        $i = strpos($header, ':');

        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }

        return strlen($header);
    }
}
