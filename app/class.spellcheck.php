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

class Spellcheck
{
    private static $NWORDS;
    
    /**
    * Returns the word that is present on the dictionary that is the most similar (and the most relevant) to the
    * word passed as parameter,
    *
    * @param string $word
    * @return string
    */
    public static function correct($word)
    {
        $word = trim($word);

        if (empty($word)) {
            return;
        }
        
        $word = strtolower($word);
        
        if (empty(self::$NWORDS)) {
            /* To optimize performance, the serialized dictionary can be saved on a file
            instead of parsing every single execution */
            if (!file_exists($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/config/serialized_dictionary.txt')) {
                self::$NWORDS = self::train(self::words(file_get_contents($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/config/dictionary.txt')));
                $fp = fopen($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/config/serialized_dictionary.txt', 'w+');
                fwrite($fp, serialize(self::$NWORDS));
                fclose($fp);
            } else {
                self::$NWORDS = unserialize(file_get_contents($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/config/serialized_dictionary.txt'));
            }
        }

        $candidates = [];

        if (self::known([$word])) {
            return $word;
        } elseif (($tmp_candidates = self::known(self::edits1($word)))) {
            foreach ($tmp_candidates as $candidate) {
                $candidates[] = $candidate;
            }
        } elseif (($tmp_candidates = self::known_edits2($word))) {
            foreach ($tmp_candidates as $candidate) {
                $candidates[] = $candidate;
            }
        } else {
            return $word;
        }

        $max = 0;

        foreach ($candidates as $c) {
            $value = self::$NWORDS[$c];

            if ($value > $max) {
                $max = $value;
                $word = $c;
            }
        }

        return $word;
    }
    
    /**
    * Reads a text and extracts the list of words
    *
    * @param string $text
    * @return array The list of words
    */
    private static function words($text)
    {
        $matches = [];
        preg_match_all("/[a-z]+/", strtolower($text), $matches);
        return $matches[0];
    }
    
    /**
    * Creates a table (dictionary) where the word is the key and the value is it's relevance
    * in the text (the number of times it appear)
    *
    * @param array $features
    * @return array
    */
    private static function train(array $features)
    {
        $model = [];
        $count = count($features);

        for ($i = 0; $i < $count; $i++) {
            $f = $features[$i];
            $model[$f] += 1;
        }
        return $model;
    }
    
    /**
    * Generates a list of possible "disturbances" on the passed string
    *
    * @param string $word
    * @return array
    */
    private static function edits1($word)
    {
        $alphabet = str_split('abcdefghijklmnopqrstuvwxyz');
        $n = strlen($word);
        $edits = [];

        for ($i = 0 ; $i < $n; $i++) {
            $edits[] = substr($word, 0, $i).substr($word, $i + 1); //deleting one char

            foreach ($alphabet as $c) {
                $edits[] = substr($word, 0, $i).$c.substr($word, $i + 1); //substituting one char
            }
        }

        for ($i = 0; $i < $n - 1; $i++) {
            $edits[] = substr($word, 0, $i).$word[$i + 1].$word[$i].substr($word, $i + 2); //swapping chars order
        }

        for ($i = 0; $i < $n + 1; $i++) {
            foreach ($alphabet as $c) {
                $edits[] = substr($word, 0, $i).$c.substr($word, $i); //inserting one char
            }
        }

        return $edits;
    }
    
    /**
    * Generate possible "disturbances" in a second level that exist on the dictionary
    *
    * @param string $word
    * @return array
    */
    private static function known_edits2($word)
    {
        $known = [];

        foreach (self::edits1($word) as $e1) {
            foreach (self::edits1($e1) as $e2) {
                if (array_key_exists($e2, self::$NWORDS)) {
                    $known[] = $e2;
                }
            }
        }

        return $known;
    }
    
    /**
    * Given a list of words, returns the subset that is present on the dictionary
    *
    * @param array $words
    * @return array
    */
    private static function known(array $words)
    {
        $known = [];

        foreach ($words as $w) {
            if (array_key_exists($w, self::$NWORDS)) {
                $known[] = $w;
            }
        }

        return $known;
    }
}
