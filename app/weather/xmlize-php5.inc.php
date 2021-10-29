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
 * @param mixed $data
 * @param mixed $WHITE
 * @param mixed $encoding
 */

function xmlize($data, $WHITE = 1, $encoding = 'UTF-8')
{
    $data = trim($data);
    $vals = $index = $array = [];
    $parser = xml_parser_create($encoding);

    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, $WHITE);
    xml_parse_into_struct($parser, $data, $vals, $index);
    xml_parser_free($parser);

    $i = 0;
    $tagname = $vals[$i]['tag'];
    if (isset($vals[$i]['attributes'])) {
        $array[$tagname]['@'] = $vals[$i]['attributes'];
    } else {
        $array[$tagname]['@'] = [];
    }
    $array[$tagname]["#"] = xml_depth($vals, $i);
    return $array;
}

/*
 *
 * You don't need to do anything with this function, it's called by
 * xmlize.  It's a recursive function, calling itself as it goes deeper
 * into the xml levels.  If you make any improvements, please let me know.
 *
 *
 */

function xml_depth($vals, &$i)
{
    $children = [];
    if (isset($vals[$i]['value'])) {
        array_push($children, $vals[$i]['value']);
    }

    while (++$i < count($vals)) {
        switch ($vals[$i]['type']) {
           case 'open':

                if (isset($vals[$i]['tag'])) {
                    $tagname = $vals[$i]['tag'];
                } else {
                    $tagname = '';
                }
                if (isset($children[$tagname])) {
                    $size = sizeof($children[$tagname]);
                } else {
                    $size = 0;
                }

                if (isset($vals[$i]['attributes'])) {
                    $children[$tagname][$size]['@'] = $vals[$i]["attributes"];
                }

                $children[$tagname][$size]['#'] = xml_depth($vals, $i);

            break;

            case 'cdata':

                array_push($children, $vals[$i]['value']);

            break;

            case 'complete':

                $tagname = $vals[$i]['tag'];
                if (isset($children[$tagname])) {
                    $size = sizeof($children[$tagname]);
                } else {
                    $size = 0;
                }

                if (isset($vals[$i]['value'])) {
                    $children[$tagname][$size]["#"] = $vals[$i]['value'];
                } else {
                    $children[$tagname][$size]["#"] = '';
                }

                if (isset($vals[$i]['attributes'])) {
                    $children[$tagname][$size]['@'] = $vals[$i]['attributes'];
                }

            break;

            case 'close':

                return $children;
                
            break;
        }
    }
    return $children;
}

/* function by acebone@f2s.com, a HUGE help!
 *
 * this helps you understand the structure of the array xmlize() outputs
 *
 * usage:
 * traverse_xmlize($xml, 'xml_');
 * print '<pre>' . implode("", $traverse_array . '</pre>';
 *
 *
 */

function traverse_xmlize($array, $arrName = "array", $level = 0)
{
    foreach ($array as $key => $val) {
        if (is_array($val)) {
            traverse_xmlize($val, $arrName."[".$key."]", $level + 1);
        } else {
            $GLOBALS['traverse_array'][] = '$'.$arrName.'['.$key.'] = "'.$val."\"\n";
        }
    }
    return 1;
}
