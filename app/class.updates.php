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

class Updates extends Format
{
    public $data = [];
    public $package_control;

    private $versions;
    private $control;

    public function __construct()
    {
    }

    public function check()
    {
        $this->package_control = $_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/kernel/config/package.json';
        $this->data = json_decode(file_get_contents($this->package_control), true);
        
        if (($this->data['updated'] + 2592000) < time()) {
            if (empty($this->data['url'])) {
                $this->data['url'] = 'http://wde.bz/versions/package.json';
            }
            
            if ($this->versions = file_get_contents($this->data['url'])) {
                $this->control = json_decode($this->versions);

                foreach ($this->control as $key => $value) {
                    if ($this->data[$key] !== $value->version) {
                        $_info = [
                            'file' => $key,
                            'actual_version' => $this->data[$key]['version'],
                            'new_version' => $value->version,
                            'priority' => $value->priority,
                            'text' => $value->text,
                            'url' => $value->url,
                            'zip' => array_reverse(explode('/', $value->url))[0],
                            'date' => date('Y-m-d H:i:s')
                        ];
                        
                        $check_update = $this->sql_get_one('updates', 'id', ['file' => $key]);

                        if (empty($check_update['id'])) {
                            $check_update['id'] = $this->sql_insert_empty('updates');
                        }
                                
                        $this->sql_update('updates', $_info, (int) $check_update['id']);
                    }
                }
            }

            $this->data['updated'] = time();
            file_put_contents($this->package_control, json_encode($this->data));
        }

        return false;
    }

    public function update($_json = '')
    {
        if ($json_file = file_get_contents($_json)) {
            $package = json_decode($json_file);

            echo '<pre>';
            print_r($package);
            echo '</pre>';
            die;

            $download = $this->download($package->download, $package->file);
            $install = $this->unzip(dirname(__FILE__).'/'.$download, false, false, true);

            if (!empty($install)) {
                foreach ($l as $sql) {
                    $frm->sql_query(file_get_contents($sql));
                    unlink($sql);
                }
            }

            unlink(dirname(__FILE__).'/'.$package->file);

            $this->version_control($package);
        }
    }

    public function unzip($src_file, $dest_dir = false, $create_zip_name_dir = true, $overwrite = true)
    {
        if ($zip = zip_open($src_file)) {
            if ($zip) {
                $sql_files = [];
                $splitter = ($create_zip_name_dir === true) ? "." : "/";
                
                if ($dest_dir === false) {
                    $dest_dir = substr($src_file, 0, strrpos($src_file, $splitter))."/";
                }

                $this->create_dirs($dest_dir);
                
                while ($zip_entry = zip_read($zip)) {
                    $pos_last_slash = strrpos(zip_entry_name($zip_entry), "/");

                    if ($pos_last_slash !== false) {
                        $this->create_dirs($dest_dir.substr(zip_entry_name($zip_entry), 0, $pos_last_slash + 1));
                    }
                    
                    if (zip_entry_open($zip, $zip_entry, 'r')) {
                        $file_name = $dest_dir.zip_entry_name($zip_entry);
                        
                        if ($overwrite === true || $overwrite === false && !is_file($file_name)) {
                            $fstream = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                            @file_put_contents($file_name, $fstream);

                            if (strpos($file_name, '.sql')) {
                                $sql_files[] = $file_name;
                            }
                            
                            chmod($file_name, 0777);
                        }

                        zip_entry_close($zip_entry);
                    }
                }

                zip_close($zip);
                return $sql_files;
            }
        }

        return true;
    }

    public function download($url = '', $file = '')
    {
        if (!empty($url) && !empty($file)) {
            $resource = fopen($file, 'w');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_FILE, $resource);

            $page = curl_exec($ch);

            if (!$page) {
                throw new Exception(curl_error($ch), 1);
            }

            curl_close($ch);

            return $file;
        }
        
        return false;
    }

    public function create_dirs($path)
    {
        if (!is_dir($path)) {
            $directory_path = '';
            $directories = explode('/', $path);
            array_pop($directories);

            foreach ($directories as $directory) {
                $directory_path .= $directory.'/';

                if (!is_dir($directory_path)) {
                    mkdir($directory_path);
                    chmod($directory_path, 0777);
                }
            }
        }
    }

    public function version_control(array $data = [])
    {
        if (!empty($data)) {
            if (!empty($data['name'])) {
                $_save_array['name'] = $data['version'];
            }

            $_save_array['checked_date'] = time();
            return true;
        }
        
        return false;
    }
}
