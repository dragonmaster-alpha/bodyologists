<?php

class cache_memcached extends cache implements cache_driver  
{
    public $instant;
    
    public function checkdriver() 
    {
        if(class_exists('Memcached')) 
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

        $this->instant                                              = new Memcached();
    }
    public function connectServer() 
    {
        $s                                                          = $this->option['server'];
        if(count($s) < 1) 
        {
            $s                                                      = array(
                array('127.0.0.1', 11211, 100),
            );
        }

        foreach($s as $server) 
        {
            $name                                                   = isset($server[0]) ? $server[0] : '127.0.0.1';
            $port                                                   = isset($server[1]) ? $server[1] : 11211;
            $sharing                                                = isset($server[2]) ? $server[2] : 0;
            $checked                                                = $name . '_' . $port;

            if(!isset($this->checked[$checked])) 
            {
                if($sharing > 0) 
                {
                    $this->instant->addServer($name, $port, $sharing);
                } 
                else 
                {
                    $this->instant->addServer($name, $port);
                }

                $this->checked[$checked]                            = 1;
            }
        }
    }
    public function driver_set($keyword, $value = '', $time = 300, $option = array()) 
    {
        $this->connectServer();
        
        if(isset($option['isExisting']) && $option['isExisting'] == true) 
        {
            return $this->instant->add($keyword, $value, time() + $time);
        } 
        else 
        {
            return $this->instant->set($keyword, $value, time() + $time);
        }
    }
    public function driver_get($keyword, $option = array()) 
    {
        $this->connectServer();
        $x                                                          = $this->instant->get($keyword);

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
        $res                                                        = array(
            'info'                                                      => '',
            'size'                                                      => '',
            'data'                                                      => $this->instant->getStats(),
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
        $x                                                          = $this->get($keyword);

        return ($x == null)  ? false : true;
    }
}