<?php

class cache_redis extends cache implements cache_driver
{
    var $checked_redis 														= false;
    
    public function checkdriver() 
    {
        if (class_exists('Redis')) 
        {
            return true;
        }

        $this->fallback 													= true;

        return false;
    }
    
    public function __construct($config = array()) 
    {
        $this->setup($config);
        
        if (!$this->checkdriver() && !isset($config['skipError'])) 
        {
            $this->fallback 												= true;
        }
        
        if (class_exists('Redis')) 
        {
            $this->instant 													= new Redis();
        }
    }
    
    public function connectServer() 
    {
        $server 															= isset($this->option['redis']) ? $this->option['redis'] : array('host' => '127.0.0.1', 'port' => '6379', 'password' => '', 'database' => '', 'timeout' => '1');
        
        if ($this->checked_redis === false) 
        {
            $host 															= $server['host'];
            $port 															= isset($server['port']) ? (int) $server['port'] : "";
            
            if (!empty($port)) 
            {
                $c['port'] 													= $port;
            }
            
            $password 														= isset($server['password']) ? $server['password'] : "";
            
            if (!empty($password))
            {
                $c['password'] 												= $password;
            }
            
            $database 														= isset($server['database']) ? $server['database'] : '';
            
            if (!empty($database)) 
            {
                $c['database'] 												= $database;
            }
            
            $timeout 														= isset($server['timeout']) ? $server['timeout'] : '';
            
            if (!empty($timeout)) 
            {
                $c['timeout'] 												= $timeout;
            }
            
            $read_write_timeout 											= isset($server['read_write_timeout']) ? $server['read_write_timeout'] : '';
            
            if (!empty($read_write_timeout))
            {
                $c['read_write_timeout'] 									= $read_write_timeout;
            }
            
            if (!$this->instant->connect($host, (int) $port, (int) $timeout)) 
            {
                $this->checked_redis 										= true;
                $this->fallback 											= true;

                return false;
            } 
            else
            {
                if (!empty($database)) 
                {
                    $this->instant->select((int) $database);
                }

                $this->checked_redis 										= true;

                return true;
            }
        }
        
        return true;
    }
    
    public function driver_set($keyword, $value = '', $time = 300, $option = array()) 
    {
        if ($this->connectServer()) 
        {
            $value 															= $this->encode($value);
            
            if (isset($option['skipExisting']) && $option['skipExisting'] == true) 
            {
                return $this->instant->set($keyword, $value, array('xx', 'ex' => $time));
            } 
            else
            {
                return $this->instant->set($keyword, $value, $time);
            }
        } 
        else
        {
            return $this->backup()->set($keyword, $value, $time, $option);
        }
    }
    
    public function driver_get($keyword, $option = array()) 
    {
        if ($this->connectServer()) 
        {
            $x 																= $this->instant->get($keyword);
            return ($x == false) ? null : $this->decode($x);
        } 
        else
        {
            $this->backup()->get($keyword, $option);
        }
    }
    
    public function driver_delete($keyword, $option = array()) 
    {
        if ($this->connectServer()) 
        {
            $this->instant->delete($keyword);
        }
    }
    
    public function driver_stats($option = array()) 
    {
        if ($this->connectServer()) 
        {
            $res 															= array('info' => '', 'size' => '', 'data' => $this->instant->info());
            return $res;
        }
        
        return array();
    }
    
    public function driver_clean($option = array()) 
    {
        if ($this->connectServer()) 
        {
            $this->instant->flushDB();
        }
    }
    
    public function driver_isExisting($keyword) 
    {
        if ($this->connectServer()) 
        {
            $x 																= $this->instant->exists($keyword);
            return ($x == null) ? false : true;
        } 
        else
        {
            return $this->backup()->isExisting($keyword);
        }
    }
}
