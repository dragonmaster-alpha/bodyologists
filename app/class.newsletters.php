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

class Newsletters extends Format
{
    public function __construct()
    {
        $this->config = parent::get_config();
    }

    public function send_out()
    {
        if ($this->table_exists($this->prefix.'_newsletters_ready')) {
            foreach ($this->sql_get('newsletters_ready', '*', "active='1' AND send_to_email != ''", 'date ASC', [100]) as $data) {
                $data = $this->filter($data, 1);
                $data['body'] = $this->filter($data['body']);

                $this->send_emails($data['subject'], $data['body'], $data['send_from_email'], $data['send_from_name'], $data['send_to_email'], $data['send_to_name'], $data['reply_to_email'], $data['reply_to_name']);
                $this->delete($data['id']);
            }

            return true;
        }

        return false;
    }

    public function insert(array $data)
    {
        if (!empty($data)) {
            if (empty($data['send_from_email'])) {
                $data['send_from_email'] = 'no-reply@'.$this->site_domain();
            }
            if (empty($data['send_from_name'])) {
                $data['send_from_name'] = $this->config['sitename'];
            }
            if (empty($data['reply_to_email'])) {
                $data['reply_to_email'] = $data['send_from_email'];
            }
            if (empty($data['reply_to_name'])) {
                $data['reply_to_name'] = $data['send_from_name'];
            }
            if (empty($data['subject'])) {
                $data['subject'] = 'Important information from '.$this->config['sitename'];
            }

            $data['active'] = 1;
            $data['date'] = date('Y-m-d H:i:s');

            if (!empty($data['body']) && !empty($data['send_to_email'])) {
                return $this->sql_insert('newsletters_ready', $data);
            }
        }
        
        return false;
    }
    
    public function delete($id = '')
    {
        $id = (int) $id;

        if (!empty($id)) {
            return $this->sql_delete('newsletters_ready', $id);
        }
        
        return false;
    }

    public function get_items_stats($id = 0)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $data = $this->sql_get_one('newsletters_stats', '*', ['parent' => $id]);
            $data['links'] = unserialize($data['links']);
            $data['emails'] = unserialize($data['emails']);

            return $this->filter($data, 1);
        }
        
        return false;
    }

    public function count_clicks($id = 0, $link = '', $email = '')
    {
        $id = (int) $id;

        if (!empty($id)) {
            $data = urldecode(base64_decode($data));
            parse_str($data);

            $item_info = $this->get_items_info($id);
            $item_link = [];
            
            foreach ($item_info['links'] as $key => $val) {
                $item_link[$key] = ($key == $link) ? (int) $val + 1 : $val;
            }

            if (!in_array($email, $item_info['emails'])) {
                $item_info['emails'][] = $email;
                $item_info['unique_visits'] = $item_info['unique_visits'] + 1;
                $item_info['visits'] = $item_info['visits'] + 1;
            } else {
                $item_info['visits'] = $item_info['visits'] + 1;
            }

            return $this->sql_update('newsletters_stats', ['links' => serialize($item_link), 'emails' => serialize($item_info['emails']), 'unique_visits' => $item_info['unique_visits'], 'visits' => $item_info['visits']], ['parent' => $id]);
        }
        
        return false;
    }
}
