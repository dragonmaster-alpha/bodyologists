<?php

namespace Plugins\Members\Classes;

use App\Format as Format;
use App\Helper;
use Plugins\Deals\Classes\Deals;
use Plugins\Events\Classes\Events;

//use Plugins\Products\Classes\Products as Products;

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

class Wishlist extends Format
{
    private $plugin;
    private $products;

    public function __construct()
    {
        $this->plugin = 'members';
        $this->table = 'customers_wishlist';
    }
    /**
     * List wishlists
     * @param  integer  $start          start from
     * @param  integer  $qty            quantity to be returned
     * @param  string   $owner          row owner
     * @param  string   $sort           sort by element
     * @param  string   $dir            direction to sort
     * @return array
     */
    public function list_items($start = '0', $qty = '', $owner = 0, $sort = 'date', $dir = 'DESC')
    {
        $owner = (!empty($owner)) ? (int) $owner : (int) $_SESSION['user_info']['id'];
        
        if (!empty($owner)) {
            $_WHERE['owner'] = $owner;

            if (!empty($sort)) {
                $_ORDER = $sort.' '.$dir;
            }

            if (!empty($qty)) {
                $_LIMIT = [(int) $start, (int) $qty];
            }

            foreach ($this->sql_get('customers_wishlist', '*', $_WHERE, $_ORDER, $_LIMIT) as $data) {
                $return[] = $this->load($data);
            }
        
            return $return;
        }
        
        return false;
    }
    /**
     * Count
     * @param  integer $owner
     * @return integer
     */
    public function count($owner = 0)
    {
        $owner = (!empty($owner)) ? (int) $owner : (int) $_SESSION['user_info']['id'];

        return $this->sql_count('customers_wishlist', ['owner' => $owner]);
    }
    /**
     * Get wishlist info based on id
     * @param  integer  $id     row id
     * @return array
     */
    public function get_items_info($id = 0)
    {
        $id = (int) $id;
        
        if (!empty($id)) {
            return $this->load($this->sql_get_one('customers_wishlist', '*', $id));
        }
        
        return false;
    }
    /**
     * Reload comment info in cache
     * @param  integer  $id     row id
     * @return boolean
     */
    public function reload_cache_item($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            parent::cache()->delete('_WISHLIST_'.$id);
            $this->get_items_info($id);
            return true;
        }

        return false;
    }
    /**
     * CHeck user items in database
     * @param  array  $data
     * @return boolean
     */
    public function check_item(array $data = [])
    {
        if (!empty($data)) {
            return $this->sql_count('customers_wishlist', ['item_id' => (int) $data['item_id'], 'owner' => (int) $_SESSION['user_info']['id']]);
        }
        
        return false;
    }

    /**
     * @param array $data
     * @return int of newly inserted data
     */
    public function insert(array $data): int
    {
        $existing = $this->sql_get_one('customers_wishlist', '*', ['item_id' => $data['item_id'], 'owner' => $data['owner']]);
        if (!empty($existing)) {
            return $existing['id'];
        }

        // Remove empty fields
        $data = Helper::removeEmptyFieldsRecursive($data);

        $data['date'] = date('Y-m-d H:i:s');
        $id = $this->sql_insert('customers_wishlist', $data);
        $this->reload_cache_item($id);

        return $id;

    }

    /**
     * Delete data from wishlist
     *
     * @param int $id
     * @param string $type
     * @param int $owner
     */
    public function delete(int $id, string $type, int $owner): void
    {
        $this->sql_delete('customers_wishlist', ['item_id' => $id, 'owner' => $owner, 'belongs' => $type]);
        self::cache()->delete('_WISHLIST_'.$id);
    }

    /**
     * Get wishlist info based on id
     * @param  integer  $id     row id
     * @return array
     */
    public function get_deal_info($id = 0)
    {
        $id = (int) $id;
        
        if (!empty($id)) {
            return $this->load($this->sql_get_one('deals', '*', $id));
        }
        
        return false;
    }

    public function get_event_info($id = 0)
    {
        $id = (int) $id;
        
        if (!empty($id)) {
            return $this->load($this->sql_get_one('events', '*', $id));
        }
        
        return false;
    }
    public function get_professional_info($id = 0)
    {
        $id = (int) $id;
        
        if (!empty($id)) {
            return $this->load($this->sql_get_one('customers', '*', $id));
        }
        
        return false;
    }
    /**
     * Load and format information
     * @param  array  $data     data to be formatted
     * @return array
     */
    private function load(array $data = [])
    {
        try {
            if (count($data) == 0) {
                throw new Exception('Submitted data is empty', 1);
            }

            $return = parent::cache()->get('_WISHLIST_'.$data['id']);

            if ($return == null) {
                $return = $this->filter($data, 1);

                if (!empty($return)) {
                    parent::cache()->set('_WISHLIST_'.$data['id'], $return);
                }
            }

            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    /**
     * @param int $user_id
     * @return array
     */
    public function getWished(int $user_id): array
    {
        $rows = $this->sql_get($this->table, 'item_id', ['owner' => $user_id]);

        return array_map(
            function ($row) {
                return $row['item_id'];
            },
            $rows
        );
    }

    /**
     * @param int $item_id
     * @param int $user_id
     * @return bool
     */
    public function isWished(int $item_id, int $user_id, string $type): bool
    {
        return count(
            $this->sql_get_one(
                'customers_wishlist',
                'item_id',
                ['item_id' => $item_id, 'owner' => $user_id, 'belongs' => $type]
            )
        ) > 0;
    }

    public function getItemImageURL($item): string
    {
        $type = $item['belongs'] ?? null;

        switch ($type) {
            case 'professional':
                $professional = (new Members())->get_items_info($item['item_id']);
                $bid = md5((string) $professional['id']);
                $path = "/uploads/avatar/{$bid}/{$professional['avatar']}";

                return file_exists(DOC_ROOT.$path) ? $path : '/uploads/avatar/silhouette.jpg';
                break;

            case 'deal':
                $image = (new Deals())->get_images($item['item_id'], 1);
                $path = "/uploads/deals/{$image['bid']}/{$image['image']}";

                return ($image && file_exists(DOC_ROOT.$path)) ? $path : '/images/hot-deals-default.png';
                break;

            case 'event':
                return Events::getFirstImageURL($item['item_id']);
                break;

            case null:
            default:
                return '/images/missing_image.png';
                break;
        }
    }
}
