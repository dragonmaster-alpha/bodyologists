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

class Router
{
    private static $request;
    private static $path = [];

    public static function is_index()
    {
        return (rtrim($_SERVER['REQUEST_URI'], '\/') == _SITE_PATH || strstr($_SERVER["REQUEST_URI"], "index")) ? true : false;
    }

    public static function route()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            self::$request = explode('?', rtrim($_SERVER['REQUEST_URI'], '\/'));
            self::$path['base'] = rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/');
            self::$path['parts'] = explode('/', substr(self::$request[0], strlen(self::$path['base']) + 1));

            $query = self::$request[1] ?? '';
            parse_str($query, $_GET);
        
            if (!empty(self::$path['parts'][0])) {
                if (file_exists($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.self::$path['parts'][0].'/index.php')) {
                    $_REQUEST['plugin'] = self::$path['parts'][0];
                } else {
                    $_REQUEST['plugin'] = 'pages';
                    $_REQUEST['op'] = $_REQUEST['url'] = self::$path['parts'][0];
                }

                if (file_exists($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.self::$path['parts'][0].'/addons/'.self::$path['parts'][1].'/index.php')) {
                    $_REQUEST['addon'] = self::$path['parts'][1];

                    if ($_REQUEST['addon'] == 'categories') {
                        $_REQUEST['url'] = str_replace(_SITE_PATH.'/'.$_REQUEST['plugin'].'/'.$_REQUEST['addon'].'/', '', $_SERVER['REQUEST_URI']);
                    } else {
                        if (!empty(self::$path['parts'][2])) {
                            if (is_numeric(self::$path['parts'][2])) {
                                $_REQUEST['id'] = (int) self::$path['parts'][2];
                            } else {
                                $_REQUEST['op'] = $_REQUEST['url'] = self::$path['parts'][2];
                            }
                        }
                    }
                } elseif (file_exists($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.self::$path['parts'][0].'/'.self::$path['parts'][1].'.php')) {
                    $_REQUEST['file'] = self::$path['parts'][1];

                    if (!empty(self::$path['parts'][2])) {
                        if (is_numeric(self::$path['parts'][2])) {
                            $_REQUEST['id'] = (int) self::$path['parts'][2];
                        } else {
                            $_REQUEST['op'] = $_REQUEST['url'] = self::$path['parts'][2];
                        }
                    }
                } elseif (!empty(self::$path['parts'][1])) {
                    if (is_numeric(self::$path['parts'][1])) {
                        $_REQUEST['id'] = (int) self::$path['parts'][1];
                    } else {
                        $_REQUEST['op'] = $_REQUEST['url'] = self::$path['parts'][1];

                        if (self::$path['parts'][2] && is_numeric(self::$path['parts'][2])) {
                            $_REQUEST['id'] = (int) self::$path['parts'][2];
                        }
                    }
                }
            }

            if (!empty($_REQUEST['id'])) {
                $_GET['id'] = (int) $_REQUEST['id'];
            }
        }
    }

    public static function admin_route()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            self::$request = explode('?', $_SERVER['REQUEST_URI']);
            self::$path['base'] = rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/');
            self::$path['parts'] = explode('/', substr(self::$request[0], strlen(self::$path['base']) + 1));

            parse_str(self::$request[1], $_GET);
        
            if (!empty(self::$path['parts'][0])) {
                if (strpos(self::$path['parts'][0], '.') && !strpos(self::$path['parts'][0], 'admin')) {
                    $_REQUEST['url'] = self::$path['parts'][0];
                } else {
                    $_REQUEST['plugin'] = self::$path['parts'][0];
                }

                if (file_exists($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.self::$path['parts'][0].'/addons/'.self::$path['parts'][1].'/admin//index.php')) {
                    $_REQUEST['addon'] = self::$path['parts'][1];

                    if (!empty(self::$path['parts'][2])) {
                        if (is_numeric(self::$path['parts'][2])) {
                            $_REQUEST['id'] = (int) self::$path['parts'][2];
                        } else {
                            if (strpos(self::$path['parts'][2], '.')) {
                                $_REQUEST['url'] = self::$path['parts'][2];
                            } else {
                                $_REQUEST['op'] = self::$path['parts'][2];
                            }
                        }
                    }
                } elseif (file_exists($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.self::$path['parts'][0].'/admin/'.self::$path['parts'][1].'.php')) {
                    $_REQUEST['file'] = self::$path['parts'][1];

                    if (!empty(self::$path['parts'][2])) {
                        if (is_numeric(self::$path['parts'][2])) {
                            $_REQUEST['id'] = (int) self::$path['parts'][2];
                        } else {
                            if (strpos(self::$path['parts'][2], '.')) {
                                $_REQUEST['url'] = self::$path['parts'][2];
                            } else {
                                $_REQUEST['op'] = self::$path['parts'][2];
                            }
                        }
                    }
                } elseif (!empty(self::$path['parts'][1])) {
                    if (strpos(self::$path['parts'][1], '.')) {
                        $_REQUEST['url'] = self::$path['parts'][1];
                    } else {
                        $_REQUEST['op'] = self::$path['parts'][1];

                        if (is_numeric(self::$path['parts'][2])) {
                            $_REQUEST['id'] = (int) self::$path['parts'][2];
                        } else {
                            $_REQUEST['url'] = self::$path['parts'][2];
                        }
                    }
                }
            }

            if (!empty($_REQUEST['id'])) {
                $_GET['id'] = (int) $_REQUEST['id'];
            }
        }
    }

    /**
     * Format URL to output on HTML depending on site configuration
     * @param  string $url
     * @param mixed $_url
     */
    public static function format_url($_url)
    {
        return self::mod_rewrite(htmlentities(trim(html_entity_decode($_url)), ENT_QUOTES, 'UTF-8'));
    }
    
    public static function prepare_imgs($_data)
    {
        if (!empty($_data)) {
            $dom = new DOMDocument();
            @$dom->loadHTML($_data);

            foreach ($dom->getElementsByTagName('img') as $node) {
                if (!$node->hasAttribute('data-src')) {
                    $old_src = str_replace(' ', '%20', $node->getAttribute('src'));
                    $node->removeAttribute('src');
                    $node->setAttribute("data-src", $old_src);
                }

                if (!$node->hasAttribute('alt')) {
                    $node->setAttribute("alt", $dom->getElementsByTagName("title")->item(0)->nodeValue);
                }
            }

            $_data = $dom->saveHtml();
        }
        
        return $_data;
    }
    
    public static function mod_rewrite($_data)
    {
        $urlin = [
            # Frontend Links
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)&amp;addon=([a-zA-Z_]*)&amp;op=([a-zA-Z_]*)&amp;([-#&+\\./0-9=?A-Z_a-z]*)'",
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)&amp;addon=([a-zA-Z_]*)&amp;op=([a-zA-Z_]*)'",
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)&amp;addon=([a-zA-Z_]*)&amp;id=([0-9]*)&amp;([-#&+\\./0-9=?A-Z_a-z]*)'",
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)&amp;addon=([a-zA-Z_]*)&amp;id=([0-9]*)'",
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)&amp;addon=([a-zA-Z_]*)'",
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)&amp;file=([a-zA-Z_]*)&amp;op=([a-zA-Z_]*)&amp;([-#&+\\./0-9=?A-Z_a-z]*)'",
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)&amp;file=([a-zA-Z_]*)&amp;op=([a-zA-Z_]*)'",
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)&amp;file=([a-zA-Z_]*)&amp;id=([0-9]*)&amp;([-#&+\\./0-9=?A-Z_a-z]*)'",
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)&amp;file=([a-zA-Z_]*)&amp;id=([0-9]*)'",
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)&amp;file=([a-zA-Z_]*)'",
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)&amp;op=([a-zA-Z_]*)&amp;id=([0-9]*)&amp;([-#&+\\./0-9=?A-Z_a-z]*)'",
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)&amp;op=([a-zA-Z_]*)&amp;id=([0-9]*)'",
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)&amp;op=([a-zA-Z_]*)&amp;([-#&+\\./0-9=?A-Z_a-z]*)'",
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)&amp;op=([a-zA-Z_]*)'",
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)&amp;([-#&+\\./0-9=?A-Z_a-z]*)'",
            "'(?<!<a href=)index.php\?plugin=([a-zA-Z_]*)'",
            # Backend Links
            "'(?<!<a href=)admin.php\?plugin=([a-zA-Z_]*)&amp;addon=([a-zA-Z_]*)&amp;op=([a-zA-Z_]*)&amp;([-#&+\\./0-9=?A-Z_a-z]*)'",
            "'(?<!<a href=)admin.php\?plugin=([a-zA-Z_]*)&amp;addon=([a-zA-Z_]*)&amp;op=([a-zA-Z_]*)'",
            "'(?<!<a href=)admin.php\?plugin=([a-zA-Z_]*)&amp;addon=([a-zA-Z_]*)&amp;id=([0-9]*)'",
            "'(?<!<a href=)admin.php\?plugin=([a-zA-Z_]*)&amp;addon=([a-zA-Z_]*)'",
            "'(?<!<a href=)admin.php\?plugin=([a-zA-Z_]*)&amp;file=([a-zA-Z_]*)&amp;op=([a-zA-Z_]*)&amp;([-#&+\\./0-9=?A-Z_a-z]*)'",
            "'(?<!<a href=)admin.php\?plugin=([a-zA-Z_]*)&amp;file=([a-zA-Z_]*)&amp;op=([a-zA-Z_]*)'",
            "'(?<!<a href=)admin.php\?plugin=([a-zA-Z_]*)&amp;file=([a-zA-Z_]*)&amp;id=([0-9]*)'",
            "'(?<!<a href=)admin.php\?plugin=([a-zA-Z_]*)&amp;file=([a-zA-Z_]*)'",
            "'(?<!<a href=)admin.php\?plugin=([a-zA-Z_]*)&amp;op=([a-zA-Z_]*)&amp;([-#&+\\./0-9=?A-Z_a-z]*)'",
            "'(?<!<a href=)admin.php\?plugin=([a-zA-Z_]*)&amp;op=([a-zA-Z_]*)&amp;id=([0-9]*)'",
            "'(?<!<a href=)admin.php\?plugin=([a-zA-Z_]*)&amp;op=([a-zA-Z_]*)'",
            "'(?<!<a href=)admin.php\?plugin=([a-zA-Z_]*)&amp;([-#&+\\./0-9=?A-Z_a-z]*)'",
            "'(?<!<a href=)admin.php\?plugin=([a-zA-Z_]*)'"
        ];

        $urlout = [
            # Frontend Links
            "\\1/\\2/\\3/?\\4",
            "\\1/\\2/\\3",
            "\\1/\\2/\\3/?\\4",
            "\\1/\\2/\\3",
            "\\1/\\2",
            "\\1/\\2/\\3/?\\4",
            "\\1/\\2/\\3",
            "\\1/\\2/\\3/?\\4",
            "\\1/\\2/\\3",
            "\\1/\\2",
            "\\1/\\2/\\3?\\4",
            "\\1/\\2/\\3",
            "\\1/\\2/?\\3",
            "\\1/\\2",
            "\\1/?\\2",
            "\\1",
            # Backend Links
            "\\1/\\2/\\3/?\\4",
            "\\1/\\2/\\3",
            "\\1/\\2/\\3",
            "\\1/\\2",
            "\\1/\\2/\\3/?\\4",
            "\\1/\\2/\\3",
            "\\1/\\2/\\3",
            "\\1/\\2",
            "\\1/\\2/?\\3",
            "\\1/\\2/\\3",
            "\\1/\\2",
            "\\1/?\\2",
            "\\1"
        ];

        return preg_replace($urlin, $urlout, $_data);
    }
}
