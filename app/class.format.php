<?php

namespace App;

use App\Db\Database as Database;
use App\Security\Barcode as Barcode;
use DOMDocument;
use Exception;
use PDO;
use PHPMailer as PHPMailer;

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

require_once ('db/class.database.php');

class Format extends Database
{
    public $prefix = _DB_PREFIX;
    public $language = 'english';
    public $currentlang;

    public function __construct()
    {
        # Starts PDO connection to MySQL or any other desired database supported by PDO.
        parent::__construct('mysql:dbname='._DB_NAME.';host='._DB_HOST, _DB_USER, _DB_PASSWORD, []);
        parent::set_attribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        parent::set_attribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
        parent::set_attribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET CHARACTER SET utf8');

        # Starts and set the general settings configuration of the website.
        $this->config = self::get_config();
        
        # Set main session cookie on the site.
        if (!isset($_COOKIE['session'])) {
            setcookie("session", md5(($_SERVER["REQUEST_TIME"].$_SERVER["REMOTE_ADDR"])), 0, '/');
        }
        
        # Check and insert global languages files and set a cookie with the used language.
        if (isset($_REQUEST['newlang'])) {
            setcookie('lang', $_REQUEST['newlang'], time() + 31536000, '/');
            $_SESSION['lang'] = $_REQUEST['newlang'];
            header("Location: index.php");
        } elseif (!isset($_COOKIE['lang'])) {
            setcookie('lang', 'english', time() + 31536000, '/');
            $_SESSION['lang'] = 'english';
        } else {
            $_SESSION['lang'] = $_COOKIE['lang'];
        }
        
        require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/language/lang-'.$_SESSION['lang'].'.php');
    }
    /**
     * Gets general settings of the site
     * @return array returns an array with all the general configuration values
     */
    public static function get_config()
    {
        $site_config = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/config/site.settings.conf'), 1);
        
        if ($site_config['merchant'] == 'authorizenet') {
            $site_config['merchant_info']['user'] = $site_config['authorizenet_user'];
            $site_config['merchant_info']['password'] = $site_config['authorizenet_password'];
        } elseif ($site_config['merchant'] == 'paypalpro') {
            $site_config['merchant_info']['user'] = $site_config['paypalpro_user'];
            $site_config['merchant_info']['password'] = $site_config['paypalpro_password'];
            $site_config['merchant_info']['extra'] = $site_config['paypalpro_signature'];
        } elseif ($site_config['merchant'] == 'payflowpro') {
            $site_config['merchant_info']['user'] = $site_config['payflowpro_user'];
            $site_config['merchant_info']['password'] = $site_config['payflowpro_password'];
            $site_config['merchant_info']['extra'] = $site_config['payflowpro_partner'];
        } elseif ($site_config['merchant'] == 'stripe') {
            $site_config['merchant_info']['user'] = $site_config['stripe_key'];
        } else {
            $site_config['merchant_info'] = [];
        }

        return $site_config;
    }
    /**
     * Gets general settings of the site
     * @return array returns an array with all the general configuration values
     */
    public static function cache()
    {
        require_once (__DIR__.'/cache/class.cache.php');
        return new Cache\Cache(_CACHE_HANDLER, ['path' => _CACHE_FOLDER]);
    }
    /**
     *  Will run commands in the background of the server,
     * e.g when a video is uploaded if will convert it to fmv but let you do other things while the convertion is made
     * @param mixed $command - Command to be passed INTO THE SERVER
     * @param integer $priority - Priority for the server, leave blank if you dont kow.
     */
    public function backgroundExec($command, $priority = 0)
    {
        return (!empty($priority)) ? shell_exec("nohup nice -n $priority $command 2> /dev/null & echo $!") : shell_exec("nohup $command 2> /dev/null & echo $!");
    }
    
    /**
     * display the running process on the server, only for testing server performance
     *
     * @param mixed $PID - the ID of the process you want to check
     */
    public function runningProcess($PID)
    {
        exec("ps $PID", $processState);
        return(count($processState) >= 2);
    }

    /**
     * Return site domain
     * @return string
     */
    public function site_domain()
    {
        return str_replace('www.', '', strtolower($_SERVER["SERVER_NAME"]));
    }

    /**
     * return domain host and real path
     * @return string
     */
    public function real_host()
    {
        return (!empty($this->config['force_https']) ? 'https://' : 'http://').$this->site_domain();
    }
    
    /**
     * Return file extension
     * @param  string $filename
     * @return string
     */
    public function file_ext($filename = '')
    {
        $path_info = pathinfo($filename);
        return $path_info['extension'];
    }

    /**
     * Format numbers
     * @param  integral  $number
     * @param  boolean  $clean    decides if function should return values with separated by thousands_sep or not
     * @param  integer $decimals
     * @return string            return formatted number
     */
    public function number($number = 0, $clean = null, $decimals = 2)
    {
        $number = preg_replace("/[^0-9.]/", '', $number);
        return (!empty($clean)) ? number_format((double) $number, $decimals, _DECIMAL_PUNCTUATION, '') : number_format((double) $number, $decimals, _DECIMAL_PUNCTUATION, _THOUSAND_PUNCTUATION);
    }

    /**
     * Makes sure given value is an integral number, it can contain zeros in the beginning of the number
     * @param  int $number
     * @return int
     */
    public function int($number = 0)
    {
        return preg_replace("/[^0-9]/", '', $number);
    }
    /**
     * Calculate percentage out of a number
     * @param  integer $percentage percentage to be calculated
     * @param  integer $number     number to calculate from
     * @param  integer $precision  precision of decimal places
     * @return float               calculated number
     */
    public function percentage($percentage = 0, $number = 0, $precision = 2)
    {
        $percentage = $this->number($percentage, 1);
        $number = $this->number($number, 1);

        if (!empty($percentage) && !empty($number)) {
            return round(($number - $percentage / $number) * 100, $precision);
        }
        
        return false;
    }
    /**
     * Calculate percentage amount out of a number
     * @param  integer $percentage percentage to be calculated
     * @param  integer $number     number to calculate from
     * @param  integer $precision  precision of decimal places
     * @return float               calculated number
     */
    public function calc_percent($percentage = 0, $number = 0, $precision = 2)
    {
        $percentage = (double) $percentage;
        $number = $this->number($number, 1);

        if (!empty($percentage) && !empty($number)) {
            return $this->number(round(($percentage / 100) * $number, $precision), 1);
        }
        
        return false;
    }

    /**
     * Makes sure given value is a float number, it can contain zeros in the beginning of the number
     * @param  int $number
     * @return int
     */
    public function float($number = 0)
    {
        return preg_replace("/[^0-9.]/", '', $number);
    }
    
    /**
     * Converts given number into a phone format
     * @param  int $phone
     * @return string
     */
    public function phone($phone = '')
    {
        $phone = preg_replace("/[^0-9]/", "", $phone);
        $phone = preg_replace('~(\d{3})[^\d]*(\d{3})[^\d]*(\d{4})$~', '$1.$2.$3', $phone);
        return $phone;
    }
    
    /**
     * Clean and converts string into lower case and replace spaces with '-'
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    public function link($string = '')
    {
        $string = preg_replace('/\s+/', '-', $string);
        $string = strtolower(preg_replace("/[^a-z0-9-]/i", '', $string));
        return str_replace(['---', '--', '/'], '-', $string);
    }
    /**
     * Generate and format a url
     * @param  string $url text to be converted into a url
     * @param mixed $plugin
     * @return string
     */
    public function gen_url($url = '', $plugin = '')
    {
        $url = (string) $url;
        $plugin = (string) $plugin;

        if (!empty($url)) {
            return $this->link(str_replace([$this->file_ext($url), $plugin.'/'], '', $url));
        }

        return false;
    }
    
    /**
     * Try to format given string into a valid email address
     * @param  string $string
     * @param mixed $email
     * @return string
     */
    public function email($email = '')
    {
        $email = str_replace(' ', '', $email);
        return strtolower(preg_replace("/[^@.a-zA-Z0-9-_]/", '', $email));
    }

    /**
     * Checks if an email address is right
     * @param string $email
     * @return bool
     */
    public function check_email($email = ''): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            [$user, $domain] = explode('@', $email);

            if (checkdnsrr($domain, 'MX') && checkdnsrr($domain, 'A')) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Checks if a url address is right
     * @param  string $email
     * @param mixed $url
     * @return string
     */
    public function check_url($url = '')
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
    
    /**
     * Checks if a IP address is right
     * @param  string $email
     * @param mixed $ip
     * @return string
     */
    public function check_ip($ip = '')
    {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }
    
    /**
     * Checks for a given number
     * @param  string $num
     * @param  array $range
     * @return string
     */
    public function check_int($num = '', array $range = [])
    {
        if (count($range) > 0) {
            $options['options']['min_range'] = $range['min'];
            $options['options']['max_range'] = $range['max'];
            $options['flags'] = FILTER_FLAG_ALLOW_OCTAL;

            return filter_var($num, FILTER_VALIDATE_INT, $options);
        }
         
        return filter_var($num, FILTER_VALIDATE_INT);
    }
    
    /**
     * Checks if a given address is a po. box
     * @param  string  $address
     * @return boolean
     */
    public function is_po_box($address = '')
    {
        return (preg_match('/^([pP]{1}(.*?)[oO]{1}(.*?))?([bB][oO]?[xX])(\s+)([0-9]+)$/', trim($address))) ? true : false;
    }

    /**
     * Cleans given string from any html, js, css or more tags
     * @param  string $string
     * @return string
     */
    public function strip_tags($string = '')
    {
        return strip_tags(strtr($string, array_flip(get_html_translation_table(HTML_ENTITIES))));
    }
    
    /**
     * Reduce given string to $limit amount of characters placing dots in the middle
     * @param  string  $string
     * @param  integer $limit    allowed amount of words
     * @param  string  $end_char end character to be added at the end of the string
     * @return string
     */
    public function reduce_text_format($string = '', $limit = 30, $end_char = '&#8230;')
    {
        if (trim($string) == '' || strlen($string) <= $limit) {
            return $string;
        }

        $sizes = round($limit / 2, 0, PHP_ROUND_HALF_DOWN) - 1;
        $fst_half = substr($string, 0, $sizes);
        $sec_half = substr($string, -$sizes);

        if (strlen($string) > $limit) {
            $end_char = '';
        }

        return $fst_half.$end_char.$sec_half;
    }
    
    /**
     * Reduce given string to $limit amount of characters
     * @param  string  $string
     * @param  integer $limit    allowed amount of words
     * @param  string  $end_char end character to be added at the end of the string
     * @return string
     */
    public function reduce_text($string = '', $limit = 30, $end_char = '&#8230;')
    {
        if (trim($string) == '' || strlen($string) <= $limit) {
            return $string;
        }

        $string = substr($string, 0, $limit);
        
        if (strlen($string) > $limit) {
            $end_char = '';
        }
        
        return $string.$end_char;
    }
    
    /**
     * Reduce given string to $limit amount of words
     * @param  string  $string
     * @param  integer $limit    allowed amount of words
     * @param  string  $end_char end character to be added at the end of the string
     * @return string
     */
    public function reduce_words($string = '', $limit = 30, $end_char = '&#8230;')
    {
        if (trim($string) == '') {
            return $string;
        }

        preg_match('/\s*(?:\S*\s*){'.(int) $limit.'}/', $string, $matches);
        
        if (strlen($matches[0]) == strlen($string)) {
            $end_char = '';
        }
        
        return rtrim($matches[0]).$end_char;
    }
    
    /**
     * Format given date as time ago
     * @param  string  $time
     * @param  integer $limit    allowed amount of words
     * @param  string  $end_char end character to be added at the end of the string
     * @return string
     */
    public function format_date($time = '')
    {
        if (!empty($time)) {
            if (!is_numeric($time)) {
                $time = strtotime($time);
            }

            $periods = ['second', 'minute', 'hour', 'day', 'week', 'month', 'year', 'decade'];
            $lengths = ['60','60','24','7','4.35','12','10'];
            $now = time();
            $difference = $now - $time;
            $tense = "ago";

            for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
                $difference /= $lengths[$j];
            }

            $difference = round($difference);

            if ($difference != 1) {
                $periods[$j] .= 's';
            }
            
            return $difference.' '.$periods[$j].' '.$tense;
        }

        return false;
    }

    /**
     * Format given date
     * @param  string  $time
     * @param  integer $limit    allowed amount of words
     * @param  string  $end_char end character to be added at the end of the string
     * @return string
     */
    public function format_sort_date($time = '')
    {
        if (!empty($time)) {
            if (!is_numeric($time)) {
                $time = strtotime($time);
            }
            
            $difference = time() - $time;
            $today = time() - strtotime(date('Y-m-d 00:00:00'));
            $yesterday = $today + 86400;

            if ($difference < $today) {
                return 'Today '.date('g:iA', $time);
            } elseif ($difference < $yesterday) {
                return 'Yesterday '.date('g:iA', $time);
            }
            return date('M jS g:iA', $time);
        }

        return false;
    }
    
    /**
     * Find the difference between two dates
     * @param  integer $from     first date to check
     * @param  integer $to     [second date to check
     * @return array              the different between dates including years, months, weeks, days, hours, minutes, seconds
     */
    public function date_diff($from = '', $to = '')
    {
        $from_date = new DateTime(date('Y-m-d H:i:s', strtotime($from)));
        $to_date = new DateTime(date('Y-m-d H:i:s', strtotime($to)));

        $intervals = ['year' => 0, 'month' => 0, 'week' => 0, 'day' => 0, 'hour' => 0, 'minute' => 0, 'second' => 0];

        foreach ($intervals as $key => &$value) {
            while ($from_date <= $to_date) {
                $from_date->modify('+1 '.$key);
                
                if ($from_date > $to_date) {
                    $from_date->modify('-1 '.$key);
                    break;
                }
                 
                $value++;
            }
        }

        return $intervals;
    }
    /**
     * Compare arrays and return its different
     * @param  array  $_first_array  first array to check
     * @param  array  $_second_array second array to check
     * @param mixed $avoid
     * @return array                 array with found results
     */
    public function array_diff(array $_first_array = [], array $_second_array = [], $avoid = [])
    {
        try {
            $_valid_array = array_diff($_first_array, $_second_array);
            $_result_array = [];

            foreach ($_second_array as $key => $val) {
                if ($_first_array[$key] != $val && !array_search($key, $avoid)) {
                    if (!is_array($key)) {
                        $_result_array[] = $key.' was '.$_first_array[$key];
                    }
                }
            }

            return $_result_array;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    
    /**
     * Search for an given string within text or array
     * @param  string  $needle
     * @param  string/array  $haystack
     * @param  integer $case_sensitive
     * @return boolean
     */
    public function str_search($needle = '', $haystack = '', $case_sensitive = 0)
    {
        if (is_array($haystack)) {
            if (!empty($case_sensitive)) {
                if (in_array($needle, $haystack, true)) {
                    return true;
                }
            } else {
                if (in_array($needle, $haystack)) {
                    return true;
                }
            }
        } else {
            if (!empty($case_sensitive)) {
                if (strstr($haystack, $needle)) {
                    return true;
                }
            } else {
                if (stristr($haystack, $needle)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * seach for an given string within any section of a given string or array, string does not have to be exactly as needed in order to be found
     * @param  string  $needle
     * @param  string/array  $haystack
     * @param  integer $case_sensitive
     * @return boolean
     */
    public function str_locate($needle = '', $haystack = '', $case_sensitive = 0)
    {
        if (is_array($haystack)) {
            if (!empty($case_sensitive)) {
                foreach ($haystack as $value) {
                    if (stripos($value, $needle) !== false) {
                        return true;
                    }
                }
            } else {
                foreach ($haystack as $value) {
                    if (strpos($value, $needle) !== false) {
                        return true;
                    }
                }
            }
        } else {
            if (!empty($case_sensitive)) {
                if (strstr($haystack, $needle)) {
                    return true;
                }
            } else {
                if (stristr($haystack, $needle)) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * filters a given string or array
     * @param  string/array $string
     * @param  boolean $nohtml decides if html tags are allowed or not
     * @param  boolean $save   decides if string will be stringslashed or not
     * @return string|array
     */
    public function filter($string = '', $nohtml = 0, $save = 0)
    {
        if (is_array($string)) {
            foreach ($string as $key => $value) {
                $return[$key] = $this->filter($value, $nohtml, $save);
            }
        } else {
            if (is_string($string)) {
                $string = trim($string);
                if (!empty($nohtml)) {
                    $string = htmlentities(strip_tags($string), ENT_QUOTES, 'UTF-8', false);
                } else {
                    $string = preg_replace('/(<style type=.+?)+(<\/style>)/i', '', $string);
                    $string = htmlentities($string, ENT_QUOTES, 'UTF-8', false);
                }
                
                if (!empty($save)) {
                    $string = addslashes($this->cleanMSWord($string));
                } else {
                    $string = str_replace(['&lt;', '&gt;', '&amp;', '&#039;', '&quot;'], ['<', '>','&','\'','"'], htmlspecialchars_decode($string, ENT_NOQUOTES));
                    $string = stripslashes($string);
                }

                $return = $string;
            } else {
                $return = $string;
            }
        }

        return $return;
    }
    /**
     * Encode array to be submitted using any $_REQUEST, $_SESSION or $_COOKIE method
     * @param  array  $data data to be encoded
     * @return [string]       ready to be used encoded string
     */
    public function encode_var($data = [])
    {
        if (!empty($data) && is_array($data)) {
            return urlencode(base64_encode(serialize($data)));
        }

        return false;
    }
    /**
     * Decode string used on any $_REQUEST, $_SESSION or $_COOKIE method
     * @param  string $data encoded string
     * @return [array] decoded array ready to be used
     */
    public function decode_var($data = '')
    {
        if (!empty($data)) {
            return unserialize(base64_decode(urldecode($data)));
        }

        return false;
    }
    /**
     * Serialize and filter data to be inserted into database
     * @param  [array]  $data array to be serialized
     * @return [string] filtered and serialized string to be inserted in database
     */
    public function serialize($data = [])
    {
        if (!empty($data) && is_array($data)) {
            $data = serialize($data);
            return $this->filter($data, 1, 1);
        }

        return false;
    }

    /**
     * Unserilize and clean string from database
     * @param  [string] $data data to be unserialized
     * @return [array] array already cleaned and unserialized
     */
    public function unserialize($data = '')
    {
        if (!empty($data)) {
            return unserialize($this->filter($data, 1));
        }

        return false;
    }

    /**
     * Check if request was placed using ajax
     * @return boolean
     */
    public function is_ajax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;
    }

    /**
     * Check is array is completely empty
     * @param  [array] $array
     * @return [boolean]
     */
    public function empty_array($array = [])
    {
        if (!@is_array($array) || @count($array) == 0) {
            return true;
        }

        return false;
    }
    /**
     * Check if any given info is empty
     * @param  string  $data string, array, number to be checked
     * @return boolean
     */
    public function is_empty($data = '')
    {
        if (is_array($data)) {
            return $this->empty_array($data);
        }
        
        if (!empty($data)) {
            return true;
        }
        
        return false;
    }
    /**
     * clean any microsoft word formating given to an string
     * @param  string $string
     * @param  string $convertTo decides the format the string will be converted to
     */
    public function cleanMSWord($string = '', $convertTo = 'ascii')
    {
        $exclude = [129, 141, 143, 144, 157];

        for ($i = 128; $i <= 255; $i++) {
            $characterMap['&#'.$i.';'] = chr($i);
        }

        foreach ($exclude as $i) {
            unset($characterMap['&#'.$i.';']);
        }

        switch ($convertTo) {
            case 'ascii':
                $find = array_keys($characterMap);
                $replace = array_values($characterMap);
                break;
    
            case 'entity':
            default:
                $find = array_values($characterMap);
                $replace = array_keys($characterMap);
                break;
        }

        return str_replace($find, $replace, $string);
    }
    /**
     * clean and prepares string to be used with class.pagenav.php
     * @param  string $remove
     * @param mixed $key
     */
    public function cleanQueryString($key = 'start')
    {
        parse_str($_SERVER["QUERY_STRING"], $arguments);

        $url = http_build_query(array_diff_key($arguments, [$key => '']));

        if (!empty($this->config['use_mod_rewrite'])) {
            $url = htmlentities(trim($url), ENT_QUOTES, 'UTF-8');
            return Router::mod_rewrite($url);
        }

        return $url;
    }
    /**
     * Generate Orders Numbers
     * @param mixed $table
     * @return [string]
     */
    public function generate_order_id($table = '', array $data = [])
    {
        if ($this->table_exists($table)) {
            $data['ip'] = $_SERVER['REMOTE_ADDR'];
            
            return $this->sql_insert($table, $data);
        }
        
        return false;
    }
    /**
     * Generate Orders Numbers
     * @param mixed $code
     * @return [string]
     */
    public function generate_order_number($code)
    {
        return str_pad(substr(time(), strlen($code)).$code, 13, 0, STR_PAD_LEFT);
        //return strtoupper(uniqid(substr(md5(_DB_NAME), 7, 3) . '-'));
    }
    /**
     * Generate clean credit card number and add xxxx in front os the last 4 digits
     * @param mixed $number
     * @return [string] string formatted as xxxx-xxxx-xxxx-1111
     */
    public function format_cc_number($number = '')
    {
        return str_pad(substr($number, -4), 10, ' .... ', STR_PAD_LEFT);
    }
    /**
     * Generate Barcode
     * @param  integer $id id of the item for who the barcode is generted
     * @param  string  $save_path   Place where the barcode image will be saved within the uploads folder
     */
    public function barcode($id = '', $save_path = '')
    {
        try {
            $directory = $_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/uploads/'.$save_path.'/'.md5((string) $id);
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
                @shell_exec("chmod -R 777 $directory");
            }

            $barcode = new Barcode;
            $barcode->barcode_print($id, $directory);
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    /**
     * clean new lines "\n", tabs "\t" and return "\r" lines from given string
     * @param  string $string
     */
    public function cleanNL($string = '')
    {
        $result = $string;

        foreach (["  ", " \t",  " \r",  " \n", "\t\t", "\t ", "\t\r", "\t\n", "\r\r", "\r ", "\r\t", "\r\n", "\n\n", "\n ", "\n\t", "\n\r"] as $replacement) {
            $result = str_replace($replacement, $replacement[0], $result);
        }
        
        return $string !== $result ? $this->cleanNL($result) : $result;
    }
    
    /**
     * Format text to send plain/text based emails
     * @param  string $text text to be cleaned
     * @param mixed $string
     * @return string       formatted text
     */
    public function format_plain_text($string = '')
    {
        try {
            $string = preg_replace('/(<|>)\1{2}/is', '', $string);
            $string = preg_replace(['@<head[^>]*?>.*?</head>@siu', '@<style[^>]*?>.*?</style>@siu', '@<script[^>]*?.*?</script>@siu', '@<noscript[^>]*?.*?</noscript>@siu'], "", $string);
            $string = $this->cleanNL($string);
            $string = strip_tags($string);
            $string = str_replace(['&amp;'], ['&'], $string);
            
            return $string;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    /**
     * send_emails()
     * Format and send emails out of the site.
     * @param string $subject email subject
     * @param string $body email body
     * @param string $from_email email to send out from
     * @param string $from_name name to send out from
     * @param string $receiver_email emial of the person receiving the message
     * @param string $receiver_name name of the person receiving the message
     * @param string $content_type email format, it could be text/html or text/plain
     * @param array $cc Array content for cc email, format: array(name => email)
     * @param array $bcc Array content for bcc email, format: array(name => email)
     * @param array $attachment file to be attached to the email
     * @param int $priority email prioruty, format: (1 = High, 3 = Normal, 5 = low)
     * @param mixed $reply_email
     * @param mixed $reply_name
     * @return
     */
    public function send_emails($subject = '', $body = '', $from_email = '', $from_name = '', $receiver_email = '', $receiver_name = '', $reply_email = '', $reply_name = '', $content_type = 'text/html', array $cc = [], array $bcc = [], array $attachment = [], $priority = '3')
    {
        try {
            $this->config = self::get_config();

            if (empty($subject)) {
                $subject = 'Important information from '.$this->site_domain();
            }
            if (empty($from_email)) {
                $from_email = 'no-reply@'.$this->site_domain();
            }
            if (empty($from_name)) {
                $from_name = $this->config['sitename'];
            }
            if (empty($receiver_email)) {
                $receiver_email = $this->config['contactemail'];
            }
            if (empty($receiver_name)) {
                $receiver_name = $this->config['contactname'];
            }
            if (empty($reply_email)) {
                $reply_email = $from_email;
            }
            if (empty($reply_name)) {
                $reply_name = $from_name;
            }

            require_once(APP_DIR.'/vendors/phpmailer/phpmailer/class.phpmailer.php');
            $mail = new PHPMailer();

            # For SMTP, use this function when client uses Exchange or gmail for their emails.
            
            /**
             * The following 2 options is for debugging only, comment after it is all working
             * $mail->SMTPDebug                                            = 2;
             * $mail->Debugoutput                                          = 'html';
             */

            # SMTP server configuration
            /**
             * $mail->SMTPSecure                                           = 'tls';
             * $mail->Host                                                 = 'smtp.server.com';
             * $mail->Port                                                 = 587;
             * $mail->SMTPAuth                                             = true;
             * $mail->Username                                             = 'email';
             * $mail->Password                                             = 'password';
             */
            if ($this->config['mail_transport'] === 'smtp') {
                $mail->isSMTP();
                $mail->Helo = $this->config['domain'];
                $mail->SMTPSecure = _MAIL_SECURITY;
                $mail->Host = _MAIL_HOST;
                $mail->Port = _MAIL_PORT;
                $mail->SMTPAuth = true;
                $mail->Username = _MAIL_USER;
                $mail->Password = _MAIL_PASS;
            }
            $mail->SMTPDebug = 0;
            $mail->ContentType = $content_type;
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $this->format_plain_text($body);
            $mail->WordWrap = 80;

            $mail->SetFrom($from_email, $from_name);
            $mail->AddReplyTo($reply_email, $reply_name);
            $mail->AddAddress($receiver_email, $receiver_name);
            
            if (count($cc) > 0) {
                foreach ($cc as $cc_name => $cc_email) {
                    $mail->AddCC($cc_email, $cc_name);
                }
            }
            if (count($bcc) > 0) {
                foreach ($bcc as $bcc_name => $bcc_email) {
                    $mail->AddBCC($bcc_email, $bcc_name);
                }
            }
            if (count($attachment) > 0) {
                foreach ($attachment as $attachment_name => $attachment_path) {
                    $mail->AddAttachment($attachment_path, $attachment_name);
                }
            }

            new Log("SENDING EMAIL \n".var_export($mail, true));
            $sent = $mail->Send();
            new Log($sent ? 'EMAIL SENT OK' : 'ERROR SENDING EMAIL: ' .$mail->ErrorInfo);

            if (!$sent) {
                throw new \Exception($mail->ErrorInfo);
            }
            $mail->ClearAddresses();
        } catch (\Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    /**
     * Create a multidimentional array
     * @param  [type]  $parent parent Key
     * @param  array   &$array Data
     * @param  integer $level  Level to start from
     * @return array
     */
    public function multi_dimention_array($parent, array &$array, $level = 0)
    {
        if (is_array($array[$parent])) {
            foreach ($array[$parent] as $items) {
                $indent = str_repeat('&nbsp; &nbsp;', $level);
                $return[] = [
                    'id' => $item[0],
                    'name' => $indent.$item[1]
                ];
                $this->multi_dimention_array($items[0], $array, $level + 1);
            }
        }

        return $return;
    }
    /**
     * Check spam
     * @param  string $ip        User IP
     * @param  string $name      Given Name
     * @param  string $email     Given Email
     * @param  string $message   Given Message
     * @return boolean
     */
    public function check_spam($ip = '', $name = '', $email = '', $message = '')
    {
        $ip = (!empty($ip)      ? $ip   : $_SERVER['REMOTE_ADDR']);
        $link = (!empty($link)    ? $link : $_SERVER["HTTP_REFERER"]);

        $spammer_checker = new Security\Checkspam;
        $spam_checkers = $spammer_checker->GetCheckers();

        if ($spammer_checker->CheckSpamIP($ip)) {
            return true;
        }

        return false;
    }

    /**
     * count how many notes (<li>) are made on a given html content
     * @param  string $notes html notes
     * @return int        count
     */
    public function count_notes($notes = '')
    {
        try {
            if (!empty($notes)) {
                $dom_documents = new DOMDocument();
                $dom_documents->loadHTML($notes);
                $li = $dom_documents->getElementsByTagName("li");
                $i = 0;
                foreach ($li as $li_c) {
                    $i++;
                }
                return (int) $i;
            }
            
            return 0;
        } catch (Exception $e) {
            die('ERROR: '.$e->getMessage());
        }
    }
    /**
     * Tries to change the modes of a given file to the given mode
     * @param  string $file
     * @param  int $mode
     */
    public function set_chmod($file = '', $mode = '777')
    {
        if (!empty($file)) {
            # Try using PHP chmod
            if (!@chmod($file, '0'.$mode)) {
                # Otherwise try system chmod
                @shell_exec("chmod $mode $file");
            }
            # If all fail then nothing to do, you need to change this files modes manually
        }
    }
    /**
     * Format and redirec URL depending on site configuration
     * @param  string $url
     */
    public function redirect($url = '')
    {
        $url = html_entity_decode($url);

        if (empty($url)) {
            $url = str_replace(['http://', 'https://', 'www.', $this->site_domain(), _SITE_PATH], '', $_SERVER['HTTP_REFERER']);
            $url = ($url[0] == '/') ? substr($url, 1): $url;
        }

        $url = htmlentities(trim($url), ENT_QUOTES, 'UTF-8');
        require_once(APP_DIR.'/class.router.php');
        header('Location: '._SITE_PATH.'/'.Router::mod_rewrite($url));
        exit;
    }
    
    /**
     * Format URL to output on HTML depending on site configuration
     * @param  string $url
     */
    public function format_url($url)
    {
        return Router::mod_rewrite(htmlentities(trim(html_entity_decode($url)), ENT_QUOTES, 'UTF-8'));
    }
    /**
     * Get images from a given html code
     * @param  string $text html code
     * @return string
     */
    public function get_images_from_html($text = '')
    {
        return false;
    }
}
