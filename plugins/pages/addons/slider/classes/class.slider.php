<?php

namespace Plugins\Pages\Addons\Slider\Classes;

use App\Format as Format;

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

class Slider extends Format
{
    private $plugin;
    
    public function __construct()
    {
        $this->plugin = 'pages';
    }

    public function list_items($qty = '', $belongs = 'Slider', $sort = 'imageId', $dir = 'ASC')
    {
        $_WHERE['belongs'] = $belongs;

        if (!empty($sort)) {
            $_ORDER = $sort.' '.$dir;
        }
        
        if (!empty($qty)) {
            $_LIMIT = [(int) $qty];
        }

        foreach ($this->sql_get('media', '*', $_WHERE, $_ORDER, $_LIMIT) as $data) {
            $return[] = $this->filter($data, 1);
        }

        return $return;
    }

    public function get_item_info($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->filter($this->sql_get_one('media', '*', $id), 1);
        }

        return false;
    }
    
    public function update($data)
    {
        if (!empty($data)) {
            $id = (int) $data['id'];
            unset($data['id']);

            return $this->sql_update('media', $data, $id);
        }

        return false;
    }
    
    public function delete($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->sql_delete('media', $id);
        }
        
        return false;
    }
    
    public function reorder(array $items)
    {
        foreach ($items as $ordering => $id) {
            $this->sql_update('media', ['imageId' => $ordering], (int) $id);
        }
    }
}
