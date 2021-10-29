<?php

use App\Db\Database;
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

define('TEMP_DIR', 'uploads/temp');
define('AVATARS_DIR', 'uploads/avatar');
define('DEALS_DIR', 'uploads/deals');
define('EVENTS_DIR', 'uploads/events');

require_once('mainfile.php');

$upload = new Upload();

switch ($_REQUEST['op']) {

    case "cropImage":
        $allowedExts = ["gif", "jpeg", "jpg", "png"];
        $mime = mime_content_type($_FILES["img"]["tmp_name"]);
        $extension = str_replace('image/', '', $mime);
        $is_image = strpos($mime, 'image/') === 0;

        if (!$is_image || !in_array($extension, $allowedExts)) {
            echo json_encode([
                 "status" => 'error',
                 "message" => 'The image is not supported. Please use any of: '.implode(', ', array_filter($allowedExts)),
                 "debug" => ['mime' => $mime, 'extension' => $extension , 'image' => $is_image],
             ]);
            exit();
        }

        if(!is_dir(TEMP_DIR) && !mkdir(TEMP_DIR, 0777, true)){
            echo json_encode([
                "status" => 'error',
                "message" => 'Can`t upload File. Internal error'
            ]);
            exit();
        }

        if ($_FILES["img"]["error"] > 0) {
            echo json_encode([
                "status" => 'error',
                "message" => 'ERROR Return Code: '. $_FILES["img"]["error"],
            ]);
            exit();
        }

        $filename = $_FILES["img"]["tmp_name"];
        [$width, $height] = getimagesize($filename);
        $url = TEMP_DIR.'/'.basename($_FILES["img"]["tmp_name"]);

        if (!move_uploaded_file($filename, $url)) {
            echo json_encode([
                 "status" => 'error',
                 "message" => 'Could not store the file. Internal error'
             ]);
            exit();
        }

        $response = [
            "status" => 'success',
            "url" => $url,
            "width" => $width,
            "height" => $height
        ];
        print json_encode($response);
        break;

    case 'saveAvatar':
        try {
            $bid = md5((string) $_POST['id']);
            $name = basename($_POST['imgUrl']).'-'.mt_rand().'.jpg';
            $filename = AVATARS_DIR."/{$bid}/{$name}";

            $data = hydrateDataforMedia($_POST['owner'], $bid, $name, 'avatar');

            processAndStoreCroppedImage($_POST, $filename);
            $id = saveToDB('media', $data);

            $_SESSION['user_info']['avatar'] = $name;

            successResponse($filename, $id);

        } catch (RuntimeException $exception) {
            errorResponse($exception->getMessage());
        }
        break;

    case 'saveDealImage':
        try {
            // Avoid saving an orphan row when the Deal is being created
            $deal_id = !empty($_POST['id']) ? trim($_POST['id']) : "new-{$_POST['owner']}";
            $bid = md5($deal_id);
            $name = basename($_POST['imgUrl']).'-'.mt_rand().'.jpg';
            $filename = DEALS_DIR."/{$bid}/{$name}";

            $data = hydrateDataforMedia($_POST['owner'], $bid, $name, 'deals');

            processAndStoreCroppedImage($_POST, $filename);
            $id = saveToDB('media', $data);

            successResponse($filename, $id);

        } catch (RuntimeException $exception) {
            errorResponse($exception->getMessage());
        }
        break;

    case 'saveEventImage':
        try {
            // Avoid saving an orphan row when the Event is being created
            $event_id = !empty($_POST['id']) ? trim($_POST['id']) : "new-{$_POST['owner']}";
            $bid = md5($event_id);
            $name = basename($_POST['imgUrl']).'-'.mt_rand().'.jpg';
            $filename = EVENTS_DIR."/{$bid}/{$name}";

            $data = hydrateDataforMedia($_POST['owner'], $bid, $name, 'events');

            processAndStoreCroppedImage($_POST, $filename);
            $id = saveToDB('media', $data);

            successResponse($filename, $id);

        } catch (RuntimeException $exception) {
            errorResponse($exception->getMessage());
        }
        break;

    case "photos":

        if (!empty($_POST['folder_name'])) {
            $media_data = $helper->filter($_POST, 1, 1);
            $media_data['field_name'] = 'Filedata';
            $media_data['owner'] = $_SESSION['user_info']['id'];
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
            $media_data['owner'] = $_SESSION['user_info']['id'];
            $media_data['active'] = (!empty($_POST['active']) ? 1                     : 0);
            $media_data['parent'] = (!empty($_POST['id'])     ? $_POST['id']          : 0);
            $media_data['single'] = true;

            $data = $upload->start_upload($media_data);

            if (!empty($data) && $data['belongs'] === 'avatar') {
                $_SESSION['user_info']['avatar'] = $data['file'];
            }

            if (!empty($data) && $data['belongs'] === 'covers') {
                $_SESSION['user_info']['cover'] = $data['file'];
            }

            $helper->json_response($data);
        }

    break;

    case "youtube":

        if (!empty($_POST['folder_name'])) {
            $media_data = $helper->filter($_POST, 1, 1);
            $media_data['field_name'] = 'Filedata';
            $media_data['owner'] = $_SESSION['user_info']['id'];
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
            $media_data['owner'] = $_SESSION['user_info']['id'];
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
            $media_data['owner'] = $_SESSION['user_info']['id'];
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
            $media_data['owner'] = $_SESSION['user_info']['id'];
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
            $media_data['owner'] = $_SESSION['user_info']['id'];
            $media_data['active'] = (!empty($_POST['active']) ? 1                     : 0);
            $media_data['parent'] = (!empty($_POST['id'])     ? (int) $_POST['id']    : 0);
            $media_data['media'] = 'document';

            $data = $upload->start_upload($media_data);
            $helper->json_response($data);
        }
        
    break;
}

/**
 * Given
 * @param array $data
 * @param string $filename
 */
function processAndStoreCroppedImage(array $data, string $filename): void
{
    $imgUrl = $data['imgUrl'];

    if (!file_exists($imgUrl)) {
        throw new RuntimeException('File does not exists');
    }

    // original sizes
    $imgInitW = $data['imgInitW'];
    $imgInitH = $data['imgInitH'];
    // resized sizes
    $imgW = $data['imgW'];
    $imgH = $data['imgH'];
    // offsets
    $imgY1 = $data['imgY1'];
    $imgX1 = $data['imgX1'];
    // crop box
    $cropW = $data['cropW'];
    $cropH = $data['cropH'];
    // rotation angle
    $angle = $data['rotation'];

    $jpeg_quality = 90;

    $what = getimagesize($imgUrl);

    switch(strtolower($what['mime'])) {
        case 'image/png':
            $source_image = imagecreatefrompng($imgUrl);
            break;

        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($imgUrl);
            break;

        case 'image/gif':
            $source_image = imagecreatefromgif($imgUrl);
            break;

        default:
            throw new RuntimeException('The image is not supported or MIME type not found. Please use any of JPG / GIF / PNG.');
    }

    // Create dir if doesn't exists yet
    if (!mkdir(dirname($filename),0777, true) && !is_dir(dirname($filename))) {
        throw new RuntimeException('Could not save file to dir');
    }

    //Check write Access to Directory
    if(!is_writable(dirname($filename))){
        throw new RuntimeException('Can`t save cropped File');
    }

    // resize the original image to size of editor
    $resizedImage = imagecreatetruecolor($imgW, $imgH);
    imagecopyresampled($resizedImage, $source_image, 0, 0, 0, 0, $imgW, $imgH, $imgInitW, $imgInitH);

    // rotate the resized image
    $rotated_image = imagerotate($resizedImage, -$angle, 0);

    // find new width & height of rotated image
    $rotated_width = imagesx($rotated_image);
    $rotated_height = imagesy($rotated_image);

    // diff between rotated & original sizes
    $dx = $rotated_width - $imgW;
    $dy = $rotated_height - $imgH;

    // crop rotated image to fit into original resized rectangle
    $cropped_rotated_image = imagecreatetruecolor($imgW, $imgH);
    imagecolortransparent($cropped_rotated_image, imagecolorallocate($cropped_rotated_image, 0, 0, 0));
    imagecopyresampled($cropped_rotated_image, $rotated_image, 0, 0, $dx / 2, $dy / 2, $imgW, $imgH, $imgW, $imgH);

    // crop image into selected area
    $final_image = imagecreatetruecolor($cropW, $cropH);
    imagecolortransparent($final_image, imagecolorallocate($final_image, 0, 0, 0));
    imagecopyresampled($final_image, $cropped_rotated_image, 0, 0, $imgX1, $imgY1, $cropW, $cropH, $cropW, $cropH);

    // finally output jpg image
    imagejpeg($final_image, $filename, $jpeg_quality);
}

/**
 * Fills the row to insert in 'media' table
 *
 * @param $owner
 * @param $bid
 * @param $basename
 * @param $belongs
 * @return array
 */
function hydrateDataforMedia($owner, $bid, $basename, $belongs): array
{
    return [
        'parent' => 0,
        'owner' => $owner,
        'belongs' => $belongs,
        'bid' => $bid,
        'media' => 'Image',
        'imageId' => 1,
        'image' => $basename,
        'title' => '',
        'text' => '',
        'link' => '',
        'meta_info' => '',
        'active' => 0,
        'date' => date('Y-m-d H:i:s')
    ];
}

/**
 * @param $filename
 * @param $id
 */
function successResponse($filename, $id): void
{
    echo json_encode([
        "status" => 'success',
        "url" => $filename,
        "id" => $id,
    ]);
    exit();
}

/**
 * @param $message
 */
function errorResponse($message): void
{
    echo json_encode([
        "status" => 'error',
        "description" => $message
    ]);
    exit();
}

/**
 * @param string $table
 * @param array $data
 * @return int
 */
function saveToDB(string $table, array $data): int
{
    return getDatabaseConnection()->sql_insert($table, $data);
}

/**
 * @return Database
 */
function getDatabaseConnection(): Database
{
    $connection = new Database('mysql:dbname='._DB_NAME.';host='._DB_HOST, _DB_USER, _DB_PASSWORD, []);
    $connection->set_attribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->set_attribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
    $connection->set_attribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET CHARACTER SET utf8');

    return $connection;
}

