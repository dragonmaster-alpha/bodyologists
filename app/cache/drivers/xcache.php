<?php

class cache_xcache extends cache implements cache_driver  
{
    public function checkdriver() 
    {
        return (extension_loaded('xcache') && function_exists('xcache_get')) ? true : false;
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
            if(!$this->isExisting($keyword)) 
            {
                return xcache_set($keyword, $value, $time);
            }
        } 
        else 
        {
            return xcache_set($keyword, $value, $time);
        }

        return false;
    }

    public function driver_get($keyword, $option = array()) 
    {
        $data                                                       = xcache_get($keyword);

        if($data === false || $data == '') 
        {
            return null;
        }

        return $data;
    }

    public function driver_delete($keyword, $option = array()) 
    {
        return xcache_unset($keyword);
    }

    public function driver_stats($option = array()) 
    {
        $res                                                        = array(
            'info'                                                      =>  '',
            'size'                                                      =>  '',
            'data'                                                      =>  '',
        );

        try 
        {
            $res['data']                                            = xcache_list(XC_TYPE_VAR, 100);
        } 
        catch(Exception $e) 
        {
            $res['data']                                            = array();
        }

        return $res;
    }

    public function driver_clean($option = array()) 
    {
        $cnt                                                        = xcache_count(XC_TYPE_VAR);

        for ($i=0; $i < $cnt; $i++) 
        {
            xcache_clear_cache(XC_TYPE_VAR, $i);
        }
        return true;
    }

    public function driver_isExisting($keyword) 
    {
        return (xcache_isset($keyword)) ? true : false;
    }
}