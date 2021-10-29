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
 */

require_once("xmlize-php5.inc.php");

define("PXWEATHER_URL", 0);		// to set $_url
define("PXWEATHER_CACHE", 1);		// to set $_cache
define("PXWEATHER_CACHEFOR", 2);		// to set $_age
define("PXWEATHER_CACHEAT", 3);		// to set $_cachepath

class Weather
{
    // The URL from whence to retreive XML weather data
    public $_url = "http://www.weather.unisys.com/forexml.cgi";
    // If true, will use a local file cache
    public $_cache = true;
    // How many minutes to keep the cached data
    public $_age = 60;
    // If cache-enabled, use this directory for storing data
    public $_cachepath = "cache";

    public $_xml = null;		// to store our xml array
    public $_city = "miami";	// The city we're interested in
    public $_force = false;	// If true, will override the cache and fetch data fresh

    /*
    ** Constructor
    */
    public function __construct($city = "Miami", $force = false)
    {
        $this->_city = strtoupper($city);
        $this->_force = $force;
    }

    public function setOption($option, $value)
    {
        switch ($option) {
            case EASYWEATHER_URL:
                $this->_url = $value;
            break;
            
            case EASYWEATHER_CACHE:
                $this->_cache = $value;
            break;
            
            case EASYWEATHER_CACHEFOR:
                $this->_age = $value;
            break;
            
            case EASYWEATHER_CACHEAT:
                $this->_cachepath = $value;
            break;
        }
    }

    public function toString($array = null)
    {
        if ($array == null) {
            $array = $this->_xml;
        }
    }

    public function getCurrent($field)
    {
        $o = $this->_getObservation();
        
        if (@array_key_exists($field, $o)) {
            return $o[$field];
        }
         
        return null;
    }

    public function getSunrise()
    {
        $a = $this->_getAlmanac();
        return $a["sunrise"];
    }

    public function getSunset()
    {
        $a = $this->_getAlmanac();
        return $a["sunset"];
    }

    public function getForecasts()
    {
        $forecasts = $this->_xml["forexml"]["#"]["forecast"];
        $returnArray = [];
        $i = 0;
        foreach ($forecasts as $forecast) {
            $returnArray[$i] = $forecast["@"];
            $i++;
        }

        return $returnArray;
    }

    public function getDaycasts()
    {
        $daycasts = $this->_xml["forexml"]["#"]["daycast"];
        $returnArray = [];
        $i = 0;

        foreach ($daycasts as $daycast) {
            $returnArray[$i] = $daycast["@"];
            $i++;
        }

        return $returnArray;
    }

    /*
    ** Returns a weather condition string given a code (i.e 'TS')
    */
    public function weatherString($code)
    {
        switch ($code) {
            case "TS":
                return "Thunderstorms";
            break;
            
            case "RA":
                return "Rain";
            break;
            
            case "MC":
                return "Mostly cloudy";
            break;
            
            case "SU":
                return "Sunny";
            break;
            
            case "MO":
                return "Mostly clear";
            break;
            
            case "PC":
                return "Partly cloudy";
            break;
            
            case "SN":
                return "Snow";
            break;
            
            case "CL":
                return "Overcast";
            break;
            
            case "FG":
                return "Fog";
            break;
        }
        return null;
    }

    public function load()
    {
        $filename = $this->_cachepath.'/weather/'.$this->_city.'.cache';
        // if not force, check cache for valid file
        if (!$this->_force && $this->_cache) {
            if (file_exists($filename) && filemtime($filename) > (time() - ($this->_age * 60))) {
                $file = fopen($filename, "r");
                $this->_xml = unserialize(fread($file, filesize($filename)));
                fclose($file);
                return;
            }
        }
        // if force, or no valid cache file, get XML fresh
        $this->_xml = $this->_getXML();
        if ($this->_cache) {
            $file = fopen($filename, "w");
            fwrite($file, serialize($this->_xml));
            fclose($file);
        }
    }

    public function _getXML()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_url."?".$this->_city);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $xml = curl_exec($ch);

        if (curl_errno($ch)) {
            return null;
        }
        curl_close($ch);

        return xmlize($xml);
    }

    public function _getObservation()
    {
        return $this->_xml["forexml"]["#"]["observation"][0]["@"];
    }

    // array with keys "sunrise" and "sunset"
    public function _getAlmanac()
    {
        return $this->_xml["forexml"]["#"]["almanac"][0]["@"];
    }
}
