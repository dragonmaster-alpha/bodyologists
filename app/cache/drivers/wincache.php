<?php

class cache_wincache extends cache implements cache_driver  
{
    public function checkdriver() 
    {
        return (extension_loaded('wincache') && function_exists('wincache_ucache_set')) ? true : false;
    }

    public function __construct($option = array()) 
    {
        $this->setOption($option);

        if(!$this->checkdriver() && !isset($option['skipError'])) 
        {
            throw new Exception('Can not use this driver for your website!');
        }
    }

    public function driver_set($keyword, $value = '', $time = 300, $option = array()) 
    {
        if(isset($option['skipExisting']) && $option['skipExisting'] == true) 
        {
            return wincache_ucache_add($keyword, $value, $time);
        } 
        else 
        {
            return wincache_ucache_set($keyword, $value, $time);
        }
    }

    public function driver_get($keyword, $option = array()) 
    {
        $x                                                              = wincache_ucache_get($keyword,$suc);

        return ($suc == false) ? null : $x;
    }

    public function driver_delete($keyword, $option = array()) 
    {
        return wincache_ucache_delete($keyword);
    }

    public function driver_stats($option = array()) 
    {
        $res                                                            = array(
            'info'                                                          =>  '',
            'size'                                                          =>  '',
            'data'                                                          =>  wincache_scache_info(),
        );

        return $res;
    }

    public function driver_clean($option = array()) 
    {
        wincache_ucache_clear();
        return true;
    }

    public function driver_isExisting($keyword) 
    {
        return (wincache_ucache_exists($keyword)) ? true : false;
    }
}