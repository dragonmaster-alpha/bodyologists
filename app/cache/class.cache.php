<?php

namespace App\Cache;

require_once(dirname(__FILE__) . '/class.driver.php');
use App\Cache\Driver as Driver;

if(!function_exists('__c')) 
{
    function __c($storage = '', $option = array()) 
    {
        return Cache($storage, $option);
    }
}

if(!function_exists('cache')) 
{
    function cache($storage = '', $option = array()) 
    {
        if(!isset(cache_instances::$instances[$storage])) 
        {
            cache_instances::$instances[$storage]                       = new Cache($storage, $option);
        }

        return cache_instances::$instances[$storage];
    }
}

class cache_instances 
{
    public static $instances                                            = array();
}

class Cache 
{
    public static $storage                                              = 'auto';
    public static $config                                               = array(
        'storage'                                                           =>  'auto',
        'fallback'                                                          =>  array(
            'example'                                                           =>  'files',
        ),
        'securityKey'                                                       => 'auto',
        'htaccess'                                                          => true,
        'path'                                                              => '',
        'server'                                                            => array(
            array('127.0.0.1', 11211, 1)
        ),
        'extensions'                                                        => array(),
    );
    public $tmp                                                         = array();
    public  $checked                                                    = array(
        'path'                                                              => false,
        'fallback'                                                          => false,
        'hook'                                                              => false,
    );
    public $is_driver                                                   = false;
    public $driver                                                      = null;
    public $option                                                      = array(
        'path'                                                              => _CACHE_HANDLER, // path for cache folder
        'htaccess'                                                          => null, // auto create htaccess
        'securityKey'                                                       => _DB_PREFIX,  // Key Folder, Setup Per Domain will good.
        'system'                                                            => array(),
        'storage'                                                           => '',
        'cachePath'                                                         => '',
    );

    public function __construct($storage = 'files', $option = array()) 
    {   
        if(isset(self::$config['fallback'][$storage])) 
        {
            $storage                                                    = self::$config['fallback'][$storage];
        }

        if($storage == '') 
        {
            $storage                                                    = self::$storage;
            self::option("storage", $storage);
        } 
        else 
        {
            self::$storage                                              = $storage;
        }

        $this->tmp['storage']                                           = $storage;
        $this->option                                                   = @array_merge($this->option, self::$config, $option);
        
        if($storage != 'auto' && $storage != '' && $this->isExistingDriver($storage)) 
        {
            $driver                                                     = 'cache_' . $storage;
        } 
        else 
        {
            $storage                                                    = $this->autoDriver();
            self::$storage                                              = $storage;
            $driver                                                     = 'cache_' . $storage;
        }

        require_once(dirname(__FILE__) . '/drivers/' . $storage . '.php');
        
        $this->option('storage', $storage);

        if($this->option['securityKey'] == 'auto' || $this->option['securityKey'] == '') 
        {
            $this->option['securityKey']                                = 'cache.storage.site';
        }

        $this->driver                                                   = new $driver($this->option);
        $this->driver->is_driver                                        = true;
    }

    /*
     * Basic Method
     */
    public function set($keyword, $value = '', $time = 600, $option = array() ) 
    {
        $object = array(
            'value' => $value,
            'write_time' => @date('U'),
            'expired_in' => $time,
            'expired_time' => @date('U') + (int) $time,
        );

        return ($this->is_driver == true) ?
            $this->driver_set($keyword, $object, $time, $option) :
            $this->driver->driver_set($keyword, $object, $time, $option);
    }

    public function get($keyword, $option = array()) 
    {
        $object = ($this->is_driver == true) ? $this->driver_get($keyword, $option) : $this->driver->driver_get($keyword, $option);

        if($object == null) {
            return null;
        }

        return $object['value'];
    }

    public function getInfo($keyword, $option = array()) 
    {
        $object = ($this->is_driver == true) ? $this->driver_get($keyword, $option) : $this->driver->driver_get($keyword, $option);

        if($object == null) 
        {
            return null;
        }

        return $object;
    }

    public function delete($keyword, $option = array()) 
    {
        return ($this->is_driver == true) ? $this->driver_delete($keyword, $option) : $this->driver->driver_delete($keyword, $option);
    }

    public function stats($option = array()) 
    {
        return ($this->is_driver == true) ? $this->driver_stats($option) : $this->driver->driver_stats($option);
    }

    public function clean($option = array()) 
    {
        return ($this->is_driver == true) ? $this->driver_clean($option) : $this->driver->driver_clean($option);
    }

    public function isExisting($keyword) 
    {
        if($this->is_driver == true) 
        {
            if(method_exists($this, 'driver_isExisting')) 
            {
                return $this->driver_isExisting($keyword);
            }
        } 
        else 
        {
            if(method_exists($this->driver, 'driver_isExisting')) 
            {
                return $this->driver->driver_isExisting($keyword);
            }
        }

        $data                                                           = $this->get($keyword);

        return ($data == null) ? false : true;
    }

    public function increment($keyword, $step = 1 , $option = array()) 
    {
        $object                                                         = $this->get($keyword);

        if($object == null) 
        {
            return false;
        } 
        else 
        {
            $value                                                      = (int) $object['value'] + (int) $step;
            $time                                                       = $object['expired_time'] - @date('U');
            $this->set($keyword,$value, $time, $option);

            return true;
        }
    }

    public function decrement($keyword, $step = 1 , $option = array()) 
    {
        $object                                                         = $this->get($keyword);

        if($object == null) 
        {
            return false;
        } 
        else 
        {
            $value                                                      = (int) $object['value'] - (int) $step;
            $time                                                       = $object['expired_time'] - @date('U');
            $this->set($keyword, $value, $time, $option);

            return true;
        }
    }
    /*
     * Extend more time
     */
    public function touch($keyword, $time = 300, $option = array()) 
    {
        $object                                                         = $this->get($keyword);

        if($object == null) 
        {
            return false;
        } 
        else 
        {
            $value                                                      = $object['value'];
            $time                                                       = $object['expired_time'] - @date('U') + $time;
            $this->set($keyword, $value, $time, $option);

            return true;
        }
    }
    /*
    * Other Functions Built-int for cache since 1.3
    */

    public function setMulti($list = array()) 
    {
        foreach($list as $array) 
        {
            $this->set($array[0], isset($array[1]) ? $array[1] : 300, isset($array[2]) ? $array[2] : array());
        }
    }

    public function getMulti($list = array()) 
    {
        $res                                                            = array();

        foreach($list as $array) 
        {
            $name                                                       = $array[0];
            $res[$name]                                                 = $this->get($name, isset($array[1]) ? $array[1] : array());
        }

        return $res;
    }

    public function getInfoMulti($list = array()) 
    {
        $res                                                            = array();

        foreach($list as $array) 
        {
            $name                                                       = $array[0];
            $res[$name]                                                 = $this->getInfo($name, isset($array[1]) ? $array[1] : array());
        }

        return $res;
    }

    public function deleteMulti($list = array()) 
    {
        foreach($list as $array) 
        {
            $this->delete($array[0], isset($array[1]) ? $array[1] : array());
        }
    }

    public function isExistingMulti($list = array()) 
    {
        $res                                                            = array();

        foreach($list as $array) 
        {
            $name                                                       = $array[0];
            $res[$name] = $this->isExisting($name);
        }

        return $res;
    }

    public function incrementMulti($list = array()) 
    {
        $res                                                            = array();

        foreach($list as $array) 
        {
            $name                                                       = $array[0];
            $res[$name]                                                 = $this->increment($name, $array[1], isset($array[2]) ? $array[2] : array());
        }

        return $res;
    }

    public function decrementMulti($list = array()) 
    {
        $res                                                            = array();

        foreach($list as $array) 
        {
            $name                                                       = $array[0];
            $res[$name]                                                 = $this->decrement($name, $array[1], isset($array[2]) ? $array[2] : array());
        }

        return $res;
    }

    public function touchMulti($list = array()) 
    {
        $res                                                            = array();

        foreach($list as $array) 
        {
            $name                                                       = $array[0];
            $res[$name]                                                 = $this->touch($name, $array[1], isset($array[2]) ? $array[2] : array());
        }

        return $res;
    }
    /*
     * Begin Parent Classes;
     */
    public static function setup($name, $value = '') 
    {
        if(!is_array($name)) 
        {
            if($name == "storage") 
            {
                self::$storage                                          = $value;
            }

            self::$config[$name]                                        = $value;
        } 
        else 
        {
            foreach($name as $n => $value) 
            {
                self::setup($n, $value);
            }
        }
    }
    /*
     * For Auto Driver
     *
     */
    public function autoDriver() 
    {
        $driver                                                         = 'files';

        if(extension_loaded('apc') && ini_get('apc.enabled') && strpos(PHP_SAPI, 'CGI') === false)
        {
            $driver                                                     = 'apc';
        }
        else if(extension_loaded('pdo_sqlite') && is_writeable($this->getPath())) 
        {
            $driver                                                     = 'sqlite';
        }
        else if(is_writeable($this->getPath())) 
        {
            $driver                                                     = 'files';
        }
        else if(class_exists('memcached')) 
        {
            $driver                                                     = 'memcached';
        }
        else if(extension_loaded('wincache') && function_exists("wincache_ucache_set")) 
        {
            $driver                                                     = 'wincache';
        }
        else if(extension_loaded('xcache') && function_exists("xcache_get")) 
        {
            $driver                                                     = 'xcache';
        }
        else if(function_exists('memcache_connect')) 
        {
            $driver                                                     = 'memcache';
        }
        else 
        {
            $path                                                       = dirname(__FILE__) . '/drivers';
            $dir                                                        = opendir($path);

            while($file = readdir($dir)) 
            {
                if($file != '.' && $file != '..' && strpos($file, '.php') !== false) 
                {
                    require_once($path . '/' . $file);
                    $namex                                              = str_replace('.php', '', $file);
                    $class                                              = 'cache_' . $namex;
                    $option                                             = $this->option;
                    $option['skipError']                                = true;
                    $driver                                             = new $class($option);
                    $driver->option                                     = $option;

                    if($driver->checkdriver()) 
                    {
                        $driver                                         = $namex;
                    }
                }
            }
        }

        return $driver;
    }

    public function option($name, $value = null) 
    {
        if($value == null) 
        {
            if(isset($this->option[$name])) 
            {
                return $this->option[$name];
            } 
            else 
            {
                return null;
            }
        } 
        else 
        {
            if($name == 'path') 
            {
                $this->checked['path']                                  = false;
                $this->driver->checked['path']                          = false;
            }

            self::$config[$name]                                        = $value;
            $this->option[$name]                                        = $value;
            $this->driver->option[$name]                                = $this->option[$name];

            return $this;
        }
    }

    public function setOption($option = array()) 
    {
        $this->option                                                   = array_merge($this->option, self::$config, $option);
        $this->checked['path']                                          = false;
    }

    public function __get($name) 
    {
        $this->driver->option                                           = $this->option;
        return $this->driver->get($name);
    }

    public function __set($name, $v) 
    {
        $this->driver->option                                           = $this->option;

        if(isset($v[1]) && is_numeric($v[1])) 
        {
            return $this->driver->set($name,$v[0],$v[1], isset($v[2]) ? $v[2] : array());
        } 
        else 
        {
            throw new Exception("Example ->$name = array('VALUE', 300);", 98);
        }
    }

    /*
     * Only require_once for the class u use.
     * Not use autoload default of PHP and don't need to load all classes as default
     */
    private function isExistingDriver($class) 
    {
        if(file_exists(dirname(__FILE__) . '/drivers/' . $class . '.php')) 
        {
            require_once(dirname(__FILE__) . '/drivers/' . $class . '.php');

            if(class_exists('cache_' . $class)) 
            {
                return true;
            }
        }

        return false;
    }

    /*
     * return System Information
     */
    public function systemInfo() 
    {
        if(count($this->option("system")) == 0)  
        {
            $this->option['system']['driver']                           = 'files';
            $this->option['system']['drivers']                          = array();
            $dir                                                        = @opendir(dirname(__FILE__) . '/drivers/');

            if(!$dir) 
            {
                throw new Exception("Can't open file dir ext",100);
            }

            while($file = @readdir($dir)) 
            {
                if($file != '.' && $file != '..' && strpos($file, '.php') !== false) 
                {
                    require_once(dirname(__FILE__) . '/drivers/' . $file);
                    $namex                                              = str_replace('.php', '', $file);
                    $class                                              = 'cache_' . $namex;
                    $this->option['skipError']                          = true;
                    $driver                                             = new $class($this->option);
                    $driver->option                                     = $this->option;

                    if($driver->checkdriver()) 
                    {
                        $this->option['system']['drivers'][$namex]      = true;
                        $this->option['system']['driver']               = $namex;
                    } 
                    else 
                    {
                        $this->option['system']['drivers'][$namex]      = false;
                    }
                }
            }

            /*
             * PDO is highest priority with SQLite
             */
            if($this->option['system']['drivers']['sqlite'] == true) 
            {
                $this->option['system']['driver']                       = 'sqlite';
            }
        }

        $example                                                        = new cache_example($this->option);
        $this->option("path", $example->getPath(true));

        return $this->option;
    }

    public function getOS() 
    {
        $os                                                             = array(
            'os'                                                            => PHP_OS,
            'php'                                                           => PHP_SAPI,
            'system'                                                        => php_uname(),
            'unique'                                                        => md5(php_uname() . PHP_OS . PHP_SAPI)
        );

        return $os;
    }

    /*
     * Object for Files & SQLite
     */
    public function encode($data) 
    {
        return serialize($data);
    }

    public function decode($value) 
    {
        $x                                                              = @unserialize($value);
        return ($x == false)  ? $value : $x;
    }

    /*
     * Auto Create .htaccess to protect cache folder
     */
    public function htaccessGen($path = '') 
    {
        if($this->option('htaccess') == true) 
        {
            if(!file_exists($path . '/.htaccess')) 
            {
                $html                                                   = "order deny, allow \r\ndeny from all \r\nallow from 127.0.0.1";
                $f                                                      = @fopen($path . '/.htaccess', 'w+');

                if(!$f) 
                {
                    throw new Exception("Can't create .htaccess", 97);
                }

                fwrite($f, $html);
                fclose($f);
            }
        }
    }

    /*
    * Check phpModules or CGI
    */
    public function isPHPModule() 
    {
        if(PHP_SAPI == 'apache2handler') 
        {
            return true;
        } 
        else 
        {
            if(strpos(PHP_SAPI, 'handler') !== false) 
            {
                return true;
            }
        }

        return false;
    }

    /*
     * return PATH for Files & PDO only
     */
    public function getPath($create_path = false) 
    {
        if($this->option['path'] == '' && self::$config['path'] != '') 
        {
            $this->option('path', self::$config['path']);
        }

        if ($this->option['path'] == '')
        {
            if($this->isPHPModule()) 
            {
                $tmp_dir                                                = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
                $this->option('path', $tmp_dir);

            } 
            else 
            {
                $this->option('path', dirname(__FILE__));
            }

            if(self::$config['path'] == '') 
            {
                self::$config['path']                                   = $this->option('path');
            }
        }

        $full_path                                                      = $this->option('path') . '/' . $this->option('securityKey') . '/';

        if($create_path == false && $this->checked['path'] == false) 
        {
            if(!file_exists($full_path) || !is_writable($full_path)) 
            {
                if(!file_exists($full_path)) 
                {
                    @mkdir($full_path,0777);
                }

                if(!is_writable($full_path)) 
                {
                    @chmod($full_path,0777);
                }

                if(!file_exists($full_path) || !is_writable($full_path)) 
                {
                    throw new \Exception('Sorry, Please create ' . $this->option('path') . '/' . $this->option('securityKey') . '/ and SET Mode 0777 or any Writable Permission!' , 100);
                }
            }

            $this->checked['path']                                      = true;
            $this->htaccessGen($full_path);
        }

        $this->option['cachePath']                                      = $full_path;

        return $this->option['cachePath'];
    }
    /*
     * Read File
     * Use file_get_contents OR ALT read
     */
    public function readfile($file) 
    {
        if(function_exists('file_get_contents')) 
        {
            return file_get_contents($file);
        } 
        else 
        {
            $string                                                     = '';
            $file_handle                                                = @fopen($file, 'r');

            if(!$file_handle) 
            {
                throw new Exception("Can't Read File", 96);
            }
            
            while (!feof($file_handle)) 
            {
                $line                                                   = fgets($file_handle);
                $string                                                 .= $line;
            }

            fclose($file_handle);
            
           return $string;
        }
    }
}