<?php

namespace App\Social\Facebook;

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

class Facebook extends Base
{
    protected static $kSupportedKeys = ['state', 'code', 'access_token', 'user_id'];
    /**
     * Identical to the parent constructor, except that
     * we start a PHP session to store the user ID and
     * access token if during the course of execution
     * we discover them.
     *
     * @param Array $config the application configuration.
     * @see BaseFacebook::__construct in facebook.php
     */
    public function __construct($config)
    {
        parent::__construct($config);
    }
    /**
     * Provides the implementations of the inherited abstract
     * methods.  The implementation uses PHP sessions to maintain
     * a store for authorization codes, user ids, CSRF states, and
     * access tokens.
     * @param mixed $key
     * @param mixed $value
     */
    protected function setPersistentData($key, $value)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to setPersistentData.');
            return;
        }
        
        $session_var_name = $this->constructSessionVariableName($key);
        $_SESSION[$session_var_name] = $value;
    }
    
    protected function getPersistentData($key, $default = false)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to getPersistentData.');
            return $default;
        }
        
        $session_var_name = $this->constructSessionVariableName($key);
        return isset($_SESSION[$session_var_name]) ? $_SESSION[$session_var_name] : $default;
    }
    
    protected function clearPersistentData($key)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to clearPersistentData.');
            return;
        }
        
        $session_var_name = $this->constructSessionVariableName($key);
        unset($_SESSION[$session_var_name]);
    }
    
    protected function clearAllPersistentData()
    {
        foreach (self::$kSupportedKeys as $key) {
            $this->clearPersistentData($key);
        }
    }
    
    protected function constructSessionVariableName($key)
    {
        return implode('_', ['fb', $this->getAppId(), $key]);
    }
}
