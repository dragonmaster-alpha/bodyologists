<?php
namespace Plugins\Deals\Classes;

use App\Format as Format;
use Exception;
use PDO;
use Plugins\Members\Classes\Members;

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

class Deals extends Format
{
    private $plugin;
    protected $table;

    public function __construct()
    {
        $this->plugin = 'deals';
        $this->table = 'deals';

        require_once(DOC_ROOT.'/plugins/members/classes/class.members.php');
        $this->members = new \Plugins\Members\Classes\Members();
    }
    /**
     * List deals
     * @param  integer  $start          start from
     * @param  integer  $qty            quantity to be returned
     * @param  integer  $active_only    get only active rows
     * @param  string   $future_only    get only future deals
     * @param  string   $sort           sort by element
     * @param  string   $dir            direction to sort
     * @param mixed $owner
     * @return array
     */
    public function list_items($start = 0, $qty = 0, $active_only = 0, $owner = 0, $sort = 'end_date', $dir = 'ASC')
    {
        if (!empty($active_only)) {
            //$_WHERE = " start_date <= '".date('Y-m-d')."' AND end_date >= '".date('Y-m-d')."'";
            $_WHERE = " active = {$active_only}";
        }

        if (!empty($owner)) {
            $_WHERE .= " AND owner = '".$owner."'";
        }
        
        if (!empty($qty)) {
            $_LIMIT = [(int) $start, (int) $qty];
        }
        
        foreach ($this->sql_get($this->plugin, '*', $_WHERE, $sort.' '.$dir, $_LIMIT) as $data) {
            $return[] = $this->load($data);
        }

        return $return;
    }

    /**
     * deals counter
     * @param  int  $active     count only active rows
     * @param mixed $active_only
     * @return integral
     */
    public function count($active_only = 0)
    {
        $_WHERE = [];

        if (!empty($active_only)) {
            $_WHERE['active'] = 1;
        }

        return $this->sql_count($this->plugin, $_WHERE);
    }

    /**
     * Get event info based on id
     * @param integer $id row id
     * @return ?array
     * @throws \Exception
     */
    public function get_items_info($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $one = $this->sql_get_one($this->table, '*', $id);
            if (!$one) {
                return false;
            }

            return $this->load($one);
        }

        return false;
    }

    public function get_user_items_info($id = 0, $owner = 0)
    {
        $id = (int) $id;
        $owner = (int) $owner;

        if (!empty($id) && !empty($owner)) {
            return $this->load($this->sql_get_one($this->plugin, '*', ['id' => $id, 'owner' => $owner]));
        }

        return false;
    }

    /**
     * Reload info in cache
     * @param  integer  $id     user id
     * @return boolean
     */
    public function reload_cache_item($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            parent::cache()->delete('_cache_'.$this->plugin.'_'.$id);
            $this->get_items_info($id);
        }
    }

    public function get_items_from_url($url = '')
    {
        $url = $this->filter($url, 1, 1);

        if (!empty($url)) {
            $data = $this->sql_get_one($this->plugin, 'id', ['url' => $url, 'active' => 1]);

            if (!empty($data['id'])) {
                return $this->get_items_info((int) $data['id']);
            }
        }

        return false;
    }

    /**
     * Search deals
     * @param  [type] $query       phrase / word to search
     * @param  [type] $active_only get only active deals
     * @param mixed $start
     * @param mixed $qty
     * @param mixed $active
     * @param mixed $sort
     * @param mixed $dir
     * @return array
     */
    public function search($query, $start = 0, $qty = 0, $active = 0, $sort = 'end_date', $dir = 'ASC')
    {
        $sql = "
            SELECT *
            FROM ".$this->prefix."_deals
            WHERE start_date <= '".date('Y-m-d')."' 
            AND end_date >= '".date('Y-m-d')."' 
        ";

        if (!empty($active)) {
            $sql .= "
                AND active = 1
            ";
        }

        if (!empty($query['location'])) {
            $sql .= " 
                AND (
                    city LIKE '%".$query['location']."%' OR
                    state LIKE '%".$query['location']."%' 
                )
            ";
        }

        if (!empty($query['category'])) {
            $sql .= " 
                AND category LIKE '%".$query['category']."%'
            ";
        }

        if (isset($query['price_range_enabled']) && !empty($query['price_range'])) {
            $prices = explode(';', $query['price_range']);

            $sql .= "
                AND reg_price >= '".$prices[0]."'
                AND reg_price <= '".$prices[1]."'
            ";
        }
        
        if (!empty($query['to_do'])) {
            if ($query['to_do'] == 'Tomorrow') {
                $sql .= "
                    AND start_date = '".date('Y-m-d', strtotime('+1 day'))."'
                ";
            } elseif ($query['to_do'] == 'This week') {
                $sql .= "
                    AND start_date > '".date('Y-m-d', strtotime('monday this week'))."'
                    AND start_date < '".date('Y-m-d', strtotime('sunday this week'))."'
                ";
            } elseif ($query['to_do'] == 'This weekend') {
                $sql .= "
                    AND start_date > '".date('Y-m-d', strtotime('friday this week'))."'
                    AND start_date < '".date('Y-m-d', strtotime('sunday this week'))."'
                ";
            } elseif ($query['to_do'] == 'This month') {
                $sql .= "
                    AND start_date > '".date('Y-m-1')."'
                    AND start_date < '".date('Y-m-t')."'
                ";
            } elseif ($query['to_do'] == 'Next month') {
                $sql .= "
                    AND start_date > '".date('Y-m-1', strtotime('+1 month'))."'
                    AND start_date < '".date('Y-m-t', strtotime('+1 month'))."'
                ";
            } else {
                $sql .= "
                    AND start_date = '".date('Y-m-d')."'
                ";
            }
        }
        
        if (!empty($query['keyword'])) {
            $sql .= "
                AND (
                    title LIKE '%".$query['keyword']."%' OR
                    text LIKE '%".$query['keyword']."%'
                )
            ";
        }
        
        if (!empty($sort)) {
            $sql .= "
                ORDER BY ".$sort." ".$dir." 
            ";
        }

        if ($start > 0 || !empty($qty)) {
            $sql .= "
                LIMIT ".$start.", ".$qty." 
            ";
        }

        foreach ($this->sql_fetchrow($sql) as $data) {
            $return[] = $this->load($data, 1, 1);
        }

        return $return;
    }

    /**
     * Activate/ deactivate event
     * @param  integer  $id     row id
     * @return string           modified article title
     */
    public function manage_activation($id = 0)
    {
        $item_info = $this->get_items_info($id);
        $this->sql_activate($this->plugin, (int) $id);
        $this->reload_cache_item($id);
        return $item_info['title'];
    }

    /**
     * save deals information
     * @param array $data data containing article information
     * @return integer          updated row id
     * @throws \JsonException
     */
    public function save(array $data)
    {
        $data = $this->filter($data, 1, 1);

        $data['meta'] = json_encode($data['meta'], JSON_HEX_QUOT | JSON_THROW_ON_ERROR);

            # Begin Transaction
            $this->begin_transaction();

        if (!empty($data)) {
            if (!empty($data['id'])) {
                $id = $data['id'];
                $data['modified'] = date('Y-m-d H:i:s');
            } else {
                // New record
                unset($data['id']); // prevent empty strings
                $data['date'] = date('Y-m-d H:i:s');
                $id = $this->sql_insert('deals', $data);

                // Check for an already saved (still orphan) image
                $old_bid = md5("new-{$data['owner']}");
                $new_bid = md5((string) $id);
                $values = [
                    ':owner' => $data['owner'],
                    ':bid' => $old_bid,
                    ':belongs' => 'deals',
                    ':date' => date('Y-m-d H:i:s', strtotime('-1 hour'))
                ];
                $sql = "SELECT  id, image  FROM wde_media  ".
                        "WHERE `owner` = :owner AND `bid` = :bid AND `belongs` = :belongs AND `date` > :date  ".
                        "ORDER BY id DESC LIMIT 1";
                $pdo = $this->prepare($sql);
                $pdo->execute($values);
                $media = $pdo->fetchAll(PDO::FETCH_ASSOC)[0];

                if ($media) {
                    // Take ownership of the orphan image...
                    $sql = "UPDATE wde_media SET bid = '{$new_bid}' WHERE id = {$media['id']}";
                    $this->prepare($sql)->execute();

                    // ... and move it to the right folder
                    $from = "uploads/deals/{$old_bid}/{$media['image']}";
                    $to = "uploads/deals/{$new_bid}/{$media['image']}";
                    if (!is_dir(dirname($to))) {
                        mkdir(dirname($to), 0777, true);
                    }
                    rename($from, $to);
                }
            }

            $data['active'] = (int) $data['active'];

            if (!empty($data['start_date'])) {
                $data['start_date'] = date('Y-m-d', strtotime($data['start_date']));
            } else {
                $data['start_date'] = date('Y-m-d');
            }

            if (!empty($data['end_date'])) {
                $data['end_date'] = date('Y-m-d', strtotime($data['end_date']));
            } else {
                $data['end_date'] = date('Y-m-d');
            }
                        
            $data['url'] = (!empty(trim($data['url'])) ? $this->gen_url($data['url'], $this->plugin) : $this->gen_url($data['title'].' '.$data['end_date'], $this->plugin));

            unset($data['id']);

            $this->sql_update($this->plugin, $data, (int) $id);



            # Commit Transaction
            $this->commit();

            $this->reload_cache_item($id);

            return (int) $id;
        }

        return false;
    }

    /**
     * Delete event from database
     * @param integer $id row id
     * @param int $owner
     * @return boolean
     */
    public function delete(int $id, int $owner)
    {

        if (!empty($id)) {
            $this->sql_delete($this->plugin, ['id' => $id, 'owner' => $owner]);
            $this->cache()->delete('_cache_'.$this->plugin.'_'.$id);
        }
        
        return false;
    }

    public function get_images($id = 0, $qty = '')
    {
        $id = (int) $id;
        $return = [];

        if (!empty($id)) {
            if ($qty == 1) {
                return $this->filter($this->sql_get_one('media', 'id, bid, image, imageId, title, text', ['belongs' => $this->plugin, 'bid' => md5((string) $id), 'media' => 'Image'], 'id DESC'));
            }
            
            if (!empty($qty)) {
                $_LIMIT = [(int) $qty];
            }

            foreach ($this->sql_get('media', 'id, bid, image, imageId, title, text', ['belongs' => $this->plugin, 'bid' => md5((string) $id), 'media' => 'Image'], 'imageId ASC', $_LIMIT) as $data) {
                $return[] = $this->filter($data, 1, 1);
            }
        }

        return $return;
    }

    /**
     * Return the url for the image or a default one
     *
     * @param mixed $item
     * @return string
     */
    public static function getImageURL($item): string
    {

        if (!$item['media']) {
            return '/images/hot-deals-default.png';
        }

        return "/uploads/deals/{$item['media']['bid']}/{$item['media']['image']}";
    }

    /**
     * Load and format event information
     * @param  array  $data     data to be formatted
     * @return array
     */
    private function load($data = [])
    {
        try {
            if (!$data || count($data) == 0) {
                throw new \Exception('Submitted data is empty', 1);
            }

            // $return  = parent::cache()->get('_cache_' . $this->plugin . '_' . $data['id']);

            // if($return == null)
            // {
            $return = $this->filter($data, 1);
            $return['url'] = $this->plugin.'/'.$data['url'];
            $return['media'] = $this->get_images($data['id'], 1);

            if (!empty($data['reg_price']) && !empty($data['discount'])) {
                $return['dicount_percentage'] = round(($data['reg_price'] - $data['discount']) / $data['reg_price'] * 100, 0);
            }

            $return['owner_info'] = $this->members->get_items_info($data['owner']);

            $return['flag_count'] = $this->getReportedCount($data['id']);

            //     parent::cache()->set('_cache_' . $this->plugin . '_' . $data['id'], $return);
            // }
            
            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    /**
     * @param int $deal_id
     * @param int $user_id
     * @return int
     */
    protected function getReportedCount(int $deal_id): int
    {
        return $this->sql_count('deals_flags', ['deal_id' => $deal_id]);
    }

    /**
     * @param int $deal_id
     * @param int $user_id
     * @return bool
     */
    public function isFlagged(int $deal_id, int $user_id): bool
    {
        return count(
                $this->sql_get_one(
                    'deals_flags',
                    'deal_id',
                    ['deal_id' => $deal_id, 'user_id' => $user_id]
                )
            ) > 0;
    }

    /**
     * @param int $user_id
     * @return array
     */
    public function getFlagged(int $user_id): array
    {
        $rows = $this->sql_get('deals_flags', 'deal_id', ['user_id' => $user_id]);

        return array_map(
            function ($row) {
                return $row['deal_id'];
            },
            $rows
        );
    }

    /**
     * @param $deal_id
     * @return array
     */
    public function getMedia($deal_id): array
    {
        $cond = [
            'belongs' => 'deals',
            'bid' => md5((string) $deal_id),
        ];
        return $this->sql_get_one('media', '*', $cond, 'id DESC') ?? [];
    }
}
