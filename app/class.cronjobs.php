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

class Cronjobs
{
    public static function get()
    {
        $output = shell_exec('crontab -l');
        return self::_to_array($output);
    }
    
    public static function set($jobs = [])
    {
        $output = shell_exec('echo "'.self::_to_string($jobs).'" | crontab -');

        return $output;
    }
    
    public static function check($job = '')
    {
        $jobs = self::get();

        foreach ($jobs as $value) {
            if (stripos($value, 'http://'.$frm->site_domain().'/'.$job) !== false) {
                return true;
            }
        }

        return false;
    }
    
    public static function add_daily_job($job = '')
    {
        if (!self::check($job)) {
            $jobs = self::get();
            $jobs[] = rand(0, 59).' '.rand(0, 23).' * * * curl --user-agent -O http://'.$frm->site_domain().'/'.$job.' >/dev/null';

            return self::set($jobs);
        }
    }

    public static function add_weekly_job($job = '')
    {
        if (!self::check($job)) {
            $jobs = self::get();
            $jobs[] = rand(0, 59).' '.rand(0, 23).' * * '.rand(0, 6).' curl --user-agent -O http://'.$frm->site_domain().'/'.$job.' >/dev/null';

            return self::set($jobs);
        }
    }
    
    public static function add_monthly_job($job = '')
    {
        if (!self::check($job)) {
            $jobs = self::get();
            $jobs[] = rand(0, 59).' '.rand(0, 23).' '.rand(1, 28).' * * curl --user-agent -O http://'.$frm->site_domain().'/'.$job.' >/dev/null';

            return self::set($jobs);
        }
    }

    public static function add_yearly_job($job = '')
    {
        if (!self::check($job)) {
            $jobs = self::get();
            $jobs[] = rand(0, 59).' '.rand(0, 23).' '.rand(1, 28).' '.rand(1, 12).' * curl --user-agent -O http://'.$frm->site_domain().'/'.$job.' >/dev/null';

            return self::set($jobs);
        }
    }

    public static function remove_job($job = '')
    {
        if (self::check($job)) {
            $jobs = self::get();
            
            foreach ($jobs as $key => $value) {
                if (stripos($value, 'http://'.$frm->site_domain().'/'.$job) !== false) {
                    unset($jobs[$key]);
                }
            }

            return self::set($jobs);
        }
        
        return false;
    }

    # Private functions
    private static function _to_array($jobs = '')
    {
        $array = explode("\r\n", trim($jobs));

        foreach ($array as $key => $item) {
            if ($item == '') {
                unset($array[$key]);
            }
        }

        return $array;
    }
    
    private static function _to_string($jobs = [])
    {
        $string = implode("\r\n", $jobs);
        return $string;
    }
}
