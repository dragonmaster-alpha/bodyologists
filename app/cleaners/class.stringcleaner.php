<?php

namespace App\Cleaners;

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

class cleaner
{
    private $stopwords = [' and ',' if ',' then ',' when ',' how ',' we ',' you ',' they ',' their ',' theirs ',' them ',' me ',' mine ',' my ',' his ',' hers ',' from ',' for ',' to ',' who ',' why ',' is ',' has ',' have ',' got ',' had ',' gotten ',' do ',' will ',' shall ',' should ',' be ',' by ',' could ',' what ',' of ',' this ',' it ',' on ',' the ',' and ',' was ',' yet ',' a ',' an ',' in ',' more ',' are ',' some ',' most ',' must ',' but ',' can ',' with ',' out ',' one ',' up ',' as ',' not ',' that ',' than ',' tan ',' its ',' it ',' way ',' use ',' using '];

    private $symbols = ['/', '\\', '\'', '"', ',', '.', '<', '>', '?', ';', ':', '[', ']', '{', '}', '|' ,'=', '+', '-', '_', ')', '(', '*', '&', '^', '%', '$', '#', '@', '!', '~', '`'];
 
    public function parse_string($string)
    {
        //$string                                               = ' ' . $string . ' ';
        $string = $this->remove_stop_words($string);
        $string = $this->remove_symbols($string);
        
        return trim(str_replace('  ', ' ', $string));
    }
    
    public function remove_stop_words($string)
    {
        for ($i = 0; $i < sizeof($this->stopwords); $i++) {
            $string = str_replace($this->stopwords[$i], ' ', $string);
        }

        return trim($string);
    }
    
    public function remove_symbols($string)
    {
        for ($i = 0; $i < sizeof($this->symbols); $i++) {
            $string = str_replace($this->symbols[$i], ' ', $string);
        }

        return trim($string);
    }
}
