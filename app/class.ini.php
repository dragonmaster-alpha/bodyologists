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

class INI
{
    /**
     *  WRITE
     * @param mixed $filename
     * @param mixed $ini
     */
    public static function write($filename, $ini)
    {
        $string = '';
        foreach (array_keys($ini) as $key) {
            $string .= '['.$key."]\n";
            $string .= self::write_get_string($ini[$key], '')."\n";
        }
        file_put_contents($filename, $string);
    }
    /**
     *  write get string
     * @param mixed $ini
     * @param mixed $prefix
     */
    public static function write_get_string(& $ini, $prefix)
    {
        $string = '';
        ksort($ini);

        foreach ($ini as $key => $val) {
            if (is_array($val)) {
                $string .= self::write_get_string($ini[$key], $prefix.$key.'.');
            } else {
                $string .= $prefix.$key.' = '.str_replace("\n", "\\\n", self::set_value($val))."\n";
            }
        }
        return $string;
    }
    /**
     *  Manage keys
     * @param mixed $val
     */
    public static function set_value($val)
    {
        if ($val === true) {
            return 'true';
        } elseif ($val === false) {
            return 'false';
        }
        return $val;
    }
    /**
     *  READ
     * @param mixed $filename
     */
    public static function read($filename)
    {
        $ini = [];
        $lines = file($filename);
        $section = 'default';
        $multi = '';

        foreach ($lines as $line) {
            if (substr($line, 0, 1) !== ';') {
                $line = str_replace("\r", "", str_replace("\n", "", $line));
                if (preg_match('/^\[(.*)\]/', $line, $m)) {
                    $section = $m[1];
                } elseif ($multi === '' && preg_match('/^([a-z0-9_.\[\]-]+)\s*=\s*(.*)$/i', $line, $m)) {
                    $key = $m[1];
                    $val = $m[2];
                    if (substr($val, -1) !== "\\") {
                        $val = trim($val);
                        self::manage_keys($ini[$section], $key, $val);
                        $multi = '';
                    } else {
                        $multi = substr($val, 0, -1)."\n";
                    }
                } elseif ($multi !== '') {
                    if (substr($line, -1) === "\\") {
                        $multi .= substr($line, 0, -1)."\n";
                    } else {
                        self::manage_keys($ini[$section], $key, $multi.$line);
                        $multi = '';
                    }
                }
            }
        }
        
        $buf = get_defined_constants(true);
        $consts = [];

        foreach ($buf['user'] as $key => $val) {
            $consts['{'.$key.'}'] = $val;
        }
        array_walk_recursive($ini, ['self', 'replace_consts'], $consts);
        return $ini;
    }
    /**
     *  manage keys
     * @param mixed $val
     */
    public static function get_value($val)
    {
        if (preg_match('/^-?[0-9]$/i', $val)) {
            return intval($val);
        } elseif (strtolower($val) === 'true') {
            return true;
        } elseif (strtolower($val) === 'false') {
            return false;
        } elseif (preg_match('/^"(.*)"$/i', $val, $m)) {
            return $m[1];
        } elseif (preg_match('/^\'(.*)\'$/i', $val, $m)) {
            return $m[1];
        }
        return $val;
    }
    /**
     *  manage keys
     * @param mixed $val
     */
    public static function get_key($val)
    {
        if (preg_match('/^[0-9]$/i', $val)) {
            return intval($val);
        }
        return $val;
    }
    /**
     *  manage keys
     * @param mixed $ini
     * @param mixed $key
     * @param mixed $val
     */
    public static function manage_keys(& $ini, $key, $val)
    {
        if (preg_match('/^([a-z0-9_-]+)\.(.*)$/i', $key, $m)) {
            self::manage_keys($ini[$m[1]], $m[2], $val);
        } elseif (preg_match('/^([a-z0-9_-]+)\[(.*)\]$/i', $key, $m)) {
            if ($m[2] !== '') {
                $ini[$m[1]][self::get_key($m[2])] = self::get_value($val);
            } else {
                $ini[$m[1]][] = self::get_value($val);
            }
        } else {
            $ini[self::get_key($key)] = self::get_value($val);
        }
    }
    /**
     *  replace utility
     * @param mixed $item
     * @param mixed $key
     * @param mixed $consts
     */
    public static function replace_consts(& $item, $key, $consts)
    {
        if (is_string($item)) {
            $item = strtr($item, $consts);
        }
    }
}
