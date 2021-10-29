<?php
namespace Plugins\Events\Classes;

use App\Format as Format;
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

class Events extends Format
{
    private $plugin;

    public function __construct()
    {
        $this->plugin = 'events';
        $this->members = new \Plugins\Members\Classes\Members;
    }
    /**
     * List events
     * @param  integer  $start          start from
     * @param  integer  $qty            quantity to be returned
     * @param  integer  $active_only    get only active rows
     * @param  string   $future_only    get only future events
     * @param  string   $sort           sort by element
     * @param  string   $dir            direction to sort
     * @param mixed $owner
     * @return array
     */
    public function list_items($start = 0, $qty = 0, $active_only = 0, $owner = 0, $sort = 'date', $dir = 'ASC')
    {
        if (!empty($active_only)) {
            $_WHERE = " active = '1'";
        }

        if (!empty($future_only)) {
            $_WHERE .= " AND event_date >= '".date('Y-m-d')."'";
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

    public function list_birthdays()
    {
        $sql = "
            SELECT MD5(id) as bid, first_name, last_name, birthday, get_avatar(id) AS avatar
            FROM ".$this->prefix."_customers
            WHERE active = '1'
            AND MONTH(birthday) = MONTH(NOW())
            ORDER BY birthday ASC
        ";

        foreach ($this->sql_fetchrow($sql) as $data) {
            $return[] = $this->filter($data, 1);
        }

        return $return;
    }
    /**
     * events counter
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

    public function save_event($data = [])
    {
        if (!empty($data)) {
            $data = $this->filter($data, 1, 1);

            # Begin Transaction
            $this->begin_transaction();

            $data['active'] = (int) $data['active'];

            if (!empty($data['id'])) {
                $id = $data['id'];
                $data['modified'] = date('Y-m-d H:i:s');
            } else {
                unset($data['id']);
                $data['date'] = date('Y-m-d H:i:s');
                $id = $this->sql_insert('events', $data);

                // Check for an already saved (still orphan) image
                $old_bid = md5("new-{$data['owner']}");
                $new_bid = md5((string) $id);
                $values = [
                    ':owner' => $data['owner'],
                    ':bid' => $old_bid,
                    ':belongs' => 'events',
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
                    $from = "uploads/events/{$old_bid}/{$media['image']}";
                    $to = "uploads/events/{$new_bid}/{$media['image']}";
                    if (!is_dir(dirname($to))) {
                        mkdir(dirname($to), 0777, true);
                    }
                    rename($from, $to);
                }
            }

            $data['url'] = $this->gen_url($id.' '.$data['title'], 'members/events');


            $this->sql_update('events', $data, (int) $id);

            # Commit Transaction
            $this->commit();

            $this->reload_cache_item($id);

            return $id;
        }

        return false;
    }

    /**
     * Get event info based on id
     * @param  integer  $id     row id
     * @return array
     */
    public function get_items_info($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->load($this->sql_get_one('events', '*', $id));
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
     * Search events
     * @param  [type] $query       phrase / word to search
     * @param  [type] $active_only get only active events
     * @param mixed $start
     * @param mixed $qty
     * @param mixed $sort
     * @param mixed $dir
     * @return array
     */
    public function search($query, $start = 0, $qty = 0, $sort = 'date', $dir = 'DESC')
    {
        $sql = "
            SELECT *
            FROM ".$this->prefix."_events
            WHERE id != '0' 
            AND end_date >= '".date('Y-m-d')."'
       ";

        if (!empty($query['main_category'])) {
            $array = explode(" ", $query['main_category']);
            $category = $array[0];

            $sql .= "
                AND category = '".$category."'
            ";
        }

        if (!empty($query['q'])) {
            $array = explode(" ", $query['q']);
            $category = $array[0];

            $sql .= "
                AND category = '".$category."'
            ";
        }

        if (!empty($query['location'])) {
            $sql .= "
                AND (
                    city LIKE '%".$query['location']."%' OR 
                    state LIKE '%".$query['location']."%' OR
                    zipcode = '".$query['location']."'
                )
            ";
        }
        if (!empty($query['only_free'])) {
            $sql .= "
                AND price= '0.00'
            ";
        } else {
            if (isset($query['price_range_enabled']) && !empty($query['price_range'])) {
                $prices = explode(';', $query['price_range']);

                $sql .= "
                    AND price >= '".$prices[0]."'
                    AND price <= '".$prices[1]."'
                ";
            }
        }
        
        if (!empty($query['date'])) {
            if ($query['date'] == 'Tomorrow') {
                $sql .= "
                    AND start_date = '".date('Y-m-d', strtotime('+1 day'))."'
                ";
            } elseif ($query['date'] == 'This week') {
                $sql .= "
                    AND start_date > '".date('Y-m-d', strtotime('monday this week'))."'
                    AND start_date < '".date('Y-m-d', strtotime('sunday this week'))."'
                ";
            } elseif ($query['date'] == 'This weekend') {
                $sql .= "
                    AND start_date > '".date('Y-m-d', strtotime('friday this week'))."'
                    AND start_date < '".date('Y-m-d', strtotime('sunday this week'))."'
                ";
            } elseif ($query['date'] == 'This month') {
                $sql .= "
                    AND start_date > '".date('Y-m-1')."'
                    AND start_date < '".date('Y-m-t')."'
                ";
            } elseif ($query['date'] == 'Next month') {
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

        if (!empty($qty)) {
            $sql .= "
                LIMIT ".$start.", ".$qty."
            ";
        }

        foreach ($this->sql_fetchrow($sql) as $data) {
            $return[] = $this->load($data);
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
     * save events information
     * @param  array            $data data containing article information
     * @return integer          updated row id
     */
    public function save(array $data)
    {
        $data = $this->filter($data, 1, 1);

        if (!empty($data)) {
            if (!empty($data['id'])) {
                $id = $data['id'];
            } else {
                $id = $this->sql_insert_empty($this->plugin);
                $data['date'] = date('Y-m-d H:i:s');
            }

            $data['active'] = (int) $data['active'];

            if (!empty($data['event_date'])) {
                $data['event_date'] = date('Y-m-d', strtotime($data['event_date']));

                if (!empty($data['start_time'])) {
                    $data['event_date'] .= date(' H:i:s', strtotime($data['start_time']));
                }
            }

            if (!empty($data['end_date'])) {
                $data['end_date'] = date('Y-m-d', strtotime($data['end_date']));

                if (!empty($data['end_time'])) {
                    $data['end_date'] .= date(' H:i:s', strtotime($data['end_time']));
                }
            } else {
                $data['end_date'] = $data['event_date'];
            }
            
            $data['url'] = (!empty($data['url']) ? $this->gen_url($data['url'], $this->plugin) : $this->gen_url($data['title'].' '.$data['start_date'], $this->plugin));

            unset($data['id']);

            $this->sql_update($this->plugin, $data, (int) $id);
            $this->reload_cache_item($id);

            return (int) $id;
        }

        return false;
    }
    /**
     * Get article images
     * @param  integer $id      row id
     * @param  integer $qty     amount of images to collect
     * @return array
     */
    public function get_images($id = 0, $qty = '')
    {
        $id = (int) $id;

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

            return $return;
        }
        
        return false;
    }

    /**
     * Get an event image
     *
     * @param int $id row id
     * @return array
     */
    public function find(int $id = 0): array
    {
        $bid = md5((string) $id);

        return $this->sql_get_one(
            'media',
          'id, bid, image, imageId, title, text',
                  ['belongs' => $this->plugin, 'bid' => $bid, 'media' => 'Image']
        );
    }


    public function delete_event($id = 0, $owner = 0)
    {
        $id = (int) $id;
        $owner = (int) $owner;

        if (!empty($id)) {
            return $this->sql_delete('events', ['id' => $id, 'owner' => $owner]);
        }

        return false;
    }

    /**
     * Delete event from database
     * @param  integer  $id     row id
     * @return boolean
     */
    public function delete($id)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $this->sql_delete($this->plugin, (int) $id);
            parent::cache()->delete('_cache_'.$this->plugin.'_'.$id);
        }
        
        return false;
    }
    /**
     * Load and format event information
     * @param  array  $data     data to be formatted
     * @return array
     */
    private function load(array $data = [])
    {
        try {
            if (count($data) == 0) {
                throw new Exception('Submitted data is empty', 1);
            }

            // $return = parent::cache()->get('_cache_' . $this->plugin . '_' . $data['id']);

            // if($return == null)
            // {
            $return = $this->filter($data, 1);
            $return['url'] = 'events/'.$data['url'];

            $return['text'] = $this->filter($data['text']);
                
            $return['start_date'] = date('Y-m-d', strtotime($data['start_date']));
            $return['end_date'] = date('Y-m-d', strtotime($data['end_date']));

            $return['start_time'] = date('g:i A', strtotime($data['start_time']));
            $return['end_time'] = date('g:i A', strtotime($data['end_time']));
                
            $return['media'] = $this->get_images($data['id'], 1);

            $return['owner_info'] = $this->members->get_items_info($data['owner']);

            //     parent::cache()->set('_cache_' . $this->plugin . '_' . $data['id'], $return);
            // }
            
            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    /**
     * Return the URL of *only the first* of the image(s) bound to the Event.
     *
     * @param int $event_id
     * @return string
     */
    public static function getFirstImageURL(int $event_id = null): string
    {
        $default = '/images/event_icon_default.png';

        if (!$event_id) {
            return $default;
        }

        $image = (new Events())->get_images($event_id, 1);
        $path = "/uploads/events/{$image['bid']}/{$image['image']}";

        return ($image && file_exists(DOC_ROOT.$path)) ? $path : $default;
    }

    /**
     * @param int $image_id
     * @return string
     */
    public static function getThumbURL(int $image_id): string
    {
        $image = (new Events())->find($image_id);
        $path = "/uploads/events/{$image['bid']}/small-{$image['image']}";

        return ($image && file_exists(DOC_ROOT.$path)) ? $path : '/images/event_icon_default.png';
    }

    /**
     * @param int $event_id
     * @param int $user_id
     * @return bool
     */
    public function isFlagged(int $event_id, int $user_id): bool
    {
        return count(
                $this->sql_get_one(
                    'events_flags',
                    'event_id',
                    ['event_id' => $event_id, 'user_id' => $user_id]
                )
            ) > 0;
    }

    /**
     * @param int $user_id
     * @return array
     */
    public function getFlagged(int $user_id): array
    {
        $rows = $this->sql_get('events_flags', 'event_id', ['user_id' => $user_id]);

        return array_map(
            function ($row) {
                return $row['event_id'];
            },
            $rows
        );
    }
}
