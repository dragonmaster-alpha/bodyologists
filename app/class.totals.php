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

class Totals extends Format
{
    public function __construct()
    {
    }
    /**
     * Get count of blogs and commets
     * @return int
     */
    public function get_blog_totals()
    {
        if ($this->table_exists('blog')) {
            return $this->sql_count('blog', ['alive' => 0]);
        }
    }
    
    public static function get_directory_totals()
    {
        if (parent::table_exists('directory')) {
            return parent::sql_count('directory', ['alive' => 0]);
        }
    }

    public static function get_events_totals()
    {
        if (parent::table_exists('events')) {
            return parent::sql_count('events', ['alive' => 0]);
        }
    }
    
    public function get_members_totals()
    {
        if ($this->table_exists('customers')) {
            return $this->sql_count('customers', ['alive' => 0]);
        }
    }

    public function get_orders_totals()
    {
        if ($this->table_exists('orders')) {
            return $this->sql_count('orders', "trans != ''");
        }
    }

    public static function get_payments_totals()
    {
        if (parent::table_exists('payments')) {
            return parent::sql_count('payments');
        }
    }

    public function get_sold_totals()
    {
        if ($this->table_exists('orders')) {
            $orders = $this->sql_get_one('orders', 'SUM(total) as totals', "`status` <= '4' AND trans != ''");
        }
        if ($this->table_exists('payments')) {
            $payments = $this->sql_get_one('payments', 'SUM(`total`) as totals', "`trans` != ''");
        }

        return $this->number($orders['totals'] ?? 0 + $payments['totals'] ?? 0, 1, 0);
    }
    
    public function get_pages_totals()
    {
        if ($this->table_exists('pages')) {
            return $this->sql_count('pages');
        }
    }
    
    public function get_products_totals()
    {
        if ($this->table_exists('items')) {
            return $this->sql_count('items', ['alive' => 0]);
        }
    }

    public function get_properties_totals()
    {
        if ($this->table_exists('properties')) {
            return $this->sql_count('properties', ['alive' => 0]);
        }
    }
    
    public static function get_testimonials_totals()
    {
        if (parent::table_exists('testimonials')) {
            return parent::sql_count('testimonials');
        }
    }

    public static function count_not_approved_comments($plugin = 'blog')
    {
        if (parent::table_exists('comments') && !empty($plugin)) {
            return parent::sql_count('comments', ['approved' => 0, 'plugin' => $plugin]);
        }
    }
}
