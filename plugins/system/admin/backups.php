<?php

use App\Flash as Flash;
use App\Security\Backups as Backups;

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

$plugin_name = basename(str_replace('admin', '', dirname(__FILE__)));

if ($administrator->admin_access($plugin_name)) {
    global $administrator, $frm, $helper, $meta, $settings;
    
    $backup_settings = $settings->get('backups');
    
    switch ($_REQUEST['op']) {
        default:
    
            $meta['title'] = 'Backups Summary';
            
            if (!is_dir($backup_settings['directory'])) {
                # Id the back ups directory does not exists we create it
                $backup_settings = [];
                $backup_settings['directory'] = md5((string) time());
                
                $settings->set('backups', $backup_settings);
                mkdir($backup_settings['directory'], 0777);
                chmod($backup_settings['directory'], 0777);
            
                // Create .htaccess within the folder to deny external access
                $ht_content = "deny from all";
                $ht_handler = fopen($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/'.$backup_settings['directory'].'/.htaccess', 'w');
                fwrite($ht_handler, $ht_content);
                fclose($ht_handler);
            }

            $files_list = glob($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/'.$backup_settings['directory'].'/*.sql');
            $count_files = count($files_list);

            if ($count_files > 0) {
                sort($files_list);
                foreach ($files_list as $key => $value) {
                    if (!empty($value)) {
                        $items_info[] = [
                            'name' => str_replace([$_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/'.$backup_settings['directory'].'/','.sql'], '', $value),
                            'date' => filectime($value),
                            'size' => filesize($value)
                        ];
                    }
                }
            }

            ob_start('ob_gzhandler');
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/plugins/system/admin/layout/layout.backups.phtml');
            $layout = ob_get_clean();
            require_once($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/admin/layout.php');
        
        break;
        
        case "new":
        
            try {
                $backup = new Backups(_DB_HOST, _DB_NAME, _DB_USER, _DB_PASSWORD, $backup_settings['directory'].'/db_'._DB_NAME.'-'.date('Y-m-d-H-i-s').'.sql');
                $backup->backup();
            
                $administrator->record_log("Backup created", "A new backup request has been successfully executed");
                Flash::set('success', 'A new backup request has been successfully executed', 'admin/system/backups');
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'back');
            }
        
        break;
            
        case "delete":
        
            try {
                if (empty($_GET['sql'])) {
                    throw new Exception('Please select at least one item to delete');
                }

                $backup_file = $frm->filter($_GET['sql'], 1, 1).'.sql';
                
                if (unlink($backup_settings['directory'].'/'.$backup_file)) {
                    $administrator->record_log("Backup file deleted", "Backup file $backup_file has been successfully deleted.");
                } else {
                    throw new Exception('Error deleting file '.$backup_file);
                }

                $helper->json_response(['answer' => 'done', 'message' => 'Item Successfully Deleted.']);
            } catch (Exception $e) {
                $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
            }
                
        break;
        
        case "download":
        
            try {
                if (empty($_GET['sql'])) {
                    throw new Exception('Please select at least one item to delete');
                }
                
                $backup_file = $frm->filter($_GET['sql'], 1, 1).'.sql';
            
                $filecontent = $backup_settings['directory'].'/'.$backup_file;
                $administrator->record_log("Backup file downloaded", "The file '$backup_file' has been successfully downloaded.");
                
                header("Content-type: text/sql");
                header("Content-disposition: attachment; filename=$backup_file");
                header("Pragma: no-cache");
                header("Expires: 0");
                readfile($filecontent);
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage(), 'back');
            }

        break;
    }
} else {
    header("Location: index.php");
    exit;
}
