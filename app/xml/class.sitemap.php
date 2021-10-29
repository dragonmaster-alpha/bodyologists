<?php

namespace App\Xml;

use App\Format;
use SitemapPHP\Sitemap;

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

class SitemapXML extends Format
{
    private $siteurl;
    
    public function __construct()
    {
        $this->config = parent::get_config();
        $this->siteurl = (empty($this->config['force_https']) ? 'https' : 'http').'://www.'.$this->site_domain()._SITE_PATH;

        require_once 'class.sitemap.php';
        $sitemap = new Sitemap($this->siteurl);
        $sitemap->setPath($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/');
        $sitemap->setFilename('sitemap');
                
        $sitemap->addItem('/', '1.0', 'weekly');

        if (self::plugin_exists('pages')) {
            foreach ($this->sql_get('pages', 'url, modified', "active = '1' AND url != '' AND url != 'index' AND page_type = '1'", 'ordering ASC') as $data) {
                $sitemap->addItem('/'.$data['url'], '0.5', 'monthly', date('Y-m-d', strtotime($data['modified'])));
            }
        }

        if (self::plugin_exists('products')) {
            $sitemap->addItem($this->format_url("/products"), '0.5', 'weekly');

            if (self::addon_exists('products', 'categories')) {
                foreach ($this->sql_get('items_categories', 'url, modified', "active = '1' AND url != ''", 'id DESC') as $data) {
                    $sitemap->addItem('/products/categories/'.$data['url'], '0.5', 'monthly', date('Y-m-d', strtotime($data['modified'])));
                }
            }
            foreach ($this->sql_get('items', 'url, modified', "active = '1' AND url != ''", 'ordering ASC') as $data) {
                $sitemap->addItem('/products/'.$data['url'], '0.5', 'monthly', date('Y-m-d', strtotime($data['modified'])));
            }
        }

        if (self::plugin_exists('blog')) {
            $sitemap->addItem($this->format_url("/blog"), '0.5', 'weekly');

            foreach ($this->sql_get('blog', 'url, modified', "active = '1' AND url != ''", 'date DESC') as $data) {
                $sitemap->addItem('/blog/'.$data['url'], '0.5', 'monthly', date('Y-m-d', strtotime($data['modified'])));
            }

            foreach ($this->sql_get('blog', 'YEAR(date) as year, MONTH(date) as month', "active = '1' AND date <= now()", 'date DESC') as $data) {
                $_short_date = $data['year'].'-'.$data['month'];

                if ($_already_there != $_short_date) {
                    $sitemap->addItem('/blog/archives/?year='.$data['year'].'&amp;month='.$data['month'], '0.5', 'weekly');
                }

                $_already_there = $_short_date;
            }
        }
        if (self::plugin_exists('payments')) {
            $sitemap->addItem($this->format_url("/payments"), '0.5', 'monthly');
        }
        if (self::plugin_exists('orders')) {
            $sitemap->addItem($this->format_url("/orders"), '0.5', 'monthly');
        }
        if (self::plugin_exists('events')) {
            $sitemap->addItem($this->format_url("/events"), '0.5', 'weekly');

            foreach ($this->sql_get('events', 'url, modified', "active = '1' AND url != '' AND date>'".date('Y-m-d')."'", 'date ASC') as $data) {
                $sitemap->addItem('/events/'.$data['url'], '0.5', 'monthly', date('Y-m-d', strtotime($data['modified'])));
            }
        }

        if (self::plugin_exists('directory')) {
            $sitemap->addItem($this->format_url("/directory"), '0.5', 'weekly');

            foreach ($this->sql_get('directory', 'url, modified', "active = '1' AND url != ''", 'date DESC') as $data) {
                $sitemap->addItem('/directory/'.$data['url'], '0.5', 'monthly', date('Y-m-d', strtotime($data['modified'])));
            }
        }

        if (self::plugin_exists('properties')) {
            $sitemap->addItem($this->format_url("/properties"), '0.5', 'weekly');

            foreach ($this->sql_get('properties', 'url, modified', "active = '1' AND url != ''", 'date DESC') as $data) {
                $sitemap->addItem('/properties/'.$data['url'], '0.5', 'monthly', date('Y-m-d', strtotime($data['modified'])));
            }
        }

        if (self::addon_exists('pages', 'testimonials')) {
            $sitemap->addItem($this->format_url("/pages/testimonials"), '0.5', 'weekly');

            foreach ($this->sql_get('testimonials', 'url, modified', "active = '1' AND url != ''", 'date DESC') as $data) {
                $sitemap->addItem('/testimonials/'.$data['url'], '0.5', 'monthly', date('Y-m-d', strtotime($data['modified'])));
            }
        }

        if (self::plugin_exists('members')) {
            $sitemap->addItem($this->format_url("/members"), '0.5', 'monthly');
            $sitemap->addItem($this->format_url("/members/register"), '0.5', 'monthly');
        }

        if (self::plugin_exists('sitemap')) {
            $sitemap->addItem($this->format_url("/sitemap"), '0.5', 'weekly');
        }

        $sitemap->createSitemapIndex($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/', 'Today');
    }
    /**
     * check if a given plugin folder exists
     * @param  string $plugin_name
     * @return boolean
     */
    private static function plugin_exists($plugin_name)
    {
        return (is_dir($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.$plugin_name)) ? true : false;
    }
    /**
     * check if a given plugin folder exists
     * @param  string $plugin_name
     * @param mixed $addon_name
     * @return boolean
     */
    private static function addon_exists($plugin_name, $addon_name)
    {
        return (is_dir($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/'.$plugin_name.'/addons/'.$addon_name)) ? true : false;
    }
}
