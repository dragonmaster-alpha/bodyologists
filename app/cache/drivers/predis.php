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

class cache_predis extends cache implements cache_driver 
{
	public $checked_redis = false;

    public function checkdriver() 
    {
        // Check memcache
	    $this->required_extension("predis-1.0/autoload.php");

	    try 
	    {
		    Predis\Autoloader::register();
	    } 
	    catch(Exception $e) 
	    {

	    }
	    return true;
    }

    public function __construct($config = array()) 
    {
        $this->setup($config);
	    $this->required_extension("predis-1.0/autoload.php");
    }

    public function connectServer() 
    {
	    $server 													= isset($this->option['redis']) ? $this->option['redis'] : array('host' => '127.0.0.1', 'port'  =>  '6379', 'password'  =>  '', 'database'  =>  '');

	    if($this->checked_redis === false) 
	    {
			$c 														= array("host"  => $server['host'],);

		    $port 													= isset($server['port']) ? $server['port'] : '';

		    if($port != '') 
		    {
			    $c['port'] 											= $port;
		    }

		    $password 												= isset($server['password']) ? $server['password'] : '';

		    if($password != '') 
		    {
			    $c['password'] 										= $password;
		    }

		    $database 												= isset($server['database']) ? $server['database'] : '';

		    if($database != '') 
		    {
			    $c['database'] 										= $database;
		    }

		    $timeout 												= isset($server['timeout']) ? $server['timeout'] : '';

		    if($timeout != '') 
		    {
			    $c['timeout'] 										= $timeout;
		    }

		    $read_write_timeout 									= isset($server['read_write_timeout']) ? $server['read_write_timeout'] : '';

		    if($read_write_timeout != '') 
		    {
			    $c['read_write_timeout'] 							= $read_write_timeout;
		    }

		    $this->instant 											= new Predis\Client($c);

		    $this->checked_redis 									= true;

		    if(!$this->instant) 
		    {
			    $this->fallback 									= true;
			    return false;
		    } 
		    else 
		    {
			    return true;
		    }
	    }

	    return true;
    }

    public function driver_set($keyword, $value = "", $time = 300, $option = array() ) 
    {
        if($this->connectServer()) 
        {
	        $value 													= $this->encode($value);

	        if (isset($option['skipExisting']) && $option['skipExisting'] == true) 
	        {
		        return $this->instant->setex($keyword, $time, $value);
	        } 
	        else 
	        {
		        return $this->instant->setex($keyword, $time, $value );
	        }
        } 
        else 
        {
			return $this->backup()->set($keyword, $value, $time, $option);
        }
    }

    public function driver_get($keyword, $option = array()) 
    {
        if($this->connectServer()) 
        {
	        // return null if no caching
	        // return value if in caching'
	        $x 														= $this->instant->get($keyword);
	        
	        if($x == false) 
	        {
		        return null;
	        } 
	        else 
	        {
		        return $this->decode($x);
	        }
        } 
        else 
        {
			$this->backup()->get($keyword, $option);
        }

    }

    public function driver_delete($keyword, $option = array()) 
    {
        if($this->connectServer()) 
        {
	        $this->instant->delete($keyword);
        }
    }

    public function driver_stats($option = array()) 
    {
        if($this->connectServer()) 
        {
	        $res 													= array("info"  => "", "size"  =>  "", "data"  => $this->instant->info());
	        return $res;
        }

	    return array();
    }

    public function driver_clean($option = array()) 
    {
        if($this->connectServer()) 
        {
	        $this->instant->flushDB();
        }
    }

    public function driver_isExisting($keyword) 
    {
        if($this->connectServer()) 
        {
	        $x 														= $this->instant->exists($keyword);

	        return ($x == null) ? false : true;
        } 
        else 
        {
	        return $this->backup()->isExisting($keyword);
        }
    }
}