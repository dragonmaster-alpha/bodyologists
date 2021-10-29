<?php

namespace App\Security;

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

class Checkspam
{
    // list of checkers available for individual checking
    private $_aCheckers = [
        'spamhaus' => ['.zen.spamhaus.org', true],     //available for group checking with 'all' key
        'spamcop' => ['.bl.spamcop.net',   true],    //available for group checking with 'all' key
        'dsbl' => ['.list.dsbl.org',    false],    //not available for group checking with 'all' key
        'ordb' => ['.relays.ordb.org',  false],    //not available for group checking with 'all' key
        'sorbs' => ['.dnsbl.sorbs.net',  false],    //not available for group checking with 'all' key
        'njabl' => ['.dnsbl.njabl.org',  false]    //not available for group checking with 'all' key
    ];

    /**
     * check IP for spam in checkers : given, default or all available for group checking (may be slow)
     * @param string $ip      ip address
     * @param string $checker checker name or 'all' or nothing
     */
    public function CheckSpamIP($ip = '', $checker = 'spamhaus')
    {
        if (empty($ip)) {
            return false;
        }
        if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip) != 1) {
            return false;
        }

        $octets = explode('.', $ip);

        if ($octets[0] == '127') {
            return false;
        }
        if ($octets[0] == '10') {
            return false;
        }
        if ($octets[0] == '192' && $octets[0] == '168') {
            return false;
        }
        if ($octets[0] == '169' && $octets[0] == '254') {
            return false;
        }
        if ((int) $octets[0] > 255 || (int) $octets[1] > 255 || (int) $octets[2] > 255 || (int) $octets[3] > 255) {
            return false;
        }

        $ret_val = false;
        $PTR = implode(array_reverse($octets), '.');
        if ($checker === 'all') {
            foreach (array_values($this->_aCheckers) as $c) {
                if ($c[1]) {
                    $ret_val = $ret_val || $this->_CheckDNSAnswer(dns_get_record($PTR.$c[0], DNS_A));
                }
                if ($ret_val) {
                    break;
                }
            }
        } elseif (array_key_exists($checker, $this->_aCheckers)) {
            $ret_val = $this->_CheckDNSAnswer(dns_get_record($PTR.$this->_aCheckers[$checker][0], DNS_A));
        } else {
            $ret_val = $this->_CheckDNSAnswer(dns_get_record($PTR.$this->_aCheckers[$this->_sDefaultChecker][0], DNS_A));
        }

        return $ret_val;
    }

    /**
     * gets list of available checker names
     */
    public function GetCheckers()
    {
        return array_keys($this->_aCheckers);
    }

    /**
     * gets list of checker names available for group checking with 'all' key
     */
    public function GetGroupCheckers()
    {
        $ret_val = [];

        foreach (array_keys($this->_aCheckers) as $k) {
            if ($this->_aCheckers[$k][1]) {
                array_push($ret_val, $k);
            }
        }
        return $ret_val;
    }

    /**
     * gets default checker name
     */
    public function GetDefaultChecker()
    {
        return $this->_sDefaultChecker;
    }

    /**
     * sets default checker name
     * @param string $new_checker new default checker nam
     */
    public function SetDefaultChecker($new_checker)
    {
        if (array_key_exists($new_checker, $this->_aCheckers)) {
            $this->_sDefaultChecker = $new_checker;
            return true;
        }
        
        return false;
    }

    /**
     * sets checker available for group checking
     * @param string $checker checker name
     */
    public function EnableGroupChecking($checker)
    {
        if (array_key_exists($checker, $this->_aCheckers)) {
            $this->_aCheckers[$checker][1] = true;
            return true;
        }
        
        return false;
    }

    /**
     * sets checker not available for group checking
     * @param string $checker checker name
     */
    public function DisableGroupChecking($checker)
    {
        if (array_key_exists($checker, $this->_aCheckers)) {
            $this->_aCheckers[$checker][1] = false;
            return true;
        }
        
        return false;
    }

    /**
     * checks DNS-server answer for 127.0.0.* values
     * @param  string $dns_answer answer from service
     * @return bool             true when success
     */
    private function _CheckDNSAnswer($dns_answer)
    {
        if (!is_array($dns_answer)) {
            return false;
        }

        $len = count($dns_answer);

        if ($len <= 0) {
            return false;
        }

        for ($i = 0; $i < $len; $i++) {
            $obj = $dns_answer[$i];
            if (!(is_object($obj) || is_array($obj))) {
                return false;
            }

            $ip_str = $obj['ip'];
            
            if (!is_string($ip_str)) {
                return false;
            }

            $pos = strpos($ip_str, '127.0.0.');
            
            if ($pos !== false) {
                return true;
            }
        }
        
        return false;
    }
}
