<?php

namespace App;

use App\Security\Encrypt;
use DirectoryIterator;

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

class Admin extends Format
{
    public $security_info;
    public $admin_aid;
    public $admin_info = [];
    
    public function __construct()
    {
        $this->config = parent::get_config();

        // Check if it is an admin
        if (isset($_COOKIE['admin']) && !$this->is_admin()) {
            $u = unserialize(base64_decode($_COOKIE['admin']));
            $this->auto_sign_in($u['aid'], $u['password']);
        }
    }

    /**
     * Destroy class info to facilitate logout
     */
    public function __destroy()
    {
        settype($this, 'null');
    }
    /**
     * check if it is an admin
     * @return boolean
     */
    public function is_admin()
    {
        return empty($this->admin_aid) ? false : true;
    }
    /**
     * check admin capabilities
     * @return boolean
     */
    public function is_global_admin()
    {
        if ($this->is_admin() && $this->admin_info['radminsuper'] == '1') {
            return true;
        }
        return false;
    }

    public function get_items_info($aid)
    {
        return $this->filter($this->sql_get_one('authors', '*', ['aid' => $aid]), 1);
    }
    /**
     * sign in administrator
     * @param  string $aid
     * @param  string $password
     * @return boolean
     */
    public function sign_in($aid, $password)
    {
        $this->admin_info = $this->sql_get_one('authors', '', ['active' => 1, 'aid' => $aid]);
        
        // Compare Password
        $password_match = Encrypt::valPasswd($password, $this->admin_info['pwd']);
        if ($password_match) {
            // Set user cookie
            $this->set_admin_cookie($this->admin_info['aid'], $this->admin_info['pwd']);

            // Set object $this->user_id
            $this->admin_aid = $this->admin_info['aid'];

            // Update admin last login
            $this->sql_update('authors', ['last_login' => date('Y-m-d H:i:s')], ['aid' => $this->admin_info['aid']]);
            return true;
        }

        return false;
    }
    /**
     * auto sign in admin
     * @param  string $aid
     * @param  string $password
     * @return boolean
     */
    public function auto_sign_in($aid, $password)
    {
        try {
            $this->admin_info = $this->sql_get_one('authors', '*', ['active' => 1, 'aid' => $aid, 'pwd' => $password]);

            if (!empty($this->admin_info['aid'])) {
                $this->admin_aid = $this->admin_info['aid'];
                return true;
            }
            
            $this->record_log('', '[HACK ATTEMPT] Possible XSS hack attempt COOKIE/SESSION Mal-formatted');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    /**
     * set admin cookie
     * @param string $aid
     * @param string $password
     */
    public function set_admin_cookie($aid, $password)
    {
        setcookie('admin', base64_encode(serialize(['aid' => $aid, 'password' => $password])), time() + 43200, _SITE_PATH, $this->site_domain(), false, true);
    }
    
    /**
     * Get admin from an email address
     * @param  string $email email
     * @return array        admin info
     */
    public function get_admin_from_email($email = '')
    {
        $data = $this->sql_get_one('authors', 'aid', ['email' => $email]);
        return $this->get_items_info($data['aid']);
    }

    /**
     * check admin access rights
     * @param  string $plugin
     * @return boolean
     */
    public function admin_access($plugin = '')
    {
        if ($this->is_admin()) {
            if ($this->is_global_admin()) {
                return true;
            }
             
            if ($this->sql_count('plugins', "name = '".$plugin."' AND admins LIKE '%".$this->admin_aid.",%'") == 1) {
                return true;
            }
        }

        $this->record_log('', '[user: '.$this->admin_aid.'] Permission denied to access '.$plugin);
        return false;
    }

    public function get_plugin_admin_access()
    {
        if ($this->is_admin()) {
            return $this->sql_get_one('plugins', 'name, groups', "active = '1' AND admins LIKE '%".$this->admin_aid.",%'");
        }
    }
    
    /**
     * Check security
     */
    public function security_check()
    {
        if ($this->sql_count('banned', ['ip' => $_SERVER['REMOTE_ADDR']]) > 0) {
            header("Location: secure.php");
            exit;
        }
    }

    /**
     * logout admin
     */
    public function logout()
    {
        $this->record_log('', 'Administrator successfully logged out.');
        $this->set_admin_cookie('0', '0');
        $this->admin_info = '';
        $this->admin_aid = '';
        $this->__destroy();
    }

    # Load plugins to admin area
    public function load_plugins()
    {
        if (!isset($_SESSION['loaded_plugins'])) {
            $plugins_folder = $_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins';
            $this->refresh_side_menu = false;

            foreach (new DirectoryIterator($plugins_folder) as $plugin) {
                if (is_dir($plugins_folder.'/'.$plugin->getFilename()) && !$plugin->isDot()) {
                    $plugin_name = $plugin->getFilename();
                    $this->refresh_side_menu = true;

                    if ($this->sql_count('plugins', ['name' => $plugin_name]) == 0) {
                        $plugin_info = json_decode(file_get_contents($plugins_folder.'/'.$plugin_name.'/plugin-info.json'), true);

                        $this->sql_insert('plugins', [
                            'name' => $plugin_name,
                            'groups' => $plugin_info['group'],
                            'description' => $plugin_info['description'],
                            'version' => $plugin_info['version'],
                            'active' => 1
                        ]);
                    }
                }
            }

            $_SESSION['loaded_plugins'] = 1;
        }
    }
    
    /**
     * Load Administration menu
     * @param  string $open_group  Group opened
     * @param  string $open_plugin Plugin Opened
     * @return string              ul list formatted menu
     */
    public function load_admin_menu($open_group = '', $open_plugin = '')
    {
        $admin_menu = '
        <ul>
        ';

        # Get Plugin Groups
        $sql = "SELECT DISTINCT(`groups`) FROM {$this->prefix}_plugins WHERE `groups` != ''";

        # If is not a global admin get groups where admin have acess only
        if (!$this->is_global_admin()) {
            $sql .= " AND `admins` LIKE '%".$this->admin_info['aid'].",%'";
        }

        $sql .= ' ORDER BY `groups` ASC'; //BEWARE OF THE LEADING SPACE

        foreach ($this->sql_fetchrow($sql) as $plugin_group) {
            $admin_menu .= '
            <li';
            
            if ($open_group == $plugin_group['groups']) {
                $admin_menu .= ' class="current"';
            }

            $admin_menu .= '>
                <a href="#'.$this->link($plugin_group['groups']).'" class="'.$plugin_group['groups'].'">
                    '.$plugin_group['groups'].'
                </a>
                <span class="arrow"></span>
                <ul id="'.$this->link($plugin_group['groups']).'">
            ';
    
            # Collect plugins within group
            foreach ($this->sql_get('plugins', 'name', ['groups' => $plugin_group['groups']]) as $plugin_info) {
                if ($this->admin_access($plugin_info['name'])) {
                    if (file_exists('../plugins/'.$plugin_info['name'].'/admin/index.php') && file_exists('../plugins/'.$plugin_info['name'].'/admin/admin.links.php')) {
                        include('../plugins/'.$plugin_info['name'].'/admin/admin.links.php');
                    }
                }
            }
            $admin_menu .= '
                </ul>
            </li>
            ';
        }
        $admin_menu .= '
		</ul>
		';

        return $admin_menu;
    }
    
    /**
     * records admin logs
     * @param  string $title
     * @param  string $action
     */
    public function record_log(string $title = '', string $action = ''): void
    {
        if (!empty($title)) {
            $title .= ': ';
        }
        require_once ('class.log.php');
        new Log('[user: '.$this->admin_info['aid'].'] '.$title.$action);
    }

    /**
     * Remove directory in recursive mode
     * @param  string $dir
     */
    public function remove_dir($dir)
    {
        if ($this->is_admin()) {
            if (is_dir($dir)) {
                $dir_data = scandir($dir);
                foreach ($dir_data as $dir_info) {
                    if ($dir_info != '.' && $dir_info != '..') {
                        if (filetype($dir.'/'.$dir_info) == "dir") {
                            $this->remove_dir($dir.'/'.$dir_info);
                        } else {
                            unlink($dir.'/'.$dir_info);
                        }
                    }
                }

                reset($dir_data);
                rmdir($dir);

                $this->record_log('Directory deletion', '[user: '.$this->admin_aid.'] Deletion request for '.$dir.' successfully executed');
            }

            $this->record_log('Deletion failed', '[user: '.$this->admin_aid.'] Deletion request for '.$dir.' failed, no such file or directory');
        } else {
            $this->record_log('', '[HACK ATTEMPT] Permission denied to delete directory '.$dir);
        }
    }
}
