<?php

namespace App;

use ArrayIterator;

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

class Validator
{
    public $errors = [];
    public $sanitized = [];
    private $validation_rules = [];
    private $source = [];

    public function __construct()
    {
    }
    /**
    * @add the source
    * @paccess public
    * @param array $source
    * @param mixed $trim
    */
    public function add_source($source, $trim = false)
    {
        $this->source = $source;
    }
    /**
    * @run the validation rules
    * @access public
    */
    public function run()
    {
        foreach (new ArrayIterator($this->validation_rules) as $var => $opt) {
            if ($opt['required'] == true) {
                $this->is_set($var);
            }

            if (array_key_exists('trim', $opt) && $opt['trim'] == true) {
                $this->source[$var] = trim($this->source[$var]);
            }

            switch ($opt['type']) {
                case 'email':

                    $this->validate_email($var, $opt['msg'], $opt['required']);
        
                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitize_email($var);
                    }
                
                break;

                case 'url':

                    $this->validate_url($var, $opt['msg']);

                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitize_url($var);
                    }
                
                break;

                case 'numeric':

                    $this->validate_numeric($var, $opt['msg'], $opt['min'], $opt['max'], $opt['required']);

                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitize_numeric($var);
                    }
                
                break;

                case 'string':

                    $this->validate_string($var, $opt['msg'], $opt['min'], $opt['max'], $opt['required']);

                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitize_string($var);
                    }
            
                break;

                case 'float':

                    $this->validate_float($var, $opt['msg'], $opt['required']);

                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitize_float($var);
                    }
                
                break;

                case 'ipv4':

                    $this->validate_ipv4($var, $opt['msg'], $opt['required']);

                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitize_ipv4($var);
                    }
                
                break;

                case 'ipv6':

                    $this->validate_ipv6($var, $opt['msg'], $opt['required']);

                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitize_ipv6($var);
                    }
                
                break;

                case 'bool':

                    $this->validate_bool($var, $opt['msg'], $opt['required']);

                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitized[$var] = (bool) $this->source[$var];
                    }
                
                break;

                case 'name':

                    $this->validate_name($var, $opt['msg'], $opt['required']);

                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitize_string[$var] = (string) $this->source[$var];
                    }
                
                break;

                case 'address':

                    $this->validate_address($var, $opt['msg'], $opt['required']);

                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitize_string[$var] = (string) $this->source[$var];
                    }
                
                break;

                case 'date':

                    $this->validate_date($var, $opt['msg'], $opt['required']);

                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitize_string[$var] = $this->source[$var];
                    }
                
                break;

                case 'age':

                    $this->validate_age($var, $opt['msg'], $opt['required']);

                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitize_string[$var] = $this->source[$var];
                    }
                
                break;

                case 'cc_number':

                    $this->validate_cc_number($var, $opt['msg'], $opt['required']);

                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitized[$var] = $this->source[$var];
                    }
                
                break;

                case 'cc_date':

                    $this->validate_cc_expiration_date($var, $opt['msg']);

                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitized[$var] = $this->source[$var];
                    }
                
                break;

                case 'amount':

                    $this->validate_amount($var, $opt['msg'], $opt['required']);

                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitize_amount[$var] = $this->source[$var];
                    }

                break;

                case 'phone':

                    $this->validate_phone($var, $opt['msg'], $opt['required']);

                    if (!array_key_exists($var, $this->errors)) {
                        $this->sanitize_phone[$var] = $this->source[$var];
                    }

                break;

                case 'set':

                    $this->validate_set($var, $opt['msg'], $opt['required'], $opt['compare']);

                break;
            }
        }
    }
    /**
    * @add a rule to the validation rules array
    * @access public
    * @param string $varname The variable name
    * @param string $type The type of variable
    * @param boolean $required If the field is required
    * @param int $min The minimum length or range
    * @param int $max the maximum length or range
    * @param mixed $trim
    */
    public function add_rule($varname, $type, $required = false, $min = 0, $max = 0, $trim = false)
    {
        $this->validation_rules[$varname] = ['type' => $type, 'required' => $required, 'min' => $min, 'max' => $max, 'trim' => $trim];

        return $this;
    }
    /**
    * @add multiple rules to teh validation rules array
    * @access public
    * @param array $rules_array The array of rules to add
    */
    public function add_rules(array $rules_array)
    {
        $this->validation_rules = array_merge($this->validation_rules, $rules_array);
    }
    /**
    * @validate an ipv6 IP address
    * @param string $var The variable name
    * @param  string  $message  error message to return
    * @param boolean $required
    */
    public function validate_ipv6($var, $message = '', $required = false)
    {
        if ($required == false && strlen($this->source[$var]) == 0) {
            return true;
        }
        if (filter_var($this->source[$var], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            $this->errors[$var] = $message;
        }
    }
    /**
     * validates credit card number based on submitted type
     * @param string $var the variable name
     * @param string  $message  error message to return
     */
    public function validate_cc_number($var, $message = '')
    {
        $number = preg_replace("/[^0-9]/", '', $this->source[$var]);
        $type = strtolower($this->get_card_type($number));

        if ($type == 'unknown') {
        } elseif ($type == 'mastercard') {
            if (strlen($number) != 16 || !preg_match('/^5[1-5]/', $number)) {
                $this->errors[$var] = $message;
            }
        } elseif ($type == 'visa') {
            if ((strlen($number) != 13 && strlen($number) != 16) || substr($number, 0, 1) != '4') {
                $this->errors[$var] = $message;
            }
        } elseif ($type == 'amex') {
            if (strlen($number) != 15 || !preg_match('/^3[47]/', $number)) {
                $this->errors[$var] = $message;
            }
        } elseif ($type == 'discover') {
            if (strlen($number) != 16 || substr($number, 0, 4) != '6011') {
                $this->errors[$var] = $message;
            }
        } else {
            $this->errors[$var] = $message;
        }

        $dig = $this->to_char_array($number);
        $numdig = sizeof($dig);
        $j = 0;

        for ($i = ($numdig - 2); $i >= 0; $i -= 2) {
            $dbl[$j] = $dig[$i] * 2;
            $j++;
        }

        $dblsz = sizeof($dbl);
        $validate = 0;

        for ($i = 0; $i < $dblsz; $i++) {
            $add = $this->to_char_array($dbl[$i]);

            for ($j = 0; $j < sizeof($add); $j++) {
                $validate += $add[$j];
            }

            $add = '';
        }
        
        for ($i = ($numdig - 1); $i >= 0; $i -= 2) {
            $validate += $dig[$i];
        }

        if (substr($validate, -1, 1) != '0') {
            $this->errors[$var] = $message;
        }
    }
    /**
     * Get credit card type from cart number
     * @param  string   card number
     * @param mixed $_number
     * @return string   card type
     */
    public function get_card_type($_number)
    {
        if (preg_match('/^3[47][0-9]{13}$/', $_number)) {
            return 'amex';
        } elseif (preg_match('/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/', $_number)) {
            return 'dinersclub';
        } elseif (preg_match('/^6(?:011|5[0-9][0-9])[0-9]{12}$/', $_number)) {
            return 'discover';
        } elseif (preg_match('/^(?:2131|1800|35\d{3})\d{11}$/', $_number)) {
            return 'jcb';
        } elseif (preg_match('/^5[1-5][0-9]{14}$/', $_number)) {
            return 'mastercard';
        } elseif (preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/', $_number)) {
            return 'visa';
        }
        
        return 'unknown';
    }
    # Sanitizing Methods
    /**
    * @santize and email
    * @param string $var The variable name
    * @return string
    */
    public function sanitize_email($var)
    {
        $email = preg_replace('((?:\n|\r|\t|%0A|%0D|%08|%09)+)i', '', $this->source[$var]);
        $this->sanitized[$var] = (string) filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    # Helper methods
    #
    /**
     * returns  an  array  of  characters given in an string
     * @param  string $input
     * @return array
     */
    public function to_char_array($input)
    {
        $len = strlen($input);

        for ($j = 0; $j < $len; $j++) {
            $char[$j] = substr($input, $j, 1);
        }

        return ($char);
    }
    /**
    * @Check if POST variable is set
    * @param string $var The POST variable to check
    * @param mixed $message
    */
    private function is_set($var, $message = '')
    {
        if (!isset($this->source[$var])) {
            $this->errors[$var] = $message;
        }
    }
    /**
    * @validate an ipv4 IP address
    * @param string $var The variable name
    * @param  string  $message  error message to return
    * @param boolean $required
    */
    private function validate_ipv4($var, $message = '', $required = false)
    {
        if ($required == false && strlen($this->source[$var]) == 0) {
            return true;
        }
        if (filter_var($this->source[$var], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            $this->errors[$var] = $message;
        }
    }
    /**
    * @validate a floating point number
    * @param $var The variable name
    * @param  string  $message  error message to return
    * @param boolean $required
    */
    private function validate_float($var, $message = '', $required = false)
    {
        if ($required == false && strlen($this->source[$var]) == 0) {
            return true;
        }
        if (filter_var($this->source[$var], FILTER_VALIDATE_FLOAT) === false) {
            $this->errors[$var] = $message;
        }
    }
    /**
    * @validate a string
    * @param string $var The variable name
    * @param string  $message  error message to return
    * @param int $min the minimum string length
    * @param int $max The maximum string length
    * @param boolean $required
    */
    private function validate_string($var, $message = '', $min = 0, $max = 0, $required = false)
    {
        if ($required == false && strlen($this->source[$var]) == 0) {
            return true;
        }
        if (isset($this->source[$var])) {
            if ((!empty($min) && strlen($this->source[$var]) < $min) || (!empty($max) && strlen($this->source[$var]) > $max) || (!is_string($this->source[$var]))) {
                $this->errors[$var] = $message;
            }
        }
    }
    /**
    * @validate an number
    * @param string $var The variable name
    * @param string  $message  error message to return
    * @param boolean $required
    * @param mixed $min
    * @param mixed $max
    */
    private function validate_numeric($var, $message = '', $min = 0, $max = 0, $required = false)
    {
        if ($required == false && strlen($this->source[$var]) == 0) {
            return true;
        }

        $number = preg_replace("/[^0-9]/", '', $this->source[$var]);

        if (empty($this->source[$var]) || (!empty($min) && strlen($this->source[$var]) < $min) || (!empty($max) && strlen($this->source[$var]) > $max)) {
            $this->errors[$var] = $message;
        }
    }
    /**
    * Determine if a URL exists & is accessible
    * @param string $var The variable name
    * @param string  $message  error message to return
    * @param boolean $required
    */
    private function validate_url($var, $message = '', $required = false)
    {
        if ($required == false && strlen($this->source[$var]) == 0) {
            return true;
        }
        
        if (filter_var($this->source[$var], FILTER_VALIDATE_URL) === false) {
            $this->errors[$var] = $message;
        }

        $url = parse_url(strtolower($this->source[$var]));
        
        if (isset($url['host'])) {
            $url = $url['host'];
        }

        if (function_exists('checkdnsrr')) {
            if (checkdnsrr($url) === false) {
                $this->errors[$var] = $message;
            }
        } else {
            if (gethostbyname($url) == $url) {
                $this->errors[$var] = $message;
            }
        }
    }
    /**
    * @validate an email address
    * @param string $var The variable name
    * @param string  $message  error message to return
    * @param boolean $required
    */
    private function validate_email($var, $message = '', $required = false)
    {
        if ($required == false && strlen($this->source[$var]) == 0) {
            return true;
        }

        if (filter_var($this->source[$var], FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[$var] = $message;
        }

        if (!empty($this->source[$var])) {
            list($user, $domain) = explode("@", $this->source[$var]);
            if (!checkdnsrr($domain, 'MX') && checkdnsrr($domain, 'A')) {
                $this->errors[$var] = $message;
            }
        }
    }
    /**
     * @validate a boolean
    * @param string $var the variable name
    * @param string  $message  error message to return
    * @param boolean $required
    */
    private function validate_bool($var, $message = '', $required = false)
    {
        if ($required == false && strlen($this->source[$var]) == 0) {
            return true;
        }

        if (empty($this->source[$var])) {
            $this->errors[$var] = $message;
        }
    }
    /**
     * try to determinate if value is a valid human name
     * @param string $var the variable name
     * @param string  $message  error message to return
     * @param boolean $required checks is value is required
     */
    private function validate_name($var, $message = '', $required = false)
    {
        if ($required == false && strlen($this->source[$var]) == 0) {
            return true;
        }

        if (!preg_match("/^([a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïñðòóôõöùúûüýÿ '-])+$/i", $this->source[$var]) !== false) {
            $this->errors[$var] = $message;
        }
    }
    private function validate_amount()
    {
        if ($required == false && strlen($this->source[$var]) == 0) {
            return true;
        }

        if (!preg_match("/[^0-9.]/", $this->source[$var]) !== false) {
            $this->errors[$var] = $message;
        }
    }
    private function validate_phone()
    {
        if ($required == false && strlen($this->source[$var]) == 0) {
            return true;
        }

        if (!preg_match("/[^0-9.()-]/", $this->source[$var]) !== false) {
            $this->errors[$var] = $message;
        }
    }
    /**
     * try to determinate if value is a street address using weak detection
     * @param  string $var the variable name
     * @param  string  $message  error message to return
     * @param  boolean $required checks is value is required
     */
    private function validate_address($var, $message = '', $required = false)
    {
        if ($required == false && strlen($this->source[$var]) == 0) {
            return true;
        }

        $hasLetter = preg_match('/[a-zA-Z]/', $this->source[$var]);
        $hasDigit = preg_match('/\d/', $this->source[$var]);
        $hasSpace = preg_match('/\s/', $this->source[$var]);

        $passes = $hasLetter && $hasDigit && $hasSpace;

        if (!$passes) {
            $this->errors[$var] = $message;
        }
    }
    /**
     * Determine if the provided input is a valid date
     * @param string $var the variable name
     * @param string  $message  error message to return
     * @param boolean $required checks is value is required
     * @param mixed $compare
     */
    private function validate_set($var, $message = '', $required = false, $compare = [])
    {
        if ($required == false && strlen($this->source[$var]) == 0) {
            return true;
        }

        if (!in_array($this->source[$var], $compare)) {
            $this->errors[$var] = $message;
        }
    }
    /**
     * Determine if the provided input is a valid date
     * @param string $var the variable name
     * @param string  $message  error message to return
     * @param boolean $required checks is value is required
     */
    private function validate_date($var, $message = '', $required = false)
    {
        if ($required == false && strlen($this->source[$var]) == 0) {
            return true;
        }

        $short_date = date('Y-m-d', strtotime($this->source[$var]));
        $long_date = date('Y-m-d H:i:s', strtotime($this->source[$var]));

        if ($short_date != $this->source[$var] && $long_date != $this->source[$var]) {
            $this->errors[$var] = $message;
        }
    }
    /**
     * Determine if the provided bday is a valid age
     * @param string $var the variable name
     * @param string  $message  error message to return
     * @param boolean $required checks is value is required
     */
    private function validate_age($var, $message = '', $required = false)
    {
        if ($required == false && strlen($this->source[$var]) == 0) {
            return true;
        }

        $bday = date('Y-m-d', strtotime($this->source[$var]));

        if ($bday > date('Y-m-d', strtotime(time() - (18 * 365 * 24 * 60 * 60)))) {
            $this->errors[$var] = $message;
        }
    }
    /**
     * Determine if the provided input is a valid date
     * @param string $var the variable name
     * @param string  $message  error message to return
     * @param boolean $required checks is value is required
     */
    private function validate_cc_expiration_date($var, $message = '')
    {
        $range['min'] = time();
        $range['max'] = strtotime('+10 years');

        if (strtotime($this->source[$var].'-30') < $range['min'] || strtotime($this->source[$var].'-30') > $range['max']) {
            $this->errors[$var] = $message;
        }
    }
    /**
    * @sanitize a url
    * @param string $var The variable name
    */
    private function sanitize_url($var)
    {
        $this->sanitized[$var] = (string) filter_var($this->source[$var], FILTER_SANITIZE_URL);
    }
    /**
     * @sanitize a numeric value
     * @param string $var The variable name
     */
    private function sanitize_numeric($var)
    {
        $this->sanitized[$var] = (int) filter_var($this->source[$var], FILTER_SANITIZE_NUMBER_INT);
    }
    /**
     * @sanitize a float
     * @param float $var The variable name
     */
    private function sanitize_float($var)
    {
        $this->sanitized[$var] = (double) number_format(preg_replace("/[^0-9.]/", '', $this->source[$var]), 2, '.', '');
    }
    /**
     * @sanitize a string
     * @param string $var The variable name
     */
    private function sanitize_string($var)
    {
        $this->sanitized[$var] = (string) filter_var($this->source[$var], FILTER_SANITIZE_STRING);
    }
    /**
     * @sanitize amount
     * @param string $var The variable name
     */
    private function sanitize_amount($var)
    {
        $this->sanitized[$var] = (double) number_format((double) preg_replace("/[^0-9.]/", '', $this->source[$var]), 2, _DECIMAL_PUNCTUATION, '');
    }
    /**
     * @sanitize phone number
     * @param string $var The variable name
     */
    private function sanitize_phone($var)
    {
        $this->sanitized[$var] = preg_replace('~(\d{3})[^\d]*(\d{3})[^\d]*(\d{4})$~', '$1.$2.$3', preg_replace("/[^0-9]/", "", $this->source[$var]));
    }
}
