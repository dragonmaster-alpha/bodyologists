<?php

namespace Plugins\System\Classes;

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

class Stats extends Format
{
    public $start_date;
    public $end_date;

    public function __construct($start_date, $end_date)
    {
        $this->start_date = (!empty($start_date)) ? $start_date   : date('Y-m-d', time() - 7 * 24 * 60 * 60);
        $this->end_date = (!empty($end_date))   ? $end_date     : date('Y-m-d');
    }

    public function get_visits_per_day()
    {
        try {
            $start_timestamp = strtotime($this->start_date);
            $end_timestamp = strtotime($this->end_date);

            for ($i = $start_timestamp; $i <= $end_timestamp; $i += 86400) {
                $sqlQuery = "
                    SELECT COUNT(id) as CNT
                    FROM ".$this->prefix."_stats_visits
                    WHERE date = '".date("Y-m-d", $i)."'
                ";
                
                $data = $this->sql_fetchone($sqlQuery);
                $return[] = [$count => (int) $data['CNT']];
                $count++;
            }
            return json_encode($return);
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function get_views_per_day()
    {
        $start_timestamp = strtotime($this->start_date);
        $end_timestamp = strtotime($this->end_date);

        for ($i = $start_timestamp; $i <= $end_timestamp; $i += 86400) {
            $sqlQuery = "
                SELECT SUM(visits) as CNT
                FROM ".$this->prefix."_stats_visits
                WHERE date = '".date("Y-m-d", $i)."'
            ";
            
            $data = $this->sql_fetchone($sqlQuery);
            $return[] = [$count => (int) $data['CNT']];
            $count++;
        }

        return json_encode($return);
    }
    
    public function get_ticks_per_day()
    {
        $start_timestamp = strtotime($this->start_date);
        $end_timestamp = strtotime($this->end_date);

        for ($i = $start_timestamp; $i <= $end_timestamp; $i += 86400) {
            $return[] = [$count => date('m/d', $i)];
            $count++;
        }

        return json_encode($return);
    }

    public function get_unique_visits()
    {
        $sqlQuery = "
            SELECT COUNT(DISTINCT(ip)) AS CNT
            FROM ".$this->prefix."_stats_visitors
            WHERE date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
        ";

        $data = $this->sql_fetchone($sqlQuery);
        return $this->number($data['CNT'], 0, 0);
    }

    public function get_total_visits()
    {
        $sqlQuery = "
            SELECT COUNT(id) as CNT
            FROM ".$this->prefix."_stats_visits
            WHERE date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
        ";

        $data = $this->sql_fetchone($sqlQuery);
        return $this->number($data['CNT'], 0, 0);
    }

    public function get_page_views()
    {
        $sqlQuery = "
            SELECT SUM(visits) as CNT
            FROM ".$this->prefix."_stats_visits
            WHERE date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
        ";

        $data = $this->sql_fetchone($sqlQuery);
        return $this->number($data['CNT'], 0, 0);
    }

    public function get_pages_vs_visits()
    {
        $all_visits = $this->get_total_visits();
        $all_pages = $this->get_page_views();
        
        return @$this->number($all_pages / $all_visits, 1, 1);
    }

    public function get_most_page_visits($qty = 0)
    {
        $sqlQuery = "
            SELECT url, SUM(visits) AS CNT
            FROM ".$this->prefix."_stats_visits
            WHERE date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
            GROUP BY url
            ORDER BY visits DESC
        ";

        if (!empty($qty)) {
            $sqlQuery .= "
                LIMIT ".(int) $qty."
            ";
        }

        foreach ($this->sql_fetchrow($sqlQuery) as $data) {
            $return[] = $data;
        }

        return $return;
    }

    public function get_most_country_visits($qty = 0)
    {
        $sqlQuery = "
            SELECT country AS element, SUM(visits) AS visits, (
                SELECT SUM(visits)
                FROM ".$this->prefix."_stats_visitors
                WHERE date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
                AND country != ''
            ) AS CNT
            FROM ".$this->prefix."_stats_visitors
            WHERE date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
            AND country != ''
            GROUP BY country
            ORDER BY visits DESC
        ";

        if (!empty($qty)) {
            $sqlQuery .= "
                LIMIT ".(int) $qty."
            ";
        }
        
        foreach ($this->sql_fetchrow($sqlQuery) as $data) {
            $return[] = $data;
        }
        return $return;
    }

    public function get_most_browser_visits($qty = 0)
    {
        try {
            $sqlQuery = "
                SELECT browser AS element, SUM(visits) AS visits, (
                    SELECT SUM(visits)
                    FROM ".$this->prefix."_stats_visitors
                    WHERE date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
                    AND browser != ''
                ) AS CNT
                FROM ".$this->prefix."_stats_visitors
                WHERE date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
                GROUP BY browser
                ORDER BY visits DESC
            ";

            if (!empty($qty)) {
                $sqlQuery .= "
                    LIMIT ".(int) $qty."
                ";
            }

            foreach ($this->sql_fetchrow($sqlQuery) as $data) {
                $return[] = $data;
            }
            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function get_non_mobile_visits($qty = 0)
    {
        $sqlQuery = "
            SELECT platform AS element, SUM(visits) AS visits, (
                SELECT SUM(visits)
                FROM ".$this->prefix."_stats_visitors
                WHERE date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
                AND platform != ''
                AND is_mobile = '0'
            ) AS CNT
            FROM ".$this->prefix."_stats_visitors
            WHERE date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
            AND is_mobile = '0'
            AND platform != ''
            GROUP BY platform
            ORDER BY visits DESC
        ";

        if (!empty($qty)) {
            $sqlQuery .= "
                LIMIT ".(int) $qty."
            ";
        }

        foreach ($this->sql_fetchrow($sqlQuery) as $data) {
            $return[] = $data;
        }

        return $return;
    }

    public function get_mobile_visits($qty = 0)
    {
        $sqlQuery = "
            SELECT platform AS element, SUM(visits) AS visits, (
                SELECT SUM(visits)
                FROM ".$this->prefix."_stats_visitors
                WHERE date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
                AND platform != ''
                AND is_mobile = '1'
            ) AS CNT
            FROM ".$this->prefix."_stats_visitors
            WHERE date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
            AND is_mobile = '1'
            AND platform != ''
            GROUP BY platform
            ORDER BY visits DESC
        ";

        if (!empty($qty)) {
            $sqlQuery .= "
                LIMIT ".(int) $qty."
            ";
        }

        foreach ($this->sql_fetchrow($sqlQuery) as $data) {
            $return[] = $data;
        }

        return $return;
    }

    // Per item results
    public function get_page_visits()
    {
        if (empty($url)) {
            $url = 'index.html';
        }

        $sqlQuery = "
            SELECT url AS element, SUM(visits) AS visits, (
                SELECT SUM(visits)
                FROM ".$this->prefix."_stats_visits
                WHERE date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
            ) AS CNT
            FROM ".$this->prefix."_stats_visits
            WHERE date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
            GROUP BY url
            ORDER BY visits DESC
        ";

        foreach ($this->sql_fetchrow($sqlQuery) as $data) {
            $return[] = $data;
        }

        return $return;
    }

    public function get_referrers_visits()
    {
        $sqlQuery = "
            SELECT referer AS element, referer_url as url, COUNT(id) AS visits, (
                SELECT COUNT(id)
                FROM ".$this->prefix."_stats_referers
            ) AS CNT
            FROM ".$this->prefix."_stats_referers
            WHERE date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
            GROUP BY referer_url
        ";

        foreach ($this->sql_fetchrow($sqlQuery) as $data) {
            $return[] = $data;
        }

        return $return;
    }

    public function get_search_visits()
    {
        $sqlQuery = "
            SELECT referer AS element, search_term as url, COUNT(id) AS visits, (
                SELECT COUNT(id)
                FROM ".$this->prefix."_stats_referers
                WHERE search_engine = '1'
            ) AS CNT
            FROM ".$this->prefix."_stats_referers r
            WHERE search_engine = '1'
            AND date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
            GROUP BY search_term
        ";

        foreach ($this->sql_fetchrow($sqlQuery) as $data) {
            $return[] = $data;
        }

        return $return;
    }

    public function get_social_networks_visits()
    {
        $sqlQuery = "
            SELECT referer AS element, referer_url as url, COUNT(id) AS visits, (
                SELECT COUNT(id)
                FROM ".$this->prefix."_stats_referers
                WHERE social_network = '1'
            ) AS CNT
            FROM ".$this->prefix."_stats_referers r
            WHERE social_network = '1'
            AND date BETWEEN '".$this->start_date."' AND '".$this->end_date."'
            GROUP BY referer
        ";

        foreach ($this->sql_fetchrow($sqlQuery) as $data) {
            $return[] = $data;
        }

        return $return;
    }

    public function count_latest_orders()
    {
        if ($this->table_exists("orders")) {
            return $this->sql_count('orders');
        }

        return false;
    }

    public function get_latest_orders()
    {
        if ($this->table_exists("orders")) {
            $sqlQuery = "
                SELECT id, trans, date, total, CONCAT(first_name, ' ' ,last_name) AS full_name, (
                    SELECT COUNT(*) 
                    FROM ".$this->prefix."_orders_items
                ) AS items 
                FROM ".$this->prefix."_orders
                WHERE trans != ''
                ORDER BY id DESC
                LIMIT 7
            ";

            foreach ($this->sql_fetchrow($sqlQuery) as $data) {
                $return[] = $this->filter($data, 1);
            }

            return $return;
        }

        return false;
    }

    public function count_latest_events()
    {
        if ($this->table_exists("events")) {
            return $this->sql_count('events');
        }

        return false;
    }

    public function get_latest_events()
    {
        if ($this->table_exists("events")) {
            $sqlQuery = "
                SELECT id, name, start_date, date
                FROM ".$this->prefix."_events
                WHERE start_date >= now()
                ORDER BY id DESC
                LIMIT 7
            ";

            foreach ($this->sql_fetchrow($sqlQuery) as $data) {
                $return[] = $this->filter($data, 1);
            }

            return $return;
        }

        return false;
    }

    public function count_latest_members()
    {
        if ($this->table_exists("customers")) {
            return $this->sql_count('customers', ['alive' => 0]);
        }

        return false;
    }

    public function get_latest_members()
    {
        if ($this->table_exists("customers")) {
            $sqlQuery = "
                SELECT id, CONCAT(first_name, ' ' ,last_name) AS full_name, email, phone, date
                FROM ".$this->prefix."_customers
                WHERE alive = '0'
                ORDER BY id DESC
                LIMIT 7
            ";

            foreach ($this->sql_fetchrow($sqlQuery) as $data) {
                $return[] = $this->filter($data, 1);
            }

            return $return;
        }

        return false;
    }

    public function get_latest_products()
    {
        if ($this->table_exists("items")) {
            $sqlQuery = "
                SELECT p.id, p.sku, p.title, p.price, p.stock, p.date, 
                (
                    SELECT special
                    FROM ".$this->prefix."_items_special
                    WHERE parent = p.id
                ) AS special, 
                (
                    SELECT image
                    FROM ".$this->prefix."_media
                    WHERE bid = MD5(p.id) 
                    AND belongs = 'products'
                    ORDER BY imageId ASC
                    LIMIT 1
                ) AS image
                FROM ".$this->prefix."_items p
                WHERE p.alive = '0'
                ORDER BY p.id DESC
                LIMIT 7
            ";

            foreach ($this->sql_fetchrow($sqlQuery) as $data) {
                $return[] = $this->filter($data, 1);
            }

            return $return;
        }

        return false;
    }

    public function get_out_stock_items()
    {
        if ($this->table_exists("items")) {
            $sqlQuery = "
                SELECT p.id, p.sku, p.title, p.price, p.stock, p.date, 
                (
                    SELECT image
                    FROM ".$this->prefix."_media
                    WHERE bid = MD5(p.id) 
                    AND belongs = 'products'
                    ORDER BY imageId ASC
                    LIMIT 1
                ) AS image
                FROM ".$this->prefix."_items p
                WHERE p.alive = '0'
                AND p.track_stock = '1'
                AND p.stock <= p.min_stock
                ORDER BY p.stock ASC
                LIMIT 7
            ";

            foreach ($this->sql_fetchrow($sqlQuery) as $data) {
                $return[] = $this->filter($data, 1);
            }

            return $return;
        }

        return false;
    }

    public function get_best_seller_items()
    {
        if ($this->table_exists("items")) {
            $sqlQuery = "
                SELECT p.id, p.sku, p.title, p.price, p.stock, p.date, p.times_sold, 
                (
                    SELECT image
                    FROM ".$this->prefix."_media
                    WHERE bid = MD5(p.id) 
                    AND belongs = 'products'
                    ORDER BY imageId ASC
                    LIMIT 1
                ) AS image
                FROM ".$this->prefix."_items p
                WHERE p.alive = '0'
                ORDER BY p.times_sold DESC
                LIMIT 7
            ";

            foreach ($this->sql_fetchrow($sqlQuery) as $data) {
                $return[] = $this->filter($data, 1);
            }

            return $return;
        }

        return false;
    }

    public function get_recent_comments()
    {
        if ($this->table_exists('blog')) {
            return $this->sql_count('comments', ['plugin' => 'blog', 'approved' => 0]);
        }

        return false;
    }

    public function get_recent_reviews()
    {
        if ($this->table_exists('items')) {
            return $this->sql_count('comments', ['plugin' => 'products', 'approved' => 0]);
        }

        return false;
    }
}
