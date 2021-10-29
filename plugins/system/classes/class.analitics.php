<?php

namespace Plugins\System\Classes;

use Kernel\Classes\Format as Format;

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

class Analytics extends Format
{
    public $auth;
    public $accounts;
    public $email;
    public $password;
    public $site_url;
    
    private $table_id;
    private $debug;

    public function __construct($email, $password, $site_url)
    {
        $this->email = $email;
        $this->password = $password;
        $this->site_url = $site_url;
        
        $ch = $this->curl_init("https://www.google.com/accounts/ClientLogin");
        curl_setopt($ch, CURLOPT_POST, true);
        $data = [
            'accountType' => 'GOOGLE',
            'Email' => $this->email,
            'Passwd' => $this->password,
            'service' => 'analytics',
            'source' => ''
        ];
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        $this->auth = '';
        if ($info['http_code'] == 200) {
            preg_match('/Auth=(.*)/', $output, $matches);
            if (isset($matches[1])) {
                $this->auth = $matches[1];
            }
            $this->load_accounts();
            $this->table_id = $this->get_account_table_id_from_name($this->site_url);
        }
        
        return $this->auth != '';
    }
    
    /**
    * Get Id's from profile name of the account.
    * This name correspond form the  profile of a account. See more in https://developers.google.com/analytics/resources/concepts/gaConceptsAccounts
    *
    *@param string $name
    *@return String TalbeI (ga:12345d)
    */
    public function get_account_table_id_from_name($name)
    {
        return $this->accounts['Google Analytics Profile '.$name ]['tableId'];
    }
    
    /**
     * Calls an API function using the url passed in and returns either the XML returned from the call or false on failure
     *
     * @param string $url
     * @return string or boolean false
     */
    public function call($url)
    {
        try {
            $headers = ["Authorization: GoogleLogin auth=".$this->auth.""];
            $ch = $this->curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $output = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            // set return value to a default of false; it will be changed to the return string on success
            $return = false;
            
            if ($info['http_code'] == 200) {
                $return = $output;
            } elseif ($info['http_code'] == 400) {
                throw new Exception('Badly formatted request to the Google Analytics API; check your profile id is in the format ga:12345, dates are correctly formatted and the dimensions and metrics are correct');
            } elseif ($info['http_code'] == 401) {
                throw new Exception('Unauthorized request to the Google Analytics API', 1);
            } else {
                throw new Exception('Unknown error when accessing the Google Analytics API, HTTP STATUS '.$info['http_code'], 1);
            }
            return $return;
        } catch (Exception $e) {
            $_SESSION['analytics']['message'] = $e->getMessage();
        }
    }
    /**
     * Loads the list of accounts into the $this->accounts associative array. You can then access https://developers.google.com/analytics/devguides/config/mgmt/v2/mgmtProtocol
     * the properties by the profile's domain name.
     */
    public function load_accounts()
    {
        $xml = $this->call('https://www.googleapis.com/analytics/v2.4/management/accounts/~all/webproperties/~all/profiles');
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $entries = $dom->getElementsByTagName('entry');
        $this->accounts = [];
        
        foreach ($entries as $entry) {
            $titles = $entry->getElementsByTagName('title');
            $title = $titles->item(0)->nodeValue;
            $this->accounts[$title] = ['title' => $title];
            $properties = $entry->getElementsByTagName('property');
            foreach ($properties as $property) {
                switch ($property->getAttribute('name')) {
                    case 'ga:accountId':
                        $this->accounts[$title]['accountId'] = $property->getAttribute('value');
                    break;
                    case 'ga:accountName':
                        $this->accounts[$title]['accountName'] = $property->getAttribute('value');
                    break;
                    case 'ga:webPropertyId':
                        $this->accounts[$title]['webPropertyId'] = $property->getAttribute('value');
                    break;
                    case 'ga:profileId':
                        $this->accounts[$title]['profileId'] = $property->getAttribute('value');
                    break;
                    case 'dxp:tableId':
                        $this->accounts[$title]['tableId'] = $property->getAttribute('value');
                    break;
                }
            }
        }
        ksort($this->accounts);
    }
    
    /**
     * Calls the API using the parameters passed in and returns the data in an array.
     *
     * @param string $id The profile's id e.g. ga:7426158
     * @param string $dimension The dimension(s) to use. If more than one dimension is used then
     *   comma separate the values e.g. ga:pagePath or ga:browser,ga:browserVersion
     * @param string $metric The metric(s) to use. If more than one metric is used then
     *   comma separate the values e.g. ga:visits or ga:visits,ga:pageviews
     * @param string $sort The sort order, one of the metrics fields. Use - in front of the name
     *   to reverse sort it. The default is to do a -$metric sort.
     * @param string $start The start date of the data to include in YYYY-MM-DD format. The default
     *   is 1 month ago. It can also be set to "today" which gets data for today only (as much data
     *   for the current day that Analytics can give you), "yesterday" which gets data for yesterday,
     *   or "week" which gets the data for the week to yesterday.
     * @param string $end The end date of the data to include in YYYY-MM-DD format. The default is
     *   yesterday.
     * @param integer $max_results The maximum number of results to retrieve. If the value is greater
     *   than 1000 the API will still only return 1000.
     * @param integer $start_index The index to start from. The first page is 1 (which is the defult)
     *   and the second page, if getting 1000 results at a time, is 1001.
     * @param string|analytics_filters $filters The string to pass as the filters parameter. Refer to:
     *   https://developers.google.com/analytics/devguides/reporting/core/v2/gdataReferenceDataFeed
     *   If it's a string it's appended directly onto the url after '&filters='; if it's an analytics_filters
     *   object then the ->filters property of the object is appended.
     * @param boolean $debug If true will echo the url that is called when making a call to the
     *   analytics API. If run from the CLI will echo it along with a linebreak; otherwise will
     *   put it in a <p> tag and end with a newline
     * @return array Returns an array indexed by the first dimension (then second dimension, etc) with
     *   a value for each metric.
     */
    public function data($dimension, $metric, $sort = false, $start = false, $end = false, $max_results = 10, $start_index = 1, $filters = false, $debug = false)
    {
        if (!$sort) {
            $sort = "-$metric";
        }
        if ($start == 'today') {
            $start = date('Y-m-d');
            $end = $start;
        } elseif ($start == 'yesterday') {
            $start = date('Y-m-d', strtotime('yesterday'));
            $end = $start;
        } elseif ($start == 'week') {
            $start = date('Y-m-d', strtotime('1 week ago'));
            $end = date('Y-m-d', strtotime('yesterday'));
        } else {
            if (!$start) {
                $start = date('Y-m-d', strtotime('1 month ago'));
            }
            if (!$end) {
                $end = date('Y-m-d', strtotime('yesterday'));
            }
        }
                
        $url = "https://www.googleapis.com/analytics/v2.4/data?ids=".$this->table_id."&dimensions=$dimension&metrics=$metric&sort=$sort&start-date=$start&end-date=$end&max-results=$max_results&start-index=$start_index";

        if ($filters) {
            if (is_object($filters) && is_a($filters, 'analytics_filters') && $filters->filters) {
                $url .= "&filters=".$filters->filters;
            } elseif (is_string($filters)) {
                $url .= "&filters=$filters";
            }
        }
        if ($debug) {
            if (PHP_SAPI == 'cli') {
                echo "$url\n";
            } else {
                echo "<p>".htmlentities($url)."</p>\n";
            }
        }
        $xml = $this->call($url);
        if (!$xml) {
            return false;
        }
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $entries = $dom->getElementsByTagName('entry');
        $data = [];
        foreach ($entries as $entry) {
            $index = [];
            foreach ($entry->getElementsByTagName('dimension') as $mydimension) {
                $index[] = $mydimension->getAttribute('value');
            }
        
            // find out how many dimensions are present and have an array index for each dimension
            // if there are no dimensions then the indexes are just the metric names
            // if there's a single dimension the array will be $data['dimension1'] = ...
            // if there's two dimensions the array will be $data['dimension1']['dimension2'] = ...
            // if there's three dimensions the array will be $data['dimension1']['dimension2']['dimension3'] = ...
        
            switch (count($index)) {
                case 0:
                    foreach ($entry->getElementsByTagName('metric') as $metric) {
                        $data[$metric->getAttribute('name')] = $metric->getAttribute('value');
                    }
                break;
                case 1:
                    foreach ($entry->getElementsByTagName('metric') as $metric) {
                        $data[$index[0]][$metric->getAttribute('name')] = $metric->getAttribute('value');
                    }
                break;
                case 2:
                    foreach ($entry->getElementsByTagName('metric') as $metric) {
                        $data[$index[0]][$index[1]][$metric->getAttribute('name')] = $metric->getAttribute('value');
                    }
                break;
                case 3:
                    foreach ($entry->getElementsByTagName('metric') as $metric) {
                        $data[$index[0]][$index[1]][$index[2]][$metric->getAttribute('name')] = $metric->getAttribute('value');
                    }
                break;
            }
        }
        return $data;
    }

    /**
     * Gets a summary for the specified profile and time range containing the number of visits,
     * pageviews, average time on site raw and formatted, and pages per visit
     *
     * @param string $id The profile's id e.g. ga:7426158
     * @param string $start The start date of the data to include in YYYY-MM-DD format. The default
     *   is 1 month ago. It can also be set to "today" which gets data for today only (as much data
     *   for the current day that Analytics can give you), "yesterday" which gets data for yesterday,
     *   or "week" which gets the data for the week to yesterday.
     * @param string $end The end date of the data to include in YYYY-MM-DD format. The default is
     *   yesterday.
     * @param string $filters The filters parameter for the call to the API
     * @param boolean $debug If true will echo the url that is called when making a call to the
     *   analytics API. If run from the CLI will echo it along with a linebreak; otherwise will
     *   put it in a <p> tag and end with a newline
     * @return Returns an array containing the following: ga:visits, ga:pageviews, ga:timeOnSite,
     *   average_time_on_site (in seconds), average_time_on_site_formatted, pages_per_visit
     */
    public function get_summary($start = false, $end = false, $filters = false, $debug = false)
    {
        $data = $this->data('', 'ga:visits,ga:pageviews,ga:timeOnSite', false, $start, $end, 10, 1, $filters, $debug);
        if ($data['ga:visits']) {
            $data['average_time_on_site'] = $data['ga:timeOnSite'] / $data['ga:visits'];
            $data['average_time_on_site_formatted'] = $this->sec2hms($data['ga:timeOnSite'] / $data['ga:visits']);
            $data['pages_per_visit'] = sprintf('%0.2f', ($data['ga:pageviews'] / $data['ga:visits']));
        } else {
            $data['ga:visits'] = 0;
            $data['ga:pageviews'] = 0;
            $data['ga:timeOnSite'] = "0.00";
            $data['average_time_on_site'] = 0;
            $data['average_time_on_site_formatted'] = $this->sec2hms(0);
            $data['pages_per_visit'] = "0.00";
        }
        return $data;
    }
    /**
     * Gets a summary for all profiles to which this login has access, calling the get_summary()
     * function for each account in turn and putting them into a single multi-dimensional array
     * indexed by the account title (i.e. the same index that's used for the ->accounts array)
     *
     * @param string $start The start date of the data to include in YYYY-MM-DD format. The default
     *   is 1 month ago. It can also be set to "today" which gets data for today only (as much data
     *   for the current day that Analytics can give you), "yesterday" which gets data for yesterday,
     *   or "week" which gets the data for the week to yesterday.
     * @param string $end The end date of the data to include in YYYY-MM-DD format. The default is
     *   yesterday.
     * @param string $filters The filters parameter for the call to the API
     * @param boolean $debug If true will echo the url that is called when making a call to the
     *   analytics API. If run from the CLI will echo it along with a linebreak; otherwise will
     *   put it in a <p> tag and end with a newline
     * @return Returns an array containing indexed by the account title (the same index used in the
     *   ->accounts array) with a subarray containing ga:visits, ga:pageviews, ga:timeOnSite,
     *   average_time_on_site (in seconds), average_time_on_site_formatted, pages_per_visit
     */
    public function get_summaries($start = false, $end = false, $filters = false, $debug = false)
    {
        if (!$this->accounts) {
            $this->load_accounts();
        }
        $data = [];
        foreach ($this->accounts as $account) {
            $data[$account['title']] = $this->get_summary($start, $end, $filters, $debug);
        }
        return $data;
    }
    /**
    * This function formats seconds into h:m:s and comes from http://www.laughing-buddha.net/jon/php/sec2hms/
    *
    * @param float $sec The number of seconds
    * @param boolean $padHours If you want a leading zero for less than 10 hours, pass "true"
    */
    public function sec2hms($sec, $padHours = false)
    {
        // holds formatted string
        $hms = '';
        // there are 3600 seconds in an hour, so if we
        // divide total seconds by 3600 and throw away
        // the remainder, we've got the number of hours
        $hours = intval(intval($sec) / 3600);
        // add to $hms, with a leading 0 if asked for
        $hms .= ($padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT).':' : $hours.':';
        // dividing the total seconds by 60 will give us
        // the number of minutes, but we're interested in
        // minutes past the hour: to get that, we need to
        // divide by 60 again and keep the remainder
        $minutes = intval(($sec / 60) % 60);
        // then add to $hms (with a leading 0 if needed)
        $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT).':';
        // seconds are simple - just divide the total
        // seconds by 60 and keep the remainder
        $seconds = intval($sec % 60);
        // add to $hms, again with a leading 0 if needed
        $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
        // done!
        return $hms;
    }
    
    public function parse_seconds($sec, $pad_hours = false)
    {
        $hms = "";
        $hours = intval((int) $sec / 3600);
        $hms .= ($pad_hours) ? str_pad($hours, 2, "0", STR_PAD_LEFT).":" : $hours.":";
        $minutes = intval(($sec / 60) % 60);
        $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT).":";
        $seconds = intval($sec % 60);
        $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
        return $hms;
    }
    /**
    * Returns an instance from curl_init with all the commonly needed properties set.
    *
    * @param $url string The $url to open
    */
    protected function curl_init($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->auth) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: GoogleLogin auth=$this->auth"]);
        }
        // the following thanks to Kyle from www.e-strategy.net
        // i didn't need these settings myself on a Linux box but he seemed to need them on a Windows one
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        return $ch;
    }
}

/**
* Sets up a properly url encoded filter to pass to analytics_api::data
* Refer to http://code.google.com/apis/analytics/docs/gdata/gdataReference.html#filtering for
* more details about filtering
*/
class analytics_filters
{
    /**
    * The filters string that is constructed
    * @var string
    */
    public $filters;
    
    /**
    * Constructor, pass it the initial values for the filter
    *
    * @param string $dimension_or_metric The dimension or metric to filter on e.g. ga:country or ga:browser
    * @param string $comparison The comparison type, == != > < >= <= == != =~ !~ =@ !@
    *   Refer to http://code.google.com/apis/analytics/docs/gdata/gdataReference.html#filtering
    * @param string $value The value to filter on
    */
    public function __construct($dimension_or_metric = '', $comparison = '', $value = '')
    {
        $this->filters = $dimension_or_metric.urlencode($comparison.$value);
    }
    /**
    * Add an "and" condition to the filter
    *
    * @param string $dimension_or_metric The dimension or metric to filter on e.g. ga:country or ga:browser
    * @param string $comparison The comparison type, == != > < >= <= == != =~ !~ =@ !@
    *   Refer to http://code.google.com/apis/analytics/docs/gdata/gdataReference.html#filtering
    * @param string $value The value to filter on
    */
    public function add_and($dimension_or_metric, $comparison, $value)
    {
        $this->filters .= ';'.$dimension_or_metric.urlencode($comparison.$value);
    }
    /**
    * Add an "or" condition to the filter
    *
    * @param string $dimension_or_metric The dimension or metric to filter on e.g. ga:country or ga:browser
    * @param string $comparison The comparison type, == != > < >= <= == != =~ !~ =@ !@
    *   Refer to http://code.google.com/apis/analytics/docs/gdata/gdataReference.html#filtering
    * @param string $value The value to filter on
    */
    public function add_or($dimension_or_metric = '', $comparison = '', $value = '')
    {
        $this->filters .= ','.$dimension_or_metric.urlencode($comparison.$value);
    }
}
