<?php

namespace App;

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

class Creditcards extends Format
{
    public function __construct()
    {
        $this->config = parent::get_config();
    }
    
    /**
     * collect and display all accepting credit cards types
     * @param  string $name   name of the input to be returned
     * @param  string $value any specific value assigned
     * @param  string $id    id of the input to be returned
     * @param  string $style style of the input to be returned
     * @param mixed $extra
     * @return string
     */
    public function get_cc_types($name, $value = '', $extra = [])
    {
        $return = '<select name="'.$name.'"';

        if (count($extra) > 0) {
            foreach ($extra as $name_info => $value_info) {
                $return .= ' '.$name_info.'="'.$value_info.'"';
            }
        }

        $return .= '>';

        if (!empty($this->config['we_accept_visa'])) {
            $return .= '<option value="visa"';
            if ($value == 'visa') {
                $return .= ' selected="selected"';
            }
            $return .= '>Visa</option>';
        }
        if (!empty($this->config['we_accept_mastercard'])) {
            $return .= '<option value="mastercard"';
            if ($value == 'mastercard') {
                $return .= ' selected="selected"';
            }
            $return .= '>Mastercard</option>';
        }
        if (!empty($this->config['we_accept_discover'])) {
            $return .= '<option value="discover"';
            if ($value == 'discover') {
                $return .= ' selected="selected"';
            }
            $return .= '>Discover</option>';
        }
        if (!empty($this->config['we_accept_amex'])) {
            $return .= '<option value="amex"';
            if ($value == 'amex') {
                $return .= ' selected="selected"';
            }
            $return .= '>Amex</option>';
        }

        $return .= '</select>';
        return $return;
    }
    /**
     * collect and display cards expiration months
     * @param  string $name   name of the input to be returned
     * @param  string $value any specific value assigned
     * @param  string $id    id of the input to be returned
     * @param  string $style style of the input to be returned
     * @param mixed $extra
     * @return string
     */
    public function get_cc_exp_month($name, $value = '', $extra = [])
    {
        $return = '<select name="'.$name.'"';

        if (count($extra) > 0) {
            foreach ($extra as $name_info => $value_info) {
                $return .= ' '.$name_info.'="'.$value_info.'"';
            }
        }

        $return .= '>';
        $return .= '<option value="">'._MONTH.'</option>';

        for ($m = 1; $m <= 12; $m++) {
            if ($m < 10) {
                $m = "0$m";
            }
            $return .= '<option value="'.$m.'"';
            if ($value == $m) {
                $return .= ' selected="selected"';
            }
            $return .= '>'.$m.'</option>';
        }

        $return .= '</select>';
        return $return;
    }
    /**
     * collect and display cards expiration years
     * @param  string $name   name of the input to be returned
     * @param  string $value any specific value assigned
     * @param  string $id    id of the input to be returned
     * @param  string $style style of the input to be returned
     * @param mixed $extra
     * @return string
     */
    public function get_cc_exp_year($name, $value = '', $extra = [])
    {
        $thisYear = date('Y');
        $return = '<select name="'.$name.'"';
        if (count($extra) > 0) {
            foreach ($extra as $name_info => $value_info) {
                $return .= ' '.$name_info.'="'.$value_info.'"';
            }
        }
        
        $return .= '>';
        $return .= '<option value="">'._YEAR.'</option>';

        for ($y = $thisYear; $y < $thisYear + 10; $y++) {
            $return .= '<option value="'.$y.'"';
            if ($value == $y) {
                $return .= ' selected="selected"';
            }
            $return .= '>'.$y.'</option>';
        }
        $return .= '</select>';
        return $return;
    }
    /**
     * Get credit card type from cart number
     * @param  string   card number
     * @param mixed $ccnum
     * @return string   card type
     */
    public function get_card_type($ccnum)
    {
        $ccnum = $this->int($ccnum);

        if (preg_match('/^3[47][0-9]{13}$/', $ccnum)) {
            return 'amex';
        } elseif (preg_match('/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/', $ccnum)) {
            return 'Diners Club';
        } elseif (preg_match('/^6(?:011|5[0-9][0-9])[0-9]{12}$/', $ccnum)) {
            return 'discover';
        } elseif (preg_match('/^(?:2131|1800|35\d{3})\d{11}$/', $ccnum)) {
            return 'jcb';
        } elseif (preg_match('/^5[1-5][0-9]{14}$/', $ccnum)) {
            return 'mastercard';
        } elseif (preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/', $ccnum)) {
            return 'visa';
        }
        
        return 'unknown';
    }
    /**
     * validates credit card number based on submitted type
     * @param  int $ccnum
     * @param  string $type
     * @return boolean
     */
    public function valid_cc_number($ccnum)
    {
        #Clean  up  input
        $ccnum = $this->int($ccnum);
        $type = strtolower($this->get_card_type($ccnum));

        if ($type == 'unknown') {
        } elseif ($type == 'mastercard') {
            if (strlen($ccnum) != 16 || !preg_match('/^5[1-5]/', $ccnum)) {
                return false;
            }
        } elseif ($type == 'visa') {
            if ((strlen($ccnum) != 13 && strlen($ccnum) != 16) || substr($ccnum, 0, 1) != '4') {
                return false;
            }
        } elseif ($type == 'amex') {
            if (strlen($ccnum) != 15 || !preg_match('/^3[47]/', $ccnum)) {
                return false;
            }
        } elseif ($type == 'discover') {
            if (strlen($ccnum) != 16 || substr($ccnum, 0, 4) != '6011') {
                return false;
            }
        } else {
            return false;
        }

        #Start  mod  10  checks
        $dig = $this->to_char_array($ccnum);
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

        if (substr($validate, -1, 1) == '0') {
            return true;
        }
         
        return false;
    }
    /**
     * validates credit card expiration date
     * @param  int $month
     * @param  int $year
     * @return boolean
     */
    public function valid_cc_date($month, $year)
    {
        $month = preg_replace("/[^0-9]/", '', $month);
        $year = preg_replace("/[^0-9]/", '', $year);

        if ($year >= date('Y') && $year < date('Y', time() + 10 * 365 * 24 * 60 * 60)) {
            return true;
        }
        return false;
    }
    /**
     * validates credit card CVV number
     * @param  int $num
     * @return boolean
     */
    public function valid_cc_cvv($num)
    {
        $cc = preg_replace("/[^0-9]/", '', $num);

        if (strlen($cc) >= 3 && strlen($cc) <= 4) {
            return true;
        }
        
        return false;
    }

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
}
