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

class MultiArray
{
    public $rendered;

    private $data = [];

    public function __construct(&$input)
    {
        foreach ($input as $item) {
            $item = (array) $item;
            $this->data['items'][$item['id']] = $item;
            $this->data['parents'][$item['parent_id']][] = $item['id'];

            if (!isset($this->top_level) || $this->top_level > $item['parent_id']) {
                $this->top_level = $item['parent_id'];
            }
        }
        return $this;
    }

    public function build($id)
    {
        $return{$id} = [];
        foreach ($this->data['parents'][$id] as $child) {
            $build = $this->data['items'][$child];

            if (isset($this->data['parents'][$child])) {
                $build['has_children'] = true;
                $build['children'] = $this->build($child);
            } else {
                $build['has_children'] = false;
            }
            
            $return{$id}[] = $build;
        }
        return (array) $return{$id};
    }

    public function render()
    {
        if (!isset($this->rendered) || !is_array($this->rendered)) {
            $this->rendered = $this->build($this->top_level);
        }
        return $this->rendered;
    }
}
