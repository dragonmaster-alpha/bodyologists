<?php

namespace Plugins\Sitemap\Classes;

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

class Sitemap extends Format
{
    private $plugin;

    public function __construct()
    {
        $this->plugin = 'sitemap';
    }

    public function get_pages()
    {
        foreach ($this->sql_get('pages', 'name, url', ['active' => 1, 'lang' => $_SESSION['lang']], 'ordering ASC') as $data) {
            $return[] = $this->filter($data, 1);
        }

        return $return;
    }

    public function get_products_categories()
    {
        foreach ($this->sql_get('items_categories', 'name, url', ['active' => 1, 'lang' => $_SESSION['lang']], 'name ASC') as $data) {
            $return[] = $this->filter($data, 1);
        }

        return $return;
    }

    public function get_products()
    {
        foreach ($this->sql_get('items', 'title AS name, url', ['active' => 1, 'lang' => $_SESSION['lang']], 'ordering ASC') as $data) {
            $return[] = $this->filter($data, 1);
        }
        
        return $return;
    }

    public function get_blog()
    {
        foreach ($this->sql_get('blog', 'title AS name, url', ['active' => 1, 'lang' => $_SESSION['lang']], 'date DESC') as $data) {
            $return[] = $this->filter($data, 1);
        }
        
        return $return;
    }
}
