<?php

namespace App;

require_once 'class.format.php';

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

class Settings extends Format
{
    private $config;

    public function __construct()
    {
    }

    /**
     * get given settings from .ini file
     * @param  string $name
     * @return array       array with all values within the configuration
     */
    public function get($name)
    {
        if (!empty($name)) {
            $config_file = strtolower($name);

            if (file_exists($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/config/site.'.$config_file.'.conf')) {
                $return = parent::cache()->get('_SETTINGS_'.$config_file);

                if ($return == null) {
                    $return = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/config/site.'.$config_file.'.conf'), 1);

                    if (!empty($return)) {
                        parent::cache()->set('_SETTINGS_'.$config_file, $return, 3600);
                    }
                }

                return $return;
            }
            
            return false;
        }
    }

    /**
     * insert configuration values in .ini file
     * @param string $name   where will it be saved to in DB
     * @param array $values array of values to be inserted
     */
    public function set($name, array $values)
    {
        if (!empty($name) && !empty($values) && is_array($values)) {
            $config_file = strtolower($name);

            if (is_array($values)) {
                $_save_values = [];

                foreach ($values as $key => $value) {
                    if ($key != 'plugin' && $key != 'addon' && $key != 'file' && $key != 'op' && $key != 'csrf') {
                        $_save_array[$key] = $this->filter($value, 1, 1);
                    }
                }

                file_put_contents($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/config/site.'.$config_file.'.conf', json_encode($_save_array));
                parent::cache()->delete('_SETTINGS_'.$config_file);

                return true;
            }

            return false;
        }
    }

    /**
     * calculate taxes price based on state
     * @param  string $state
     * @param mixed $amount
     * @return string        percentage taxes applied to this state
     */
    public function get_taxes($state = '', $amount = '0.00')
    {
        $this->config = parent::get_config();

        if (@in_array($state, $this->config['tax_states'])) {
            $data = $this->sql_get_one('states', 'tax', ['prefix' => $state]);
            return $this->number(($this->number($amount, 1) * $data['tax']) / 100);
        }
         
        return 0;
    }
}
