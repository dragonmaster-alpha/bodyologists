<?php
use App\Upload as Upload;

/**
 * @author
 * Web Design Enterprise
 * Phone: 786.234.6361
 * Website: www.webdesignenterprise.com
 * E-mail: info@webdesignenterprise.com
 *
 * @copyright
 * This work is licensed under the Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 United States License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/3.0/legalcode
 *
 * Be aware, violating this license agreement could result in the prosecution and punishment of the infractor.
 *
 * © 2002- 2010 Web Design Enterprise Corp. All rights reserved.
 */

require_once('../mainfile.php');

$upload = new Upload;

switch ($_POST['op']) {
    case "photos":

        if (!empty($_POST['folder_name'])) {
            $media_data = $helper->filter($_POST, 1, 1);
            $media_data['field_name'] = 'Filedata';
            $media_data['active'] = (!empty($_POST['active']) ? 1                     : 0);
            $media_data['parent'] = (!empty($_POST['id'])     ? (int) $_POST['id']    : 0);
            
            $data = $upload->start_upload($media_data);

            if (!empty($data) && $data['belongs'] === 'avatar') {
                $_SESSION['user_info']['avatar'] = $data['file'];
            }

            $helper->json_response($data);
        }
    
    break;

    case "single":

        if (!empty($_POST['folder_name'])) {
            $media_data = $helper->filter($_POST, 1, 1);
            $media_data['field_name'] = 'Filedata';
            $media_data['active'] = (!empty($_POST['active']) ? 1                     : 0);
            $media_data['parent'] = (!empty($_POST['id'])     ? $_POST['id']          : 0);
            $media_data['single'] = true;

            $data = $upload->start_upload($media_data);

            if (!empty($data) && $data['belongs'] === 'avatar') {
                $_SESSION['user_info']['avatar'] = $data['file'];
            }

            $helper->json_response($data);
        }

    break;

    case "youtube":

        if (!empty($_POST['folder_name'])) {
            $media_data = $helper->filter($_POST, 1, 1);
            $media_data['field_name'] = 'Filedata';
            $media_data['active'] = (!empty($_POST['active']) ? 1                     : 0);
            $media_data['parent'] = (!empty($_POST['id'])     ? (int) $_POST['id']    : 0);
            $media_data['media'] = 'youtube';

            $data = $upload->start_upload($media_data);
            $helper->json_response($data);
        }

    break;

    case "videos":

        if (!empty($_POST['folder_name'])) {
            $media_data = $helper->filter($_POST, 1, 1);
            $media_data['field_name'] = 'Filedata';
            $media_data['active'] = (!empty($_POST['active']) ? 1                     : 0);
            $media_data['parent'] = (!empty($_POST['id'])     ? (int) $_POST['id']    : 0);
            $media_data['media'] = 'video';

            $data = $upload->start_upload($media_data);
            $helper->json_response($data);
        }

    break;

    case "audio":

        if (!empty($_POST['folder_name'])) {
            $media_data = $helper->filter($_POST, 1, 1);
            $media_data['field_name'] = 'Filedata';
            $media_data['active'] = (!empty($_POST['active']) ? 1                     : 0);
            $media_data['parent'] = (!empty($_POST['id'])     ? (int) $_POST['id']    : 0);
            $media_data['media'] = 'audio';

            $data = $upload->start_upload($media_data);
            $helper->json_response($data);
        }

    break;
    
    case "customers_files":
    
        if (!empty($_POST['folder_name'])) {
            $media_data = $helper->filter($_POST, 1, 1);
            $media_data['field_name'] = 'Filedata';
            $media_data['active'] = (!empty($_POST['active']) ? 1                     : 0);
            $media_data['parent'] = (!empty($_POST['id'])     ? (int) $_POST['id']    : 0);
            $media_data['media'] = 'document';

            $data = $upload->start_upload($media_data);
            $helper->json_response($data);
        }
        
    break;

    case "documents":
    
        if (!empty($_POST['folder_name'])) {
            $media_data = $helper->filter($_POST, 1, 1);
            $media_data['field_name'] = 'Filedata';
            $media_data['active'] = (!empty($_POST['active']) ? 1                     : 0);
            $media_data['parent'] = (!empty($_POST['id'])     ? (int) $_POST['id']    : 0);
            $media_data['media'] = 'document';

            $data = $upload->start_upload($media_data);
            $helper->json_response($data);
        }
        
    break;
}
