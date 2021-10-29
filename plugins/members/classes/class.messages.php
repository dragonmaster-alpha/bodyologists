<?php

namespace Plugins\Members\Classes;

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

class Messages extends Format
{
    private $plugin;

    public function __construct()
    {
        $this->plugin = 'members';
        $this->config = parent::get_config();
    }

    public function list_mesages($start = 0, $qty = 10)
    {
        try {
            $owner = (int) $_SESSION['user_info']['id'];

            if (!empty($qty)) {
                $limit = [
                    $start,
                    $qty
                ];
            }

            $get_messages = $this->sql_get('customers_messages', '*', "parent = '0' AND (sent_to = '".$owner."' OR sent_from = '".$owner."')", 'date DESC', $limit);

            if (!empty($get_messages)) {
                foreach ($get_messages as $messages) {
                    $return[] = $this->load($messages);
                }
                return $return;
            }

            return false;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function count_mesages()
    {
        try {
            $owner = (int) $_SESSION['user_info']['id'];

            return $this->sql_count('customers_messages', "parent = '0' AND (sent_to = '".$owner."' OR sent_from = '".$owner."')");
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function list_sent($start = 0, $qty = 10)
    {
        try {
            $owner = (int) $_SESSION['user_info']['id'];

            if (!empty($qty)) {
                $limit = [
                    $start,
                    $qty
                ];
            }

            $get_messages = $this->sql_get('customers_messages', '*', ['sent_from' => $owner], 'date DESC', $limit);

            if (!empty($get_messages)) {
                foreach ($get_messages as $messages) {
                    $return[] = $this->load($messages);
                }
                return $return;
            }

            return false;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function count_sent()
    {
        try {
            $owner = (int) $_SESSION['user_info']['id'];
            
            return $this->sql_count('customers_messages', ['sent_from' => $owner]);
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function get_replies($id = 0)
    {
        try {
            if (!empty($id)) {
                foreach ($this->sql_get('customers_messages', '*', ['parent' => (int) $id], 'date DESC') as $data) {
                    $return[] = $data;
                }

                return $return;
            }

            return false;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function get_items_info($id = 0)
    {
        try {
            if (!empty($id)) {
                $return = $this->sql_get_one('customers_messages', '*', "id = '".(int) $id."' AND (sent_to = '".(int) $_SESSION['user_info']['id']."' OR sent_from = '".(int) $_SESSION['user_info']['id']."')");

                return $this->load($return, 1);
            }

            return false;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function send($data = [])
    {
        try {
            if (!$this->empty_array($data)) {
                $_fields_array = $this->get_fields_names('customers_messages');

                foreach ($data as $key => $value) {
                    if (!in_array($key, $_fields_array)) {
                        unset($data[$key]);
                    }
                }

                $this->sql_insert('customers_messages', $data);

                ob_start('replace_for_mod_rewrite');
                include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/messages/emails/layout.messages.email.phtml');
                $email_body = ob_get_contents();
                ob_end_clean();

                $this->send_emails('You have a new message on '.$this->config['sitename'], $email_body, '', '', $data['receiver_email'], $data['receiver_name']);

                return true;
            }
            
            return false;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function delete($id = 0)
    {
        try {
            if (!empty($id)) {
                if ($this->sql_delete('customers_messages', ['id' => $id, 'sent_to' => (int) $_SESSION['user_info']['id']])) {
                    $this->sql_delete('customers_messages', ['parent' => $id]);
                }

                return true;
            }
            return false;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    private function load($data = [])
    {
        try {
            if ($this->empty_array($data)) {
                throw new Exception("Error Processing Request, Data empty", 1);
            }

            $return = $this->filter($data, 1);
            $return['replies'] = $this->get_replies((int) $data['id']);

            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
}
