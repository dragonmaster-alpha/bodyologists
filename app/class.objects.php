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

class Objects
{
    /**
     * An array of errors
     *
     */
    public $_errors = [];
    public $_data = [];

    /**
     * Class constructor, overridden in descendant classes.
     *
     */
    public function __construct()
    {
    }
    public function __clone()
    {
    }

    /**
     * Sets objects to be stored into the $_data variable
     *
     * @param string $name  name of the array key or reference
     * @param string $value value for the array
     */
    public function __set($name = '', $value = '')
    {
        if (!empty($value)) {
            $this->_data[$name] = $value;
        }
    }

    /**
     * Gets object info from property $_data
     *
     * @param  string $name name on the key to search for
     * @return string
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }

        return false;
    }

    /**
     * Checks if there the provided name key exists into the $_data property
     *
     * @param  string  $name name of the key to search for
     * @return boolean
     */
    public function __isset($name)
    {
        return (isset($this->_data[$name])) ? true : false;
    }

    /**
     * Unset a value from property $_data
     *
     * @param string $name name of the key to search for
     */
    public function __unset($name)
    {
        unset($this->_data[$name]);
    }

    /**
     * Add an error message
     *
     * @param	string $error Error message
     * @access	public
     */
    public function __set_error($error)
    {
        if (!empty($error)) {
            array_push($this->_errors, $error);
        }
    }

    /**
     * Get the most recent error message
     *
     * @param	integer	$i Option error index
     * @return	string	Error message
     */
    public function __get_error($i = null)
    {
        if ($i === null) {
            $error = end($this->_errors);
        } elseif (!array_key_exists($i, $this->_errors)) {
            return false;
        } else {
            $error = $this->_errors[$i];
        }

        return $error;
    }

    /**
     * Return all errors, if any
     *
     * @access	public
     * @return	array	Array of error messages or errors
     */
    public function __get_errors()
    {
        return $this->_errors;
    }
}
