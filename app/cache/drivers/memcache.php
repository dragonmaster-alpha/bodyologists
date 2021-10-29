<?php

class cache_memcache extends cache implements cache_driver 
{
    public $instant;

    public function checkdriver() 
    {
        if(function_exists('memcache_connect')) 
        {
            return true;
        }
        return false;
    }
    public function __construct($option = array()) 
    {
        $this->setOption($option);
        if(!$this->checkdriver() && !isset($option['skipError'])) 
        {
            throw new Exception('Can not use this driver for your website!');
        }
        $this->instant                                                          = new Memcache();
    }
    public function connectServer() 
    {
        $server                                                                 = $this->option['server'];

        if(count($server) < 1) 
        {
            $server                                                             = array(
                array('127.0.0.1',11211),
            );
        }

        foreach($server as $s) 
        {
            $name                                                               = $s[0] . '_' . $s[1];
            if(!isset($this->checked[$name])) 
            {
                $this->instant->addserver($s[0], $s[1]);
                $this->checked[$name]                                           = 1;
            }
        }
    }
    public function driver_set($keyword, $value = '', $time = 300, $option = array() ) 
    {
        $this->connectServer();

        if(isset($option['skipExisting']) && $option['skipExisting'] == true) 
        {
            return $this->instant->add($keyword, $value, false, $time );
        } 
        else 
        {
            return $this->instant->set($keyword, $value, false, $time );
        }
    }
    public function driver_get($keyword, $option = array()) 
    {
        $this->connectServer();

        $x                                                                      = $this->instant->get($keyword);
        return ($x == false) ? null : $x;
    }
    public function driver_delete($keyword, $option = array()) 
    {
        $this->connectServer();
        $this->instant->delete($keyword);
    }
    public function driver_stats($option = array()) 
    {
        $this->connectServer();
        $res                                                                    = array(
            'info'                                                                  => '',
            'size'                                                                  => '',
            'data'                                                                  => $this->instant->getStats(),
        );

        return $res;
    }
    public function driver_clean($option = array()) 
    {
        $this->connectServer();
        $this->instant->flush();
    }
    public function driver_isExisting($keyword) 
    {
        $this->connectServer();
        $x                                                                  = $this->get($keyword);

        return ($x == null)  ? false : true;
    }
}