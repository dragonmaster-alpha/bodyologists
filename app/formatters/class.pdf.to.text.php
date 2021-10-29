<?php

namespace App\Formatters;

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

/**
 * Usage
 *
 * $a 													= new PDF2Text('test.pdf');
 * $a->decode_pdf();
 * echo $a->output();
 *
 **/

class PDF2TEXT
{
    private $multibyte = 2; // Use set_unicode(TRUE|FALSE)
    private $convertquotes = ENT_QUOTES; // ENT_COMPAT (double-quotes), ENT_QUOTES (Both), ENT_NOQUOTES (None)
    private $filename = '';
    private $decodedtext = '';
    
    public function __construct($filename = '')
    {
        $this->decodedtext = '';
        $this->filename = $filename;
    }

    public function output($echo = false)
    {
        if ($echo) {
            echo $this->decodedtext;
        } else {
            return $this->decodedtext;
        }
    }

    public function set_unicode($input = false)
    {
        if ($input == true) {
            $this->multibyte = 4;
        } else {
            $this->multibyte = 2;
        }
    }

    public function decode_pdf()
    {
        $infile = @file_get_contents($this->filename, FILE_BINARY);
        
        if (empty($infile)) {
            return '';
        }

        // Get all text data.
        $transformations = [];
        $texts = [];
    
        // Get the list of all objects.
        preg_match_all("#obj[\n|\r](.*)endobj[\n|\r]#ismU", $infile, $objects);
        $objects = @$objects[1];
    
        // Select objects with streams.
        for ($i = 0; $i < count($objects); $i++) {
            $currentObject = $objects[$i];
    
            // Check if an object includes data stream.
            if (preg_match("#stream[\n|\r](.*)endstream[\n|\r]#ismU", $currentObject, $stream)) {
                $stream = ltrim($stream[1]);
    
                // Check object parameters and look for text data.
                $options = $this->get_object_options($currentObject);
    
                if (!(empty($options["Length1"]) && empty($options["Type"]) && empty($options["Subtype"]))) {
                    continue;
                }

                // Hack, length doesnt always seem to be correct
                unset($options["Length"]);
    
                // So, we have text data. Decode it.
                $data = $this->get_decoded_stream($stream, $options);
    
                if (strlen($data)) {
                    if (preg_match_all("#BT[\n|\r](.*)ET[\n|\r]#ismU", $data, $textContainers)) {
                        $textContainers = @$textContainers[1];
                        $this->get_dirty_texts($texts, $textContainers);
                    } else {
                        $this->get_char_transformations($transformations, $data);
                    }
                }
            }
        }
        // Analyze text blocks taking into account character transformations and return results.
        $this->decodedtext = $this->get_text_using_transformations($texts, $transformations);
    }

    public function decode_ascii_hex($input)
    {
        $output = "";
        $isOdd = true;
        $isComment = false;
    
        for ($i = 0, $codeHigh = -1; $i < strlen($input) && $input[$i] != '>'; $i++) {
            $c = $input[$i];
            
            if ($isComment) {
                if ($c == '\r' || $c == '\n') {
                    $isComment = false;
                    continue;
                }
            }
    
            switch ($c) {
                case '\0': case '\t': case '\r': case '\f': case '\n': case ' ': break;
                case '%':

                    $isComment = true;

                break;
    
                default:

                    $code = hexdec($c);

                    if ($code === 0 && $c != '0') {
                        return '';
                    }
                    if ($isOdd) {
                        $codeHigh = $code;
                    } else {
                        $output .= chr($codeHigh * 16 + $code);
                    }

                    $isOdd = !$isOdd;

                break;
            }
        }
    
        if ($input[$i] != '>') {
            return '';
        }
        if ($isOdd) {
            $output .= chr($codeHigh * 16);
        }
        return $output;
    }
    
    public function decode_ascii_85($input)
    {
        $output = "";
        $isComment = false;
        $ords = [];
        
        for ($i = 0, $state = 0; $i < strlen($input) && $input[$i] != '~'; $i++) {
            $c = $input[$i];
            if ($isComment) {
                if ($c == '\r' || $c == '\n') {
                    $isComment = false;
                    continue;
                }
            }
    
            if ($c == '\0' || $c == '\t' || $c == '\r' || $c == '\f' || $c == '\n' || $c == ' ') {
                continue;
            }

            if ($c == '%') {
                $isComment = true;
                continue;
            }

            if ($c == 'z' && $state === 0) {
                $output .= str_repeat(chr(0), 4);
                continue;
            }

            if ($c < '!' || $c > 'u') {
                return '';
            }

            $code = ord($input[$i]) & 0xff;
            $ords[$state++] = $code - ord('!');
    
            if ($state == 5) {
                $state = 0;
                for ($sum = 0, $j = 0; $j < 5; $j++) {
                    $sum = $sum * 85 + $ords[$j];
                }
                for ($j = 3; $j >= 0; $j--) {
                    $output .= chr($sum >> ($j * 8));
                }
            }
        }

        if ($state === 1) {
            return '';
        } elseif ($state > 1) {
            for ($i = 0, $sum = 0; $i < $state; $i++) {
                $sum += ($ords[$i] + ($i == $state - 1)) * pow(85, 4 - $i);
            }
            for ($i = 0; $i < $state - 1; $i++) {
                $ouput .= chr($sum >> ((3 - $i) * 8));
            }
        }

        return $output;
    }
    
    public function decode_flate($input)
    {
        return gzuncompress($input);
    }
    
    public function get_object_options($object)
    {
        $options = [];

        if (preg_match("#<<(.*)>>#ismU", $object, $options)) {
            $options = explode("/", $options[1]);
            @array_shift($options);
    
            $o = [];
            
            for ($j = 0; $j < @count($options); $j++) {
                $options[$j] = preg_replace("#\s+#", " ", trim($options[$j]));
                
                if (strpos($options[$j], " ") !== false) {
                    $parts = explode(" ", $options[$j]);
                    $o[$parts[0]] = $parts[1];
                } else {
                    $o[$options[$j]] = true;
                }
            }

            $options = $o;
            unset($o);
        }
    
        return $options;
    }
    
    public function get_decoded_stream($stream, $options)
    {
        $data = '';

        if (empty($options["Filter"])) {
            $data = $stream;
        } else {
            $length = !empty($options["Length"]) ? $options["Length"] : strlen($stream);
            $_stream = substr($stream, 0, $length);

            foreach ($options as $key => $value) {
                if ($key == "ASCIIHexDecode") {
                    $_stream = $this->decode_ascii_hex($_stream);
                }
                if ($key == "ASCII85Decode") {
                    $_stream = $this->decode_ascii_85($_stream);
                }
                if ($key == "FlateDecode") {
                    $_stream = $this->decode_flate($_stream);
                }
                if ($key == "Crypt") { // TO DO
                }
            }

            $data = $_stream;
        }

        return $data;
    }

    public function get_dirty_texts(&$texts, $textContainers)
    {
        for ($j = 0; $j < count($textContainers); $j++) {
            if (preg_match_all("#\[(.*)\]\s*TJ[\n|\r]#ismU", $textContainers[$j], $parts)) {
                $texts = array_merge($texts, @$parts[1]);
            } elseif (preg_match_all("#T[d|w|m|f]\s*(\(.*\))\s*Tj[\n|\r]#ismU", $textContainers[$j], $parts)) {
                $texts = array_merge($texts, @$parts[1]);
            } elseif (preg_match_all("#T[d|w|m|f]\s*(\[.*\])\s*Tj[\n|\r]#ismU", $textContainers[$j], $parts)) {
                $texts = array_merge($texts, @$parts[1]);
            }
        }
    }

    public function get_char_transformations(&$transformations, $stream)
    {
        preg_match_all("#([0-9]+)\s+beginbfchar(.*)endbfchar#ismU", $stream, $chars, PREG_SET_ORDER);
        preg_match_all("#([0-9]+)\s+beginbfrange(.*)endbfrange#ismU", $stream, $ranges, PREG_SET_ORDER);
    
        for ($j = 0; $j < count($chars); $j++) {
            $count = $chars[$j][1];
            $current = explode("\n", trim($chars[$j][2]));

            for ($k = 0; $k < $count && $k < count($current); $k++) {
                if (preg_match("#<([0-9a-f]{2,4})>\s+<([0-9a-f]{4,512})>#is", trim($current[$k]), $map)) {
                    $transformations[str_pad($map[1], 4, "0")] = $map[2];
                }
            }
        }

        for ($j = 0; $j < count($ranges); $j++) {
            $count = $ranges[$j][1];
            $current = explode("\n", trim($ranges[$j][2]));

            for ($k = 0; $k < $count && $k < count($current); $k++) {
                if (preg_match("#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+<([0-9a-f]{4})>#is", trim($current[$k]), $map)) {
                    $from = hexdec($map[1]);
                    $to = hexdec($map[2]);
                    $_from = hexdec($map[3]);
    
                    for ($m = $from, $n = 0; $m <= $to; $m++, $n++) {
                        $transformations[sprintf("%04X", $m)] = sprintf("%04X", $_from + $n);
                    }
                } elseif (preg_match("#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+\[(.*)\]#ismU", trim($current[$k]), $map)) {
                    $from = hexdec($map[1]);
                    $to = hexdec($map[2]);
                    $parts = preg_split("#\s+#", trim($map[3]));
                    
                    for ($m = $from, $n = 0; $m <= $to && $n < count($parts); $m++, $n++) {
                        $transformations[sprintf("%04X", $m)] = sprintf("%04X", hexdec($parts[$n]));
                    }
                }
            }
        }
    }

    public function get_text_using_transformations($texts, $transformations)
    {
        $document = '';

        for ($i = 0; $i < count($texts); $i++) {
            $isHex = false;
            $isPlain = false;
            $hex = "";
            $plain = "";

            for ($j = 0; $j < strlen($texts[$i]); $j++) {
                $c = $texts[$i][$j];
                
                switch ($c) {
                    case "<":

                        $hex = '';
                        $isHex = true;

                    break;

                    case ">":

                        $hexs = str_split($hex, $this->multibyte); // 2 or 4 (UTF8 or ISO)

                        for ($k = 0; $k < count($hexs); $k++) {
                            $chex = str_pad($hexs[$k], 4, "0"); // Add tailing zero
                            if (isset($transformations[$chex])) {
                                $chex = $transformations[$chex];
                            }
                            $document .= html_entity_decode("&#x".$chex.";");
                        }

                        $isHex = false;

                    break;

                    case "(":

                        $plain = '';
                        $isPlain = true;

                    break;

                    case ")":

                        $document .= $plain;
                        $isPlain = false;

                    break;

                    case "\\":

                        $c2 = $texts[$i][$j + 1];

                        if (in_array($c2, ["\\", "(", ")"])) {
                            $plain .= $c2;
                        } elseif ($c2 == "n") {
                            $plain .= '\n';
                        } elseif ($c2 == "r") {
                            $plain .= '\r';
                        } elseif ($c2 == "t") {
                            $plain .= '\t';
                        } elseif ($c2 == "b") {
                            $plain .= '\b';
                        } elseif ($c2 == "f") {
                            $plain .= '\f';
                        } elseif ($c2 >= '0' && $c2 <= '9') {
                            $oct = preg_replace("#[^0-9]#", "", substr($texts[$i], $j + 1, 3));
                            $j += strlen($oct) - 1;
                            $plain .= html_entity_decode("&#".octdec($oct).";", $this->convertquotes);
                        }

                        $j++;

                    break;
    
                    default:

                        if ($isHex) {
                            $hex .= $c;
                        }

                        if ($isPlain) {
                            $plain .= $c;
                        }

                    break;
                }
            }

            $document .= "\n";
        }

        return $document;
    }
}
