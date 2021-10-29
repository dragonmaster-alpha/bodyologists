<?php

namespace Plugins\Members\Classes;

defined('DOC_ROOT') || define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT']);
defined('APP_DIR') || define('APP_DIR', DOC_ROOT.'/app');
ini_set('include_path', APP_DIR);

require_once(APP_DIR.'/security/class.encrypt.php');

use App\Db\Database;
use App\File;
use App\Flash as Flash;
use App\Format as Format;
use App\Helper;
use App\Log as Log;
use App\Security\Encrypt as Encrypt;
use App\Settings as Settings;
use Exception;
use PDO;

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

class Members extends Format
{
    protected $plugin;
    protected $table;
    private $settings;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->plugin = 'members';
        $this->table = 'customers';

        $settings = new Settings();
        $this->settings = $settings->get('members');
    }

    /**
     * @return array|null
     */
    public static function getCurrentUser(): ?array
    {
        return !empty($_SESSION['user_info']['id']) ? $_SESSION['user_info'] : null;
    }

    /**
     * @return int
     */
    public static function currentUserId(): int
    {
        return (int) (self::getCurrentUser()['id'] ?? null);
    }

    /**
     * List users in database
     * @param  integer 	$start 			start from
     * @param  integer 	$qty   			quantity to be returned
     * @param  integer 	$active_only 	get only active rows
     * @param  integer 	$category       get only belonging to certain group
     * @param  string  	$sort  			sort by element
     * @param  string  	$dir   			direction to sort
     * @param mixed $location
     * @return array
     */
    public function list_items($start = 0, $qty = 0, $active_only = 0, $category = 0, $location = '', $sort = 'date', $dir = 'DESC')
    {
        try {
            $where = [];
            //$where['alive'] = 0; Commented because it wont show all Clients in admin

            if (!empty($active_only)) {
                $where['active'] = 1;
            }

            if (!empty($category)) {
                $where['category'] = $category;
            }

            if (!empty($location)) {
                $where['city'] = $location;
            }

            if (!empty($sort)) {
                $order = $sort." ".$dir;
            }

            if (!empty($qty)) {
                $limit = [$start, $qty];
            }

            foreach ($this->sql_get($this->table, '*', $where, $order, $limit) as $info) {
                $return[] = $this->load($info);
            }
            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    /**
     * Users counter
     * @param  int 	$active 	count only active rows
     * @return int
     */
    public function count($active = 0)
    {
        $_WHERE['alive'] = 0;

        if (!empty($active)) {
            $_WHERE['active'] = 1;
        }

        return $this->sql_count($this->table, $_WHERE);
    }
    /**
     * Get user info based on id
     * @param  integer 	$id 	user id
     * @return ?array
     */
    public function get_items_info($id = null)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $row = $this->sql_get_one($this->table, '*', (int) $id);
            return $this->load($row);
        }

        return [];
    }
    /**
     * Get user info based on url
     * @param  integer  $url     user url
     * @return array
     */
    public function get_items_from_url($url = '')
    {
        $url = $this->filter($url, 1, 1);

        if (!empty($url)) {
            $data = $this->sql_get_one($this->table, 'id', ['url' => $url, 'professional' => 1, 'active' => 1]);

            if (!empty($data['id'])) {
                return $this->get_items_info((int) $data['id']);
            }
        }

        return false;
    }
    /**
     * Reload user's info in cache
     * @param  integer 	$id 	user id
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
    /**
     * Validate user data after email validation
     *
     * @param int $id  user id sent by email
     * @return bool
     */
    public function get_validation_data(int $id = 0): bool
    {
        return ($this->sql_count($this->table, $id) === 1);
    }
    /**
     * Get group info
     * @param  integer 	$id 	user id
     * @return array
     */
    public function get_group_info($id = 0)
    {
        $id = (int) $id;

        if (!empty($id) && $this->table_exists('customers_groups')) {
            return $this->sql_get_one('customers_groups', '*', $id);
        }

        return false;
    }
    /**
     * checks if a url address is right
     * @param  string $email
     * @param mixed $string
     * @return string
     */
    public function check_username($string = '')
    {
        return preg_match('/^[A-Za-z][A-Za-z0-9]*(?:_[A-Za-z0-9]+)*$/', $string);
    }
    /**
     * Get user info from username
     * @param  string 	$username 	username
     * @return array
     */
    public function get_user_by_username($username = '')
    {
        $username = $this->filter($username, 1, 1);
        if (!empty($username) && $this->check_username($username)) {
            return $this->load($this->sql_get_one($this->table, '', ['username' => $username, 'active' => 1, 'alive' => 0]));
        }

        return false;
    }
    /**
     * Check if username already exists
     * @param  string 	$username 	username to be checked
     * @return bool
     */
    public function check_user_from_username($username = '')
    {
        if (!empty($username) && $this->check_username($username)) {
            return $this->sql_count($this->table, "username = '".$username."' AND id != '".(int) $_SESSION['user_info']['id']."'");
        }

        return true;
    }

    /**
     * Get user info from email address
     * @param string $email user email address
     * @return array|bool
     */
    public function get_user_by_email($email = '')
    {
        if (!empty($email) && $this->check_email($email)) {
            return $this->filter($this->sql_get_one($this->table, '*', ['email' => $email]), 1);
        }

        return false;
    }

    /**
     * Checks if an email address already exists in database
     * @param  string 	$email 		email to be checked
     * @return bool
     */
    public function check_user_from_email($email = '')
    {
        return ($this->sql_count($this->table, "email = '".$email."' AND id != '".(int) $_SESSION['user_info']['id']."'") > 0) ? true : false;
    }

    /**
     * Search within users database
     * @param array $query query to search for
     * @param integer $start start from
     * @param integer $qty quantity to be returned
     * @param string $sort sort by element
     * @param string $dir direction to sort
     * @return array
     */
    public function search($query = [], $start = 0, $qty = 10, $sort = 'date', $dir = 'DESC'): ?array
    {
        /**
        DELIMITER ;;
        DROP FUNCTION IF EXISTS ANY_VALUE_IN_RANGE;;
        CREATE FUNCTION `ANY_VALUE_IN_RANGE`(`collection` JSON, `min` FLOAT, `max` FLOAT) RETURNS BOOL
        READS SQL DATA
        BEGIN
        DECLARE i INTEGER DEFAULT 0;
        DECLARE entry FLOAT;

        WHILE i < JSON_LENGTH(collection) DO
        SET entry = JSON_EXTRACT(collection,CONCAT('$[',i,']'));

        -- DEBUG
        -- INSERT INTO tmptable SELECT CONCAT(entry,' - ',`min`,' - ',`max`,' - ',entry BETWEEN `min` AND `max`);

        IF entry BETWEEN `min` AND `max` THEN
        RETURN TRUE;
        END IF;

        SELECT i + 1 INTO i;

        END WHILE;

        RETURN FALSE;

        END ;;
        DELIMITER ;
         */

        /**
         * FULL QUERY SAMPLE
         * SELECT * FROM wde_customers
            WHERE
            (email LIKE '%Pablo%' OR first_name LIKE '%Pablo%' OR last_name LIKE '%Pablo%' OR phone LIKE '%Pablo%' OR category LIKE '%Pablo%' OR main_category  LIKE '%Pablo%')
            AND
            (city LIKE '%1657%' OR state LIKE '%1657%' OR zipcode = '1657')
            AND
            (insurance LIKE '%Delta Dental%')
            AND
            gender = 'male'
            AND
            languages = 'Croatian'
            AND
            (available >= '2020-04-07 19:19')
            AND
            JSON_VALID(extra) AND (extra->"$.location.clients_location" = '1')
            AND
            JSON_VALID(extra) AND JSON_CONTAINS(`extra`, '["Bio Feedback"]', '$.training.certification')
            AND
            JSON_VALID(extra) AND JSON_CONTAINS(`extra`, '["Cholesterol"]', '$.conditions')
            AND
            JSON_VALID(extra) AND ANY_VALUE_IN_RANGE(extra->>"$.service.fees", 90, 420)
            AND
            professional = 1
            ORDER BY DATE DESC
            LIMIT 0, 10
         */
        try {
            $conditions = [];
            $_arguments = [
                'email',
                'first_name',
                'last_name',
                'phone',
                'category',
                'main_category',
            ];

            if (!empty($query['q'])) {
                $conditions[] = '('.implode(" LIKE '%".$query['q']."%' OR ", $_arguments)."  LIKE '%".$query['q']."%')";
            }

            if (!empty($query['keyword'])) {
                $conditions[] = '('.implode(" LIKE '%".$query['keyword']."%' OR ", $_arguments)."  LIKE '%".$query['keyword']."%')";
            }

            if (!empty($query['location'])) {
                $conditions[] = "(city LIKE '%{$query['location']}%' OR state LIKE '%{$query['location']}%' OR zipcode = '{$query['location']}')";
            }

            if (!empty($query['insurance'])) {
                $conditions[] = "(insurance LIKE '%".$query['insurance']."%')";
            }

            if (!empty($query['gender'])) {
                $conditions[] = "gender = '{$query['gender']}'";
            }

            if (!empty($query['language'])) {
                $conditions[] = "(languages LIKE '%{$query['language']}%')";
            }

            if (isset($query['now_available'])) {
                $conditions[] = "(available >= '".date('Y-m-d H:i')."')";
            }

            if (isset($query['external_visits'])) {
                $conditions[] = "JSON_VALID(extra) AND JSON_CONTAINS(`extra`, '\"1\"', '$.location.clients_location')";
            }

            if (!empty($query['specialty'])) {
                $terms = is_array($query['specialty']) ?
                    json_encode(explode(',', $query['specialty'])) :
                    '"'.$query['specialty'].'"' ;
                $conditions[] = "JSON_VALID(extra) AND JSON_CONTAINS(`extra`, '[{$terms}]', '$.training.certification')";
            }

            if (!empty($query['conditions'])) {
                $queryConditions = str_replace("/", "\\\/", $query['conditions']);
                $terms = is_array($queryConditions) ?
                    json_encode(explode(',', $queryConditions)) :
                    '"'.$queryConditions.'"' ;
                $conditions[] = "JSON_VALID(extra) AND JSON_CONTAINS(`extra`, '[{$terms}]', '$.conditions')";
            }

            $feeSubQuery = '';
            $havingStatement = '';
            $havingArray = [];
            if ($query['price_range_enabled']) {
                $feeSubQuery .= ", (SELECT MIN(fee) FROM wde_customer_services WHERE wde_customers.id = wde_customer_services.user_id) as min_fee, 
                (SELECT MAX(fee) FROM wde_customer_services WHERE wde_customers.id = wde_customer_services.user_id) AS max_fee";

                [$min, $max] = explode(';', $query['price_range']);
                $havingArray[] = "min_fee >= {$min} AND max_fee <= {$max}";
            }

            $distanceSubQuery = '';
            if (isset($query['location_lat']) && isset($query['location_lng'])) {
                $distanceSubQuery = ", getDistance(location_lat, location_lng, {$query['location_lat']}, {$query['location_lng']}) as distance_in_km";

            }


            if ($query['distance_enabled'] == 'on') {
                $queryDistanceKm = $query['distance'] * 1.609344;
                $havingArray[] = "distance_in_km < {$queryDistanceKm}";
            }

            if (!empty($havingArray)) {
                $havingStatement = "HAVING " . implode(" AND ", $havingArray);
            }


            if ($sort == "fee") {
                $feeSubQuery .= ", (SELECT MIN(fee) FROM wde_customer_services WHERE wde_customers.id = wde_customer_services.user_id) as fee";
            }

            $conditions[] = 'professional = 1';
            $sorting = "ORDER BY {$sort} {$dir}";
            $limits = "LIMIT {$start}, {$qty}";

            $sql  = "SELECT *{$feeSubQuery}{$distanceSubQuery} FROM {$this->prefix}_customers";
            $sql .= ' WHERE '.implode(' AND ', $conditions).' '.$havingStatement.' '.$sorting.' '.$limits;

            foreach ($this->sql_fetchrow($sql) as $data) {
                $return[] = $this->load($data);
            }

            return $return;
        } catch (Exception $e) {
            Database::log_database_error($e, $sql);
        }
    }
    /**
     * Get user avatar main image
     * @param  integer 	$id 		User id in database
     * @return string 	image name
     */
    public function get_avatar($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $data = $this->sql_get_one('media', 'image', ['belongs' => 'avatar', 'bid' => md5((string) $id), 'media' => 'Image'], 'id DESC');
            return $this->filter($data['image'], 1);
        }

        return 'silhouette.jpg';
    }
    /**
     * Insert an empty avatar for a newly registered user
     * @param  integer $id customer id
     * @return boolean
     */
    public function save_empty_avatar($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->sql_insert('media', ['parent' => 0, 'belongs' => 'avatar', 'owner' => $id, 'bid' => md5((string) $id), 'media' => 'Image', 'imageId' => 0, 'image' => 'silhouette.jpg', 'meta_info' => '']);
        }

        return false;
    }
    /**
     * Count user comments
     * @param  integer 	$id 		User id in database
     * @return integral
     */
    public function count_comments($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->sql_count('comments', $id);
        }

        return 0;
    }
    # Addresses Handler
    /**
     * Get all user addresses
     * @param  integer 	$owner   	owner id
     * @param  string  	$main 		set to get only the main address
     * @return array
     */
    public function get_addresses($owner = 0, $main = '')
    {
        $owner = (int) $owner;

        if (!empty($owner)) {
            $where = [];
            $where['owner'] = $owner;

            if (!empty($main)) {
                $where['add_type'] = '1';
            }

            foreach ($this->sql_get('customers_address', '', $where, 'id ASC') as $data) {
                $return[] = $this->filter($data, 1);
            }

            return $return;
        }

        return false;
    }

    /**
     * Get specific address
     * @param integer $id address id
     * @param integer $owner user id
     * @return array|bool|string ?array
     */
    public function get_address($id = 0, $owner = 0)
    {
        $id = (int) $id;
        $owner = (int) $owner;

        if (!empty($id) && !empty($owner)) {
            return $this->filter($this->sql_get_one('customers_address', '*', ['id' => $id, 'owner' => $owner]), 1);
        }
        return false;
    }

    /**
     * Save address
     * @param array $request
     * @return bool
     */
    public function save_address(array $request = [])
    {
        $data = Helper::removeEmptyFieldsRecursive($request);

        // Pre-fill data to avoid "not null allowed" database errors
        $data['first_name'] = $data['first_name'] ?? '';
        $data['last_name'] = $data['last_name'] ?? '';
        $data['address'] = $data['address'] ?? '';
        $data['suite'] = $data['suite'] ?? '';
        $data['city'] = $data['city'] ?? '';
        $data['state'] = $data['state'] ?? '';
        $data['zipcode'] = $data['zipcode'] ?? '';
        $data['phone'] = $data['phone'] ?? '';
        $data['phone_ext'] = $data['phone_ext'] ?? 0;
        $data['mobile_phone'] = $data['mobile_phone'] ?? '';

        if (!empty($data)) {
            if (!empty($data['id'])) {
                $id = (int) $data['id'];
            } else {
                $id = $this->sql_insert('customers_address', $data);
            }

            unset($data['id']);

            $data['owner'] = (int) $data['owner'];

            if (!empty($data['phone'])) {
                $data['phone'] = $this->phone($data['phone']);
            }
            if (!empty($data['business_phone'])) {
                $data['business_phone'] = $this->business_phone($data['phone']);
            }

            if (!empty($data['mobile_phone'])) {
                $data['mobile_phone'] = $this->phone($data['mobile_phone']);
            }
            if (!empty($data['phone_ext'])) {
                $data['phone_ext'] = $this->int($data['phone_ext']);
            }

            if (!empty($data['add_type'])) {
                $this->sql_update('customers_address', ['add_type' => '0'], ['owner' => $data['owner']]);
            }

            $this->sql_update('customers_address', $data, (int) $id);

            return (int) $id;
        }

        return false;
    }

    /**
     * Delete specific address
     * @param  integer 	$id    		address id
     * @param  integer 	$owner 		user id
     * @return bool
     */
    public function delete_address($id = 0, $owner = 0)
    {
        $id = (int) $id;
        $owner = (!empty($owner)) ? (int) $owner : (int) $_SESSION['user_info']['id'];

        if (!empty($id)) {
            $item_info = $this->get_address($id, $owner);

            if (!empty($item_info['add_type'])) {
                $this - sql_update('customers_address', ['add_type' => 1], 'owner = '.$owner.' AND id != '.$id, 'id ASC', 1);
            }

            $this->sql_delete('customers_address', ['id' => $id, 'owner' => $owner]);
            return true;
        }

        return false;
    }

    # Notifications management
    /**
     * Get user notifications
     * @param  integer 	$id 		user id
     * @return array
     */
    public function get_notifications($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            foreach ($this->sql_fetchrow('customers_notices', '', ['receiver' => $id], 'date DESC') as $data) {
                $return[] = $this->filter($data, 1);
            }

            return $return;
        }

        return false;
    }
    /**
     * Edit notifications
     * @param  integer 	$id 		user id
     * @return array
     */
    public function edit_notifications($id = 0)
    {
        $id = (int) $id;
        $owner = (int) $_SESSION['user_info']['id'];

        if (!empty($id) && !empty($owner)) {
            $data = $this->sql_get_one('customers_notices', '', ['id' => $id, 'receiver' => $owner]);
            return $this->filter($data, 1);
        }

        return false;
    }
    /**
     * Save user notification
     * @param  integer 	$owner   	user id
     * @param  string  	$subject 	notification subject
     * @param  string  	$message 	notification body
     * @return bull
     */
    public function save_notifications($owner = 0, $subject = '', $message = '')
    {
        $owner = (int) $owner;

        if (!empty($owner) && !empty($subject) && !empty($message)) {
            $this->sql_insert('customers_notices', [
                'receiver' => $owner,
                'subject' => $subject,
                'message' => $message,
                'active' => 0,
                'date' => time()
            ]);

            return true;
        }

        return false;
    }
    /**
     * Delete user notifications
     * @param  integer 	$id 		user id
     * @return bool
     */
    public function delete_notifications($id = 0)
    {
        $id = (int) $id;
        $owner = (int) $_SESSION['user_info']['id'];

        if (!empty($id)) {
            return $this->sql_delete('customers_notices', ['id' => $id, 'receiver' => $owner]);
        }

        return false;
    }

    /**
     * Update user info
     * @param array $request
     * @return int affected user id
     */
    public function save(array $request): ?int
    {
        try {
            // If [id], assume editing actual user
            //   Check new email is not already taken
            // else if [email], is registering

            $actual = $this->sql_get_one($this->table, '*', ['id' => (int)$request['id']]);
            if ($actual) {
                if ($this->check_user_from_email($request['email'])) {
                    throw new Exception(_ERROR_EMAIL_ALREADY_EXISTS);
                }
            }

            // Trim and unset empty fields
            $body = Helper::removeEmptyFieldsRecursive($request);
            $current = Helper::removeEmptyFieldsRecursive($actual);

            // Hydrate current user and apply changes
            $data = array_merge((array)$current, (array)$body);

            # Assign a Plan to Client
            if (!empty($body['plan'])) {
                if (empty($body['next_payment']) && $this->table_exists('customers_packages')) {
                    $packages_info = $this->sql_get_one('customers_packages', 'period', (int)$body['plan']);
                    $data['next_payment'] = date('Y-m-d', time() + ((int)$packages_info['period'] * 24 * 60 * 60));
                } else {
                    $data['next_payment'] = date('Y-m-d', strtotime($body['next_payment']));
                }
            }

            # Assign Group to Client
            if (empty($body['grouped'])) {
                if ($this->table_exists('customers_groups')) {
                    $data['grouped'] = $this->sql_get_one('customers_groups', 'id', ['main_group' => 1])['id'];
                }
            }

            //Pre-fill fields to avoid "not null allowed" database errors
            $data['next_payment'] = $body['next_payment'] ?? '1969-12-31';
            $data['last_login'] = $body['last_login'] ?? date('Y-m-d H:i:s');
            $data['bio'] = $body['bio'] ?? '';
            $data['extra'] = $body['extra'] ?? '';
            $data['languages'] = $body['languages'] ?? '';
            $data['notes'] = $body['notes'] ?? '';
            $data['url'] = $body['url'] ?? null;
            $data['website'] = $body['website'] ?? '';
            $data['email'] = $body['email'] ?? '';
            $data['phone'] = $body['phone'] ?? '';
            $data['insurance'] = $body['insurance'] ?? '';
            $data['cc'] = $body['cc'] ?? '';
            $data['tt'] = $body['tt'] ?? '';

            $id = $body['id'] ?? null;
            if ($id) {
                unset($body['id']);

                if (empty($body['pwd'])) {
                    unset($body['pwd']);
                } else {
                    $data['pwd'] = Encrypt::encryptPasswd($body['pwd']);
                }
            }
            $data['date'] = !empty($data['date']) ? $data['date'] : date('Y-m-d H:i:s');
            if (!empty($body['passwd'])) {
                $data['pwd'] = Encrypt::encryptPasswd($body['passwd']);
            } elseif (!empty($body['pwd'])) {
                $data['pwd'] = Encrypt::encryptPasswd($body['pwd']);
            } else {
                $data['pwd'] = Encrypt::encryptPasswd(time());
            }

            $data['active'] = $actual ? 1 : 0;

            if (!empty($body['notes'] && $id)) {
                $data['notes'] = $this->notes_format($body['notes'], $id, $body['author']);
            } else {
                unset($body['notes']);
            }

            $data['gender'] = $body['gender'];

            if (!empty($body['message']) && $id) {
                $data['notes'] .= $this->notes_format('[SEND AS EMAIL] ' . $body['message'], $id, $body['author']);
            }
            if (!empty($body['first_name'])) {
                $data['first_name'] = ucwords(strtolower($body['first_name']));
            }
            if (!empty($body['last_name'])) {
                $data['last_name'] = ucwords(strtolower($body['last_name']));
            }
            if (!empty($body['company'])) {
                $data['company'] = ucwords(strtolower($body['company']));
            }
            if (!empty($body['website'])) {
                $data['website'] = strtolower($body['website']);
            } else {
                $data['website'] = '';
            }
            if (!empty($body['bio'])) {
                $data['bio'] = ucfirst(strtolower($body['bio']));
            } else {
                $data['bio'] = '';
            }
            if (!empty($body['birthday'])) {
                $data['birthday'] = date('Y-m-d', strtotime($body['birthday']));
            }
            if (!empty($body['next_payment'])) {
                $data['next_payment'] = date('Y-m-d', strtotime($body['next_payment']));
            }

            if (!empty($body['extra'])) {
                Helper::ucFirstRecursive($body['extra'], ['license', 'reviews', 'about_me', 'location', 'conditions', 'accepted_payments']);

                // Finally, fix -some specific fields- back! :facepalm:
                $body['extra']['education']['city'] = ucwords($body['extra']['education']['city']);
                $body['extra']['license_state'] = ucwords($body['extra']['license_state']);
                $body['extra']['location']['clients_location_text'] = ucwords($body['extra']['location']['clients_location_text']);

                foreach ($body['extra']['training']['city'] as $k => $v) {
                    $body['extra']['training']['city'][$k] = ucwords($v);
                }
                foreach ($body['extra']['training']['certification'] as $k => $v) {
                    $body['extra']['training']['certification'][$k] = ucwords($v);
                }
                foreach ($body['extra']['faq']['question'] as $k => $v) {
                    $body['extra']['training']['question'][$k] = ucfirst($v);
                }
                foreach ($body['extra']['faq']['answer'] as $k => $v) {
                    $body['extra']['faq']['answer'][$k] = Helper::ucfirstOnMultiline($v);
                }
                $body['extra']['policies'] = Helper::ucfirstOnMultiline($body['extra']['policies']);

                // Merge up!
                $data['extra'] = json_encode(array_filter($body['extra']), JSON_THROW_ON_ERROR);
            } else {
                $data['extra'] = '';
            }

            if (!empty($body['city'])) {
                $data['city'] = ucwords(strtolower($body['city']));
            } else {
                $data['city'] = ucwords(strtolower($body['addresses'][0]['city']));
            }

            if (!empty($body['state'])) {
                $data['state'] = strtoupper($body['state']);
            } else {
                $data['state'] = strtoupper($body['addresses'][0]['state']);
            }

            if (!empty($body['zipcode'])) {
                $data['zipcode'] = (int)$body['zipcode'];
            } else {
                $data['zipcode'] = (int)$body['addresses'][0]['zipcode'];
            }

            if (isset($request['address']) && isset($request['city']) && isset($request['state'])) {
                $coordinates = $this->getLocationCoordinates($request['address'], $request['city'], $request['state']);
                if ($coordinates != false) {
                    $data['location_lat'] = $coordinates['lat'];
                    $data['location_lng'] = $coordinates['lng'];
                }
            }

            $data['professional'] = 1;

            // If populated, filter. Otherwise, generate it
            if (!empty($body['url']) && $id) {
                // Clean out and format
                $data['url'] = $this->link($body['url']);

                // Check if already taken
                $query = "SELECT COUNT(1) as count FROM {$this->prefix}_{$this->table} WHERE url = ? AND id != ?";
                $statement = $this->prepare($query);
                $statement->execute([$body['url'], $id]);
                $count = $statement->fetch(PDO::FETCH_ASSOC);
                $exists = $count['count'];

                if ($exists) {
                    throw new Exception("URL already in use. Please try another one.");
                }
            } else {
                $data['url'] = $this->gen_url(
                    $body['city'] . ' ' .
                    $body['state'] . ' ' .
                    !empty(
                    $body['company'] ?
                        $body['company'] :
                        $body['first_name'] . ' ' . $body['last_name']
                    ) . ' ' . random_int(10, 999),
                    $this->plugin
                );
            }
            if (!$id) {
                $id = $this->sql_insert($this->table, $data);
            } else {
                $this->sql_update($this->table, $data, (int)$id);
                $this->reload_cache_item($id);
            }

            # Update newsletters info
            if ($this->table_exists('subscriptions')) {
                if (!empty($body['category'])) {
                    $category_name = $this->sql_get_one('customers_groups', 'name', (int)$body['category']);
                } else {
                    $category_name['name'] = 'Registered User';
                }

                if ($this->count('subscriptions', ['email' => $body['email']]) > 0) {
                    $this->sql_update(
                        'subscriptions',
                        [
                            'category' => $category_name['name'],
                            'name' => $body['first_name'] . ' ' . $body['last_name'],
                            'email' => $body['email']
                        ],
                        ['email' => $body['email']]
                    );
                } else {
                    $this->sql_update(
                        'subscriptions',
                        [
                            'category' => $category_name['name'],
                            'name' => $body['first_name'] . ' ' . $body['last_name'],
                            'email' => $body['email']
                        ]
                    );
                }
            }

            # Send email if needed
            if (!empty($body['message'])) {
                $this->config = parent::get_config();
                ob_start('replace_for_mod_rewrite');
                require_once($_SERVER["DOCUMENT_ROOT"] . _SITE_PATH . '/plugins/members/layout/emails/layout.notifications.email.phtml');
                $body = ob_get_contents();
                ob_end_clean();

                $email_subject = (!empty($body['subject'])) ? $body['subject'] : "About your account on " . $this->site_domain(
                    ) . "...";
                $this->send_emails($email_subject, $data, '', '', $data['email'], $data['first_name']);
            }

            if ($data['professional'] == 1 && isset($body['extra']['service'])) {
                $this->resolveServices($body['extra']['service']);
            }

            $this->update_session();
            return $id;
        } catch (Exception $e) {
            $_SESSION['error']['message'] = $e->getMessage();
        }
    }

    protected function getLocationCoordinates($address = '', $city = '', $state = '') {
        $path = "https://maps.googleapis.com/maps/api/geocode/json?address=". urlencode($address . "," . $city . "," . $state ) . "&key=" . _GOOGLE_MAP_KEY;
        $response = json_decode($this->getResponseFromURL($path), true);

        if (!empty($response['results'])){
            return $response['results'][0]['geometry']['location'];
        } else {
            return false;
        }

    }

    protected function getResponseFromURL($path){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$path);
        curl_setopt($ch, CURLOPT_FAILONERROR,1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $retValue = curl_exec($ch);
        curl_close($ch);
        return $retValue;
    }

    protected function resolveServices(array $services){
        if ($this->table_exists('customer_services')) {
            if (empty($services)) {
                return;
            }

            $counter = max(count($services['name']), count($services['id']), count($services['fees']));
            for($i = 0; $i < $counter; $i++){
                if (empty($services['id'][$i])){
                    $this->sql_insert('customer_services', [
                        'user_id' => $_SESSION['user_info']['id'],
                        'name' => $services['name'][$i],
                        'fee' => $services['fees'][$i]
                    ]);
                } else {
                    if (empty($services['name'][$i]) && empty($services['fees'][$i])) {
                        $this->sql_delete('customer_services', ['id' => $services['id'][$i]]);
                    } else {
                        $this->sql_update('customer_services', [
                            'user_id' => $_SESSION['user_info']['id'],
                            'name' => $services['name'][$i],
                            'fee' => $services['fees'][$i]
                        ], $services['id'][$i]);
                    }
                }
            }
        }
    }

    public function update(array $data)
    {
        return $this->save($data);
    }
    /**
     * Insert user information
     * @param  array  $data 	data to be inserted
     * @return [type]       	created row id
     */
    public function insert(array $data)
    {
        return $this->save($data);
    }

    /**
     * Insert user information collected from a social network
     * @param  array  $data 	data to be inserted
     * @return [type]       	created row id
     */
    public function insert_social_user(array $data = [])
    {
        try {
            if (!empty($data['id'])) {
                $id = $data['id'];
                unset($data['id']);
            } else {
                $id = $this->sql_insert_empty($this->table);
                $data['date'] = date('Y-m-d H:i:s');
            }

            $data['birthday'] = date('Y-m-d', strtotime($data['birthday']));
            $data['pwd'] = Encrypt::encryptPasswd(time());
            $data['newsletters'] = 1;
            $data['active'] = 1;
            $data['date'] = time();
            $data['alive'] = 0;

            $this->sql_update($this->table, $data, (int) $id);

            # Update newsletters info
            if ($this->table_exists($this->prefix."_newsletters_users")) {
                $this->sql_save('newsletters_users', ['category' => 'User From Facebook', 'name' => ucwords($data['first_name']).' '.ucwords($data['last_name']), 'email' => $data['email']]);
            }

            $_SESSION['user_info'] = $this->get_items_info($id);
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    /**
     * Delete user information from DB and cache
     * @param  integer 	$id 		user id
     * @return bool
     */
    public function delete($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $this->sql_delete($this->table, $id);
            $this->sql_delete('customers_address', ['owner' => $id]);
            $this->sql_delete('customers_notices', ['receiver' => $id]);
            $this->sql_delete('customers_wishlist', ['owner' => $id]);

            parent::cache()->delete('_cache_'.$this->plugin.'_'.$id);

            return true;
        }

        return false;
    }

    # Members Management
    /**
     * Activate/Deacivate user in database
     * @param  integer 	$id 		user id
     * @return string
     */
    public function manage_activation($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $item_info = $this->get_items_info($id);
            $this->sql_activate($this->table, (int) $id);
            $this->reload_cache_item($id);

            return $item_info['full_name'];
        }

        return false;
    }
    /**
     * Set/Unset user as featured
     * @param  integer 	$id 		user id
     * @return string
     */
    public function manage_featured($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $item_info = $this->get_items_info($id);
            $this->sql_set($this->table, $id, 'featured');
            $this->reload_cache_item($id);

            return $item_info['full_name'];
        }

        return false;
    }
    /**
     * Update newsletters subscription status
     * @param  array 	$data 		data to be set
     * @return bool
     */
    public function update_newsletters_info(array $data)
    {
        if (!empty($data)) {
            if ($this->table_exists('newsletters_users')) {
                if (!empty($data['newsletters'])) {
                    $this->sql_update('newsletters_users', ['name' => $data['first_name']." ".$data['last_name'], 'email' => $data['email']], ['email' => $_SESSION['user_info']['email']]);
                } else {
                    $this->sql_delete('newsletters_users', ['email' => $_SESSION['user_info']['email']]);
                }
            }

            return true;
        }

        return false;
    }
    /**
     * Prepares notes to be set into database
     * @param  string  	$notes  	notes text
     * @param  integer 	$id     	user id
     * @param  string  	$author 	name of the notes author
     * @return string          		html formatted notes
     */
    public function notes_format($notes = '', $id = 0, $author = '')
    {
        if (!empty($notes)) {
            $return = '';

            if (!empty($id)) {
                $old_notes = $this->sql_get_one($this->table, ['notes'], (int) $id);
                $return .= $old_notes['notes'];
            }

            $return .= '<li><div><b>From '.$author.' on '.date('M jS g:iA').'</b><br />'.$notes.'</div></li>';

            return $return;
        }

        return false;
    }
    /**
     * Save user notes to database
     * @param  array  $data 		data to be saved
     * @return bool
     */
    public function save_notes(array $data = [])
    {
        if (!empty($data['id']) && !empty($data['notes'])) {
            $notes = $this->notes_format($data['notes'], $data['id'], $data['author']);

            $this->sql_update($this->table, ['notes' => $notes], (int) $data['id']);
            $this->reload_cache_item($data['id']);

            return true;
        }

        return false;
    }

    # Export Clients
    /**
     * Export users information
     * @return array
     */
    public function export()
    {
        try {
            $counter = 0;
            $csv = [];

            $csv[$counter][] = 'ID';
            $csv[$counter][] = 'Gerder';
            $csv[$counter][] = 'Birthday';
            $csv[$counter][] = 'First Name';
            $csv[$counter][] = 'Last Name';
            $csv[$counter][] = 'Username';
            $csv[$counter][] = 'Company';
            $csv[$counter][] = 'Email';
            $csv[$counter][] = 'Phone';
            $csv[$counter][] = 'Fax';
            $csv[$counter][] = 'Last Login';
            $csv[$counter][] = 'Notes';

            $counter++;
            foreach ($this->sql_get($this->table, '', '', 'id ASC') as $data) {
                $csv[$counter][] = $data['id'];
                $csv[$counter][] = $data['gender'];
                $csv[$counter][] = date('m/d/Y', strtotime($data['birthday']));
                $csv[$counter][] = $data['first_name'];
                $csv[$counter][] = $data['last_name'];
                $csv[$counter][] = $data['username'];
                $csv[$counter][] = $data['company'];
                $csv[$counter][] = $data['email'];
                $csv[$counter][] = $data['phone'];
                $csv[$counter][] = $data['fax'];
                $csv[$counter][] = $this->format_sort_date($data['last_login']);
                $csv[$counter][] = $data['notes'];
                $counter++;
            }
            return $csv;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function get_events($id = 0, $start = 0, $qty = 0, $sort = 'date', $dir = 'DESC')
    {
        $id = (int) $id;

        if (!empty($sort)) {
            $_ORDER = $sort.' '.$dir;
        }
        if (!empty($qty)) {
            $_LIMIT = [(int) $start, (int) $qty];
        }

        if (!empty($id)) {
            foreach ($this->sql_get('events', '*', ['owner' => $id], $_ORDER, $_LIMIT) as $data) {
                $return[] = $this->filter($data, 1);
            }

            return $return;
        }

        return false;
    }

    public function get_event($id = 0, $owner = 0)
    {
        $id = (int) $id;
        $owner = (int) $owner;

        if (!empty($id) && !empty($owner)) {
            return $this->filter($this->sql_get_one('events', '*', ['id' => $id, 'owner' => $owner]), 1);
        }

        return false;
    }

    public function search_events($query = [], $start = 0, $qty = 0, $sort = 'event_date', $dir = 'ASC')
    {
        try {
            $sql = "
                SELECT *
                FROM ".$this->prefix."_events
                WHERE event_date > '".date('Y-m-d')."'
            ";

            $_arguments = [
                'title',
                'text'
            ];

            if (!empty($query['q'])) {
                $sql .= "
                    AND (".implode(" LIKE '%".$query['q']."%' OR ", $_arguments)."  LIKE '%".$query['q']."%')
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
                $data['owner_info'] = $this->get_items_info($data['owner']);
                $return[] = $this->filter($data, 1, 1);
            }

            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }





    public function get_deals($id = 0, $start = 0, $qty = 0, $sort = 'date', $dir = 'DESC')
    {
        $id = (int) $id;

        if (!empty($sort)) {
            $_ORDER = $sort.' '.$dir;
        }
        if (!empty($qty)) {
            $_LIMIT = [(int) $start, (int) $qty];
        }

        if (!empty($id)) {
            foreach ($this->sql_get('deals', '*', ['owner' => $id], $_ORDER, $_LIMIT) as $data) {
                $return[] = $this->filter($data, 1);
            }

            return $return;
        }

        return false;
    }

    public function get_deal($id = 0, $owner = 0)
    {
        $id = (int) $id;
        $owner = (int) $owner;

        if (!empty($id) && !empty($owner)) {
            return $this->filter($this->sql_get_one('deals', '*', ['id' => $id, 'owner' => $owner]), 1);
        }

        return false;
    }

    public function search_deals($query = [], $start = 0, $qty = 0, $sort = 'end_date', $dir = 'ASC')
    {
        try {
            $sql = "
                SELECT *
                FROM ".$this->prefix."_deals
                WHERE start_date < '".date('Y-m-d')."' 
                AND end_date > '".date('Y-m-d')."' 
            ";

            $_arguments = [
                'title',
                'text'
            ];

            if (!empty($query['q'])) {
                $sql .= "
                    AND (".implode(" LIKE '%".$query['q']."%' OR ", $_arguments)."  LIKE '%".$query['q']."%')
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
                $data['owner_info'] = $this->get_items_info($data['owner']);
                $return[] = $this->filter($data, 1, 1);
            }

            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

//    public function save_deal($data = [])
//    {
//        if (!empty($data)) {
//            $data = $this->filter($data, 1, 1);
//
//            # Begin Transaction
//            $this->begin_transaction();
//
//            if (!empty($data['id'])) {
//                $id = $data['id'];
//                $data['modified'] = date('Y-m-d H:i:s');
//            } else {
//                unset($data['id']); // prevent empty strings
//                $data['date'] = date('Y-m-d H:i:s');
//                $id = $this->sql_insert('deals', $data);
//            }
//
//            $data['active'] = (int) $data['active'];
//            $data['start_date'] = date('Y-m-d', strtotime($data['start_date']));
//            $data['end_date'] = date('Y-m-d', strtotime($data['end_date']));
//
//            $data['url'] = $this->gen_url($id.' '.$data['title'], 'members/deals');
//
//            unset($data['id']);
//
//            $this->sql_update('deals', $data, (int) $id);
//
//            # Commit Transaction
//            $this->commit();
//
//            $this->reload_cache_item($id);
//
//            return $id;
//        }
//
//        return false;
//    }
//
//    public function delete_deal($id = 0, $owner = 0)
//    {
//        $id = (int) $id;
//        $owner = (int) $owner;
//
//        if (!empty($id)) {
//            return $this->sql_delete('deals', ['id' => $id, 'owner' => $owner]);
//        }
//
//        return false;
//    }

    # MEMBERS HANDLER FUNCTIONS
    # =================================================================================================================================

    /**
    * Checks if user is logged in
    **/
    public function is_user()
    {
        return (empty($_SESSION['user_info']['hash'])) ? false : true;
    }

    /**
     * Check user payments
     * @return redirects user to payments area
     */
    public function is_paid()
    {
        if (!empty($this->settings['charge_membership']) && $this->is_user()) {
            if ((strtotime($_SESSION['user_info']['next_payment']) < time())) {
                Flash::set('error', _MEMBERS_PAYMENT_MEMBERSHIP_EXPIRED, 'members/payments');
            }
        }
    }

    /**
     * @param int $id
     * @param string $email
     * @return string
     */
    public function getActivationCode(int $id, string $email): string
    {
        return Helper::encrypt("activate:{$id}|{$email}");
    }

    /**
     * @param string $code
     * @return array
     * @throws Exception
     */
    public function parseActivationCode(string $code): array
    {
        $decoded = Helper::decrypt($code);

        if ($decoded === false) {
            throw new Exception('We could not authenticate you. Please try again or contact us.');
        }

        $decoded = str_replace('activate:', '', $decoded);

        return explode('|', $decoded);
    }

    /**
     * @param int $id
     * @param string $file
     * @return string
     */
    public function getFileModerationCode(int $id, string $file): string
    {
        return Helper::encrypt("moderate:{$id}|{$file}");
    }

    /**
     * @param string $code
     * @return array
     * @throws Exception
     */
    public function parseFileModerationCode(string $code): array
    {
        $decoded = Helper::decrypt($code);

        if ($decoded === false) {
            throw new Exception('Something is wrong with the link. Please try again.');
        }

        $decoded = str_replace('moderate:', '', $decoded);

        return explode('|', $decoded);
    }

    /**
     * @param int $id
     * @param string $file
     * @return string
     */
    public function getAccountModerationCode(int $id, string $email): string
    {
        return Helper::encrypt("moderate:{$id}|{$email}");
    }

    /**
     * @param string $code
     * @return array
     * @throws Exception
     */
    public function parseAccountModerationCode(string $code): array
    {
        $decoded = Helper::decrypt($code);

        if ($decoded === false) {
            throw new Exception('Something is wrong with the link. Please try again.');
        }

        $decoded = str_replace('moderate:', '', $decoded);

        return explode('|', $decoded);
    }

    /**
     * @param int $account_id
     * @param int $status
     * @return bool
     */
    public function setAccountApprovalStatus(int $account_id, int $status): bool
    {
        return $this->sql_update($this->table, ['approved' => $status, 'active' => 1], $account_id);
    }

    /**
     * @param array $file
     */
    public function downloadUserFile(array $file): void
    {
        $meta = json_decode($file['description']);
        $name = $meta->uuid.'.'.$meta->extension;
        $path = $_SERVER['DOCUMENT_ROOT'].'/uploads/get_listed/';

        $filename = $path.$name;

        File::download($filename, $file['file']);
    }

    /**
     * Sign in user
     * @param  string 	$email     	user email address
     * @param  string 	$password  	un-encrypted password
     * @param  bool 	$remember 	if user wants to be remembered
     * @return bool
     */
    public function sign_in($email = '', $password = '', $remember = null)
    {
        $data = $this->sql_get_one($this->table, '*, get_avatar(id) AS avatar, user_comments(id) AS comments', ['email' => $email]);

        if (empty($data)) {
            throw new Exception(_MEMBERS_LOGIN_PAGE_UNKNOWN_EMAIL, 1);
        }

        $_user_info = $this->load($data);

        if (empty($_user_info)) {
            throw new Exception(_MEMBERS_LOGIN_PAGE_USERORPASSWD_INCORRECT, 1);
        }
        if ((int) $_user_info['approved'] === 0) {
            throw new Exception(_MEMBERS_LOGIN_PAGE_APPROVAL_PENDING, 1);
        }
        if ((int) $_user_info['approved'] === -1) {
            throw new Exception(_MEMBERS_LOGIN_PAGE_APPROVAL_REJECTED, 1);
        }
        if ((int) $_user_info['active'] !== 1) {
            throw new Exception(_MEMBERS_LOGIN_PAGE_ACCOUNT_NOT_ACTIVE, 1);
        }

        // Compare Password
        if (!Encrypt::valPasswd($password, $_user_info['pwd'])) {
            throw new Exception(_MEMBERS_LOGIN_PAGE_USERORPASSWD_INCORRECT, 1);
        }

        $_SESSION['user_info'] = $_user_info;
        $_SESSION['user_info']['hash'] = Encrypt::encryptPasswd(time());
        $this->update_login($_user_info['id']);
    }

    /**
     * Auto sign in user if needed
     * @param  string 	$email    	cookie email address
     * @param  string 	$password 	cookie encrypted password
     * @return bool
     */
    public function auto_sign_in($email = '', $password = '')
    {
        $_user_info = $this->load($this->sql_get_one($this->table, '*, get_avatar(id) AS avatar, user_comments(id) AS comments', ['active' => 1, 'email' => $email, 'pwd' => $password]));

        if (count($_user_info) === 0) {
            $_SESSION['user_info'] = $_user_info;
            return true;
        }
        return false;
    }
    /**
     * Update user information
     * @return bool
     */
    public function update_session()
    {
        if (!empty($_SESSION['user_info']['id'])) {
            $this->reload_cache_item((int) $_SESSION['user_info']['id']);
            $_SESSION['user_info'] = $this->get_items_info((int) $_SESSION['user_info']['id']);
            $_SESSION['user_info']['hash'] = Encrypt::encryptPasswd(time());
        }

        return true;
    }
    /**
     * Update last login date
     * @param mixed $id
     */
    public function update_login($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $this->sql_update($this->table, ['last_login' => date('Y-m-d H:i:s')], $id);
        }
    }
    /**
     * Log out user
     */
    public function logout()
    {
        unset($_SESSION['user_info']);
    }
    /**
     * Send password on forgot password requests
     * @param  array  $data 		data needed to collect the information
     * @return bool
     */
    public function send_password(array $data = [])
    {
        if (!empty($data)) {
            $this->config = parent::get_config();

            $cc = substr(sha1(uniqid().$data['email']), 0, 16);
            $tt = substr(sha1(uniqid().$this->site_domain()), 0, 9);

            $this->sql_update($this->table, ['cc' => $cc, 'tt' => $tt], (int) $data['id']);
            $this->reload_cache_item($data['id']);

            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/emails/layout.password.recovery.email.phtml');
            $mssg = ob_get_clean();

            # Send Email to client
            $this->send_emails(_MEMBERS_EMAIL_LOST_PASSWORD_SUBJECT, \App\Router::mod_rewrite($mssg), '', '', $data['email'], $data['first_name'].' '.$data['last_name']);

            return true;
        }

        return false;
    }
    /**
     * Reset user's passwords
     * @param  array  $data 		data needed to collect the information
     * @param mixed $email
     * @param mixed $password
     * @return bool
     */
    public function reset_password($email = '', $password = '')
    {
        if (!empty($email) && !empty($password)) {
            $data = $this->get_user_by_email($email);

            # Update Password
            $new_password = Encrypt::encryptPasswd($password);
            $this->sql_update($this->table, ['pwd' => $new_password, 'cc' => '', 'tt' => ''], (int) $data['id']);
            $this->reload_cache_item($data['id']);

            return true;
        }

        return false;
    }

    /**
     * @param string $category
     * @param int $quantity
     * @return array
     */
    public function list_featured(string $main_category, int $quantity = 10)
    {
        $return = [];
        $where = [];
        $where['main_category'] = ucfirst($main_category);
        $where['featured'] = 1;
        $where['alive'] = 1;
        $where['grouped'] = 5;
        $where['active'] = 1;
        $order = "date DESC";
        $limit = [0, $quantity];

        try {
            $data = $this->sql_get('customers', '*', $where, $order, $limit);
            foreach ($data as $info) {
                $return[] = $this->load($info);
            }
            return $return ?? [];
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    /**
     * List users in database
     * @param  integer 	$start 			start from
     * @param  integer 	$qty   			quantity to be returned
     * @param  integer 	$active_only 	get only active rows
     * @param  integer 	$category       get only belonging to certain group
     * @param  string  	$sort  			sort by element
     * @param  string  	$dir   			direction to sort
     * @param mixed $main_category
     * @param mixed $location
     * @return array
     */
    // TODO: Shouldn't we filter by 'featured' flag?
    public function list_professional($start = 0, $qty = 0, $active_only = 0, $main_category = null, $category = 0, $location = '', $sort = 'date', $dir = 'DESC')
    {
        try {
            $return = [];
            $where = [];
            $where['alive'] = 0;
            $where['grouped'] = 5;

            if (!empty($active_only)) {
                $where['active'] = 1;
            }

            if (!empty($main_category)) {
                $where['main_category'] = $main_category;
            }
            if (!empty($category)) {
                $where['category'] = $category;
            }

            if (!empty($location)) {
                $where['city'] = $location;
            }

            if (!empty($sort)) {
                $order = $sort." ".$dir;
            }

            if (!empty($qty)) {
                $limit = [$start, $qty];
            }

            foreach ($this->sql_get($this->table, '*', $where, $order, $limit) as $info) {
                $return[] = $this->load($info);
            }
            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function set_availability()
    {
        $id = (int) $_SESSION['user_info']['id'];

        return $this->sql_update($this->table, ['available' => date('Y-m-d H:i:s', strtotime('+2 hours'))], $id);
    }
    /**
     * Get module's settings
     * @return array
     */
    private function get_settings()
    {
        if (file_exists($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/kernel/config/site.members.ini')) {
            $config = Kernel_Classes_Ini::read($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/kernel/config/site.members.ini');
            return $config['members'];
        }

        return false;
    }
    /**
     * Load and format user information
     * @param  array  $data data to be formatted
     * @return array
     */
    private function load($data = [])
    {
        try {
            if (!$data || count($data) == 0) {
                if (_DUMP_DEBUG) {
                    echo 'ERROR: No data to load';
                    echo "<pre>";
                    debug_print_backtrace(0, 10);
                    echo "</pre>";
                    die();
                }

                return $data;
            }

            // $return                              			= parent::cache()->get('_cache_' . $this->plugin . '_' . $data['id']);

            // if($return == null)
            // {
            $return = $this->filter($data, 1);
            $return['full_name'] = $data['first_name'].' '.$data['last_name'];
            $return['url'] = $this->plugin.'/'.str_replace($this->plugin.'/', '', $data['url']);
            $return['display_name'] = (!empty($data['company']) ? $data['company'] : $return['full_name']);
            $return['notes'] = $this->filter($data['notes']);
            $return['count_notes'] = $this->count_notes($data['notes']);
            $return['group'] = $this->get_group_info($data['grouped']);
            $return['avatar'] = $this->get_avatar($data['id']);
            $return['comments'] = $this->count_comments($data['id']);
            $return['extra'] = json_decode($data['extra'], true);

            //     parent::cache()->set('_cache_' . $this->plugin . '_' . $data['id'], $return);
            // }

            return $return;
        } catch (Exception $e) {
            if (_DUMP_DEBUG) {
                echo 'ERROR: ' . __METHOD__ . ': ' . $e->getMessage();
                echo "<pre>";
                debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 5);
                echo "</pre>";
                die();
            }

            return [];
        }
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
            return '/uploads/avatar/silhouette.jpg';
        }

        return "/uploads/avatar/".md5((string) $item['id']).'/'.$item['avatar'];
    }
}
