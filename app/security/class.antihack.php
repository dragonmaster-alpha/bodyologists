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

/*
How to use it

if($anti->detect_hack(NULL,'SPIDER') == true or $anti->detect_hack($anti->curPageURL()) == true)
{
        die('U are banned');
}

if($anti->detect_hack(NULL,'POST') == true or $anti->detect_hack(NULL,'COOKIE') == true or $anti->detect_hack(NULL,'QUERY') == true)
{
        die('U are banned');
}

if($anti->detect_hack(NULL,'FILES') == true)
{
        die('U are banned');
}

if($anti->detect_hack($_SESSION) == true)
{
        die('U are banned');
}
*/

class Antihack
{
    public function detect_hack($str, $type = 'str')
    {
        switch ($type) {
            case 'POST':

                if ((!isset($_POST)) || ('POST' !== $_SERVER[ 'REQUEST_METHOD'])) {
                    return false;
                }

                $pnodes = $this->array_flatten($_POST, false);
                $i = 0;
                
                while ($i < count($pnodes)) {
                    if ((is_string($pnodes[$i])) && (strlen($pnodes[$i]) > 0)) {
                        $pnodes[$i] = strtolower($pnodes[$i]);
                        if (false !== $this->blacklistMatch($pnodes[$i], 2) || false !== $this->blacklistMatch(urldecode($pnodes[$i]), 2)) {
                            return true;
                        }
                    }

                    $i++;
                }
                
                return false;

            break;
            
            case 'SPIDER':

                if (false === empty($_SERVER['HTTP_USER_AGENT'])) {
                    if (false !== $this->blacklistMatch(strtolower($this->hexoctal_decode($_SERVER['HTTP_USER_AGENT'])), 3)) {
                        $header = ['HTTP/1.1 404 Not Found', 'HTTP/1.1 404 Not Found', 'Content-Length: 0'];

                        foreach ($header as $sent) {
                            header($sent);
                        }
                    }
                } else {
                    return false;
                }

            break;
            
            case 'COOKIE':

                if (false !== empty($_COOKIE)) {
                    return;
                }

                $injectattempt = false;
                $ckeys = array_keys($_COOKIE);
                $cvals = array_values($_COOKIE);
                $i = 0;

                while ($i < count($ckeys)) {
                    $ckey = strtolower($this->hexoctal_decode($ckeys[$i]));
                    $cval = rawurldecode(strtolower($this->hexoctal_decode($cvals[$i])));
                    
                    if ((is_string($ckey))) {
                        if (false !== (bool) $this->blacklistMatch($ckey, 4) || false !== (bool) $this->blacklistMatch($this->hexoctal_decode($cval), 4)) {
                            return true;
                        }
                    }

                    $injectattempt = ((bool) $this->injectMatch($ckey)) ? true : ((bool) $this->injectMatch($cval));

                    if (false !== (bool) $injectattempt) {
                        return true;
                    }

                    $i++;
                }

                return false;

            break;
            
            case 'QUERY':

                if (false !== empty($_SERVER['QUERY_STRING'])) {
                    return false;
                }
                 
                    $q = [];
                    $v = '';
                    $val = '';
                    $x = 0;
                    $qsdec = $this->url_decoder($_SERVER['QUERY_STRING']);
                    $q = explode('&', $qsdec);

                    for ($x = 0; $x < count($q); $x++) {
                        $v = is_array($q[$x])? $this->array_flatten($q[$x]) : $q[$x];
                        $val = $this->hexoctal_decode(substr($v, strpos($v, '=') + 1, strlen($v)));

                        if (false !== $this->injectMatch($val) || false !== (bool) $this->blacklistMatch($val, 1)) {
                            return true;
                        }
                    }
                
                return false;

            break;
            
            case 'FILES':

                if (!empty($_FILES)) {
                    foreach ($_FILES as $key => $item) {
                        $val = @file_get_contents($item['tmp_name']);
                        if (false !== $this->injectMatch($val) || false !== (bool) $this->blacklistMatch($val, 1)) {
                            return true;
                        }
                    }

                    return false;
                }
                
                    return false;
                
            break;
                               
            default:

                if (!is_array($str)) {
                    if (false !== $this->injectMatch($str) || false !== (bool) $this->blacklistMatch($str, 1)) {
                        return true;
                    }
                } else {
                    foreach ($str as $key => $val) {
                        if (is_array($val)) {
                            $this->detect_hack($val);
                        } else {
                            if (false !== $this->injectMatch($val) || false !== (bool) $this->blacklistMatch($val, 1)) {
                                return true;
                            }
                        }
                    }

                    return false;
                }

            break;
        }
    }

    public function injectMatch($string)
    {
        $string = $this->url_decoder($string);
        $kickoff = false;
        $vartrig = "\/\/|\.\.\/|\.js|%0D%0A|0x|all|ascii\(|base64|benchmark|by|char|column|convert|cookie|create|declare|data|date|delete|drop|concat|eval|exec|from|ftp|grant|group|insert|isnull|into|length\(|load|master|onmouse|null|php|schema|select|set|shell|show|sleep|table|union|update|utf|var|waitfor|while";
        $vartrig = preg_replace("/[\s]/", '', $vartrig);
        
        for ($x = 1; $x <= 5; $x++) {
            $string = $this->cleanString($x, $string);
            
            if (false !== (bool) preg_match("/$vartrig/i", $string)) {
                $kickoff = true;
                break;
            }
        }

        if (false === $kickoff) {
            return false;
        }
        
        $j = 1;

        while ($j <= 6) {
            $string = $this->cleanString($j, $string);
            $sqlmatchlist = "(?:abs|ascii|base64|bin|cast|chr|char|charset|collation|concat|conv|convert|count|curdate|database|date|decode|diff|distinct|elt|encode|encrypt|extract|field|_file|floor|format|hex|if|in|insert|instr|interval|lcase|left|length|load_file|locate|lock|log|lower|lpad|ltrim|max|md5|mid|mod|name|now|null|ord|password|position|quote|rand|repeat|replace|reverse|right|rlike|round|row_count|rpad|rtrim|_set|schema|select|sha1|sha2|sleep|serverproperty|soundex|space|strcmp|substr|substr_index|substring|sum|time|trim|truncate|ucase|unhex|upper|_user|user|values|varchar|version|while|ws|xor)\(|\(0x|@@|cast|integer";
            $sqlmatchlist = preg_replace("/[\s]/i", '', $sqlmatchlist);
            $sqlupdatelist = "\bcolumn\b|\bdata\b|concat\(|\bemail\b|\blogin\b|\bname\b|\bpass\b|sha1|sha2|\btable\b|table|\bwhere\b|\buser\b|\bval\b|0x|--";
            $sqlupdatelist = preg_replace("/[\s]/i", '', $sqlupdatelist);

            if (false !== (bool) preg_match("/\bdrop\b/i", $string) && false !== (bool) preg_match("/\btable\b|\buser\b/i", $string) && false !== (bool) preg_match("/--|and||\//i", $string)) {
                return true;
            } elseif ((false !== strpos($string, 'grant')) && (false !== strpos($string, 'all')) && (false !== strpos($string, 'privileges'))) {
                return true;
            } elseif (false !== (bool) preg_match("/(?:(sleep\((\s*)(\d*)(\s*)\)|benchmark\((.*)\,(.*)\)))/i", $string)) {
                return true;
            } elseif (false !== preg_match_all("/\bload\b|\bdata\b|\binfile\b|\btable\b|\bterminated\b/i", $string, $matches) > 3) {
                return true;
            } elseif (((false !== (bool) preg_match("/select|isnull|declare|ascii\(substring|length\(/i", $string)) && (false !== (bool) preg_match("/\band\b|\bif\b|group_|_ws|load_|concat\(|\bfrom\b/i", $string)) && (false !== (bool) preg_match("/$sqlmatchlist/", $string)))) {
                return true;
            } elseif (false !== preg_match_all("/$sqlmatchlist/", $string, $matches) > 2) {
                return true;
            } elseif (false !== strpos($string, 'update') && false !== (bool) preg_match("/\bset\b/i", $string) && false !== (bool) preg_match("/$sqlupdatelist/i", $string)) {
                return true;
            } elseif ((substr_count($string, 'var') > 1) && false !== (bool) preg_match("/date\(|while\(|sleep\(/i", $string)) {
                return true;
            }
               
            # run through a set of filters to find specific attack vectors
            $thenode = $this->cleanString($j, $this->get_request_uri());
            $sqlfilematchlist = 'access_|\balias\b|apache|\/bin|\bboot\b|config|\benviron\b|error_|error.|\/etc|httpd|_log|\.(?:js|txt|exe|ht|ini|bat|log)|\blib\b|\bproc\b|\bsql\b|tmp|tmp\/sess|\busr\b|\bvar\b|\/(?:uploa|passw)d';
            $sqlfilematchlist = preg_replace("/[\s]/i", '', $sqlfilematchlist);

            if ((false !== (bool) preg_match("/onmouse(?:down|over)/i", $string)) && (2 < (int) preg_match_all("/c(?:path|tthis|t\(this)|http:|(?:forgotte|admi)n|sqlpatch|,,|ftp:|(?:aler|promp)t/i", $thenode, $matches))) {
                return true;
            } elseif (((false !== strpos($thenode, 'ftp:')) && (substr_count($thenode, 'ftp') > 1)) && (2 < (int) preg_match_all("/@|\/\/|:/i", $thenode, $matches))) {
                return true;
            } elseif (('POST' == $_SERVER['REQUEST_METHOD']) && (false !== (bool) preg_match("/(?:showimg|cookie|cookies)=/i", $string))) {
                return true;
            } elseif ((substr_count($string, '../') > 3) || (substr_count($string, '..//') > 3)) {
                if (false !== (bool) preg_match("/$sqlfilematchlist/i", $string)) {
                    return true;
                }
            } elseif ((substr_count($string, '/') > 1) && (2 <= (int) preg_match_all("/$sqlfilematchlist/i", $thenode, $matches))) {
                return true;
            } elseif ((false !== (bool) preg_match("/%0D%0A/i", $thenode)) && (false !== strpos($thenode, 'utf-7'))) {
                return true;
            } elseif (false !== (bool) preg_match("/php:\/\/filter|convert.base64-(?:encode|decode)|zlib.(?:inflate|deflate)/i", $string) || false !== (bool) preg_match("/data:\/\/filter|text\/plain|http:\/\/(?:127.0.0.1|localhost)/i", $string)) {
                return true;
            }
            if (5 <= substr_count($string, '%')) {
                $string = str_replace('%', '', $string);
            }

            $sqlmatchlist = '@@|_and|ascii|b(?:enchmark|etween|in|itlength|ulk)|c(?:ast|har|ookie|ollate|olumn|oncat|urrent)|\bdate\b|dump|e(?:lt|xport)|false|\bfield\b|fetch|format|function|\bhaving\b|i(?:dentity|nforma|nstr)|\bif\b|\bin\b|l(?:case|eft|ength|ike|imit|oad|ocate|ower|pad|trim)|join|m(:?ake|atch|d5|id)|not_like|not_regexp|null|order|outfile|p(?:ass|ost|osition|riv)|\bquote\b|\br(?:egexp\b|ename\b|epeat\b|eplace\b|equest\b|everse\b|eturn\b|ight\b|like\b|pad\b|trim\b)|\bs(?:ql\b|hell\b|leep\b|trcmp\b|ubstr\b)|\bt(?:able\b|rim\b|rue\b|runcate\b)|u(?:case|nhex|pdate|pper|ser)|values|varchar|\bwhen\b|where|with|\(0x|_(?:decrypt|encrypt|get|post|server|cookie|global|or|request|xor)|(?:column|db|load|not|octet|sql|table|xp)_';
            $sqlmatchlist = preg_replace("/[\s]/i", '', $sqlmatchlist);
       
            if ((false !== (bool) preg_match("/\border by\b|\bgroup by\b/i", $string)) && (false !== (bool) preg_match("/\bcolumn\b|\bdesc\b|\berror\b|\bfrom\b|hav|\blimit\b|offset|\btable\b|\/|--/i", $string) || (false !== (bool) preg_match("/\b[0-9]\b/i", $string)))) {
                return true;
            } elseif ((false !== (bool) preg_match("/\btable\b|\bcolumn\b/i", $string)) && false !== strpos($string, 'exists') && false !== (bool) preg_match("/\bif\b|\berror\b|\buser\b|\bno\b/i", $string)) {
                return true;
            } elseif ((false !== strpos($string, 'waitfor') && false !== strpos($string, 'delay') && ((bool) preg_match("/(:)/i", $string))) || (false !== strpos($string, 'nowait') && false !== strpos($string, 'with') && (false !== (bool) preg_match("/--|\/|\blimit\b|\bshutdown\b|\bupdate\b|\bdesc\b/i", $string)))) {
                return true;
            } elseif (false !== (bool) preg_match("/\binto\b/i", $string) && (false !== (bool) preg_match("/\boutfile\b/i", $string))) {
                return true;
            } elseif (false !== (bool) preg_match("/\bdrop\b/i", $string) && (false !== (bool) preg_match("/\buser\b/i", $string))) {
                return true;
            } elseif (((false !== strpos($string, 'create') && false !== (bool) preg_match("/\btable\b|\buser\b|\bselect\b/i", $string)) || (false !== strpos($string, 'delete') && false !== strpos($string, 'from')) || (false !== strpos($string, 'insert') && (false !== (bool) preg_match("/\bexec\b|\binto\b|from/i", $string))) || (false !== strpos($string, 'select') && (false !== (bool) preg_match("/\bby\b|\bcase\b|from|\bif\b|\binto\b|\bord\b|union/i", $string)))) && ((false !== (bool) preg_match("/$sqlmatchlist/i", $string)) || (2 <= substr_count($string, ',')))) {
                return true;
            } elseif ((false !== strpos($string, 'union')) && (false !== strpos($string, 'select')) && (false !== strpos($string, 'from'))) {
                return true;
            } elseif (false !== strpos($string, 'etc/passwd')) {
                return true;
            } elseif (false !== strpos($string, 'null')) {
                $nstring = preg_replace("/[^a-z]/i", '', $this->url_decoder($string));
                if (false !== (bool) preg_match("/(null){3,}/i", $nstring)) {
                    return true;
                }
            }
            $j++;
        }
        
        return false;
    }
        
    public function blacklistMatch($val, $list)
    {
        $val = preg_replace('/[\s]/i', '', $val);
        $_blacklist = [];
        $_blacklist[1] = "php\/login|eval\(base64_decode|asc%3Deval|eval\(\$_|EXTRACTVALUE\(|allow_url_include|safe_mode|suhosin\.simulation|disable_functions|phpinfo\(|open_basedir|auto_prepend_file|php:\/\/input|\)limit|rush=|fromCharCode|;base64|base64,|onerror=prompt\(|onerror=alert\(|javascript:prompt\(|\/var\/lib\/php|javascript:alert\(|pwtoken_get|php_uname|%3Cform|passthru\(|sha1\(|sha2\(|<\?php|\/iframe|\$_GET|=@@version|ob_starting|and1=1|\.\.\/cmd|document\.cookie|document\.write|onload\=|mysql_query|document\.location|window\.location|location\.replace\(|\(\)\}|@@datadir|\/FRAMESET|<ahref=|\[url=http:\/\/|\[\/url\]|\[link=http:\/\/|\[\/link\]|YWxlcnQo|_START_|onunload%3d|PHP_SELF|shell_exec|\$_SERVER|`;!--=|substr\(|\$_POST|\$_SESSION|\$_REQUEST|\$_ENV|GLOBALS\[|\$HTTP_|\.php\/admin|mosConfig_|%3C@replace\(|hex_ent|inurl:|replace\(|\/iframe>|return%20clk|php\/password_for|unhex\(|error_reporting\(|HTTP_CMD|=alert\(|localhost|}\)%3B|Set-Cookie|%27%a0%6f%72%a0%31%3d%31|%bf%5c%27|%ef%bb%bf|%20regexp%20|\{\$\{|HTTP\/1\.|\{$\_|PRINT@@variable|xp_cmdshell|xp_availablemedia|sp_password|\/var\/www\/php|_SESSION\[!|file_get_contents\(|\*\(\|\(objectclass=|\|\|UTL_HTTP\.REQUEST|<script>";
        $_blacklist[2] = "ZXZhbCg=|eval\(base64\_decode|fromCharCode|allow\_url\_include|php:\/\/input|concat\(@@|suhosin\.simulation=|\#\!\/usr\/bin\/perl -I|file\_get_contents\(|onerror=prompt\(|script>alert\(|\$fp1 = @fopen\(|YWxlcnQo|ZnJvbUNoYXJDb2Rl";
        $_blacklist[3] = "Baidu|WebLeacher|autoemailspider|MSProxy|Yeti|Twiceler|blackhat|Mail\.Ru|fuck";
        $_blacklist[4] = "eval\(|fromCharCode|prompt\(|ZXZhbCg=|ZnJvbUNoYXJDb2Rl|U0VMRUNULyoqLw==|Ki9XSEVSRS8q|YWxlcnQo";
        $_thelist = preg_replace("/[\s]/i", '', $_blacklist[(int) $list]);
        
        if (false !== (bool) preg_match("/$_thelist/i", $val)) {
            return true;
        }

        return false;
    }
        
    public function cleanString($b, $s)
    {
        $s = strtolower($this->url_decoder($s));

        switch ($b) {
            case (1):
                return preg_replace("/[^\s{}a-z0-9_?,()=@%:{}\/.-]/i", '', $s);
            break;
            case (2):
                return preg_replace("/[^\s{}a-z0-9_?,=@%:{}\/.-]/i", '', $s);
            break;
            case (3):
                return preg_replace("/[^\s=a-z0-9]/i", '', $s);
            break;
            case (4): // fwr_security pro
                return preg_replace("/[^\s{}a-z0-9_\.\-]/i", "", $s);
            break;
            case (5):
                return str_replace('//', '/', $s);
            break;
            case (6):
                return str_replace('/**/', ' ', $s);
            break;
            default:
                return $this->url_decoder($s);
        }
    }
        
    public function url_decoder($var)
    {
        return rawurldecode(urldecode($var));
    }
        
    public function get_request_uri()
    {
        if (false !== getenv('REQUEST_URI')) {
            return getenv('REQUEST_URI');
        }
         
        return $_SERVER['REQUEST_URI'];
    }
        
    public function array_flatten($array, $preserve_keys = false)
    {
        if (false === $preserve_keys) {
            $array = array_values($array);
        }

        $flattened_array = [];

        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $flattened_array = array_merge($flattened_array, $this->array_flatten($v, $preserve_keys));
            } elseif ($preserve_keys) {
                $flattened_array[$k] = $v;
            } else {
                $flattened_array[] = $v;
            }
        }

        return $flattened_array;
    }

    public function hexoctal_decode($code)
    {
        $code = (substr_count($code, '\\x') > 5) ? urldecode(str_replace('\\x', '%', $code)) : $code;
        $code = (substr_count($code, '\\x') > 5) ? urldecode(str_replace('\\x', '%', $code)) : $code;
        
        return urldecode($code);
    }

    public function curPageURL()
    {
        $pageURL = 'http';

        if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }

        $pageURL .= "://";

        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }

        return $pageURL;
    }
}
