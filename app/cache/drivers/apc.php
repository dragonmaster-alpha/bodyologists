<?php

class cache_apc extends cache implements cache_driver 
{
    public function checkdriver() 
    {
        return (extension_loaded('apc') && ini_get('apc.enabled')) ? true : false;
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
            return apc_add($keyword,$value,$time);
        } 
        else 
        {
            return apc_store($keyword,$value,$time);
        }
    }

    public function driver_get($keyword, $option = array()) 
    {
        $data                                                           = apc_fetch($keyword, $bo);

        if($bo === false) 
        {
            return null;
        }

        return $data;
    }

    public function driver_delete($keyword, $option = array()) 
    {
        return apc_delete($keyword);
    }

    public function driver_stats($option = array()) 
    {
        $res                                                            = array(
            'info'                                                      => '',
            'size'                                                      => '',
            'data'                                                      => '',
        );

        try 
        {
            $res['data']                                                = apc_cache_info('user');
        } 
        catch(Exception $e) 
        {
            $res['data']                                                =  array();
        }

        return $res;
    }

    public function driver_clean($option = array()) 
    {
        @apc_clear_cache();
        @apc_clear_cache('user');
    }

    public function driver_isExisting($keyword) 
    {
        return (apc_exists($keyword)) ? true : false;
    }
}