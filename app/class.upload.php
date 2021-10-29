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

class Upload extends Format
{
    public $_data = [];
    public $_folder = '';
    public $_owner = 0;
    public $_parent = 0;
    public $_album = 0;
    public $_bid = '';
    public $_images_id = '';
    public $_field_name = '';
    public $_is_active = '';
    public $_next_id = '';
    public $_file_dir = '';
    public $file_name = '';
    public $tmp_name = '';
    public $path_info = '';
    public $extension = '';
    public $new_file_name = '';
    public $new_name = '';
    public $mime_type = '';
    public $_media = '';
    public $_media_data = [];
    public $_single = false;

    public function __construct()
    {
    }
    public function start_upload(array $data = [])
    {
        if (!empty($data)) {
            $this->config = parent::get_config();
            $this->_folder = $data['folder_name'];
            $this->_owner = $data['owner'];
            $this->_parent = $data['parent'];
            $this->_bid = md5((string) $this->_parent);

            $this->_is_active = (!empty($data['active'])) ? 1 : 0;
            $this->_field_name = $data['field_name'];
            $this->_media = $data['media'];
            $this->_images_id = $this->sql_get_one('media', ['imageId'], ['belongs' => $this->_folder, 'bid' => $this->_bid], 'imageId DESC');
            $this->_next_id = $this->_images_id['imageId'] + 1;
            $this->_single = $data['single'];
            $this->_file_dir = $_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/uploads/'.$this->_folder.'/'.$this->_bid;

            $this->_media_data['thumb_width'] = (!empty($data['thumb_width']))     ? (int) $data['thumb_width']     : (int) $this->config['thumb_width'];
            $this->_media_data['thumb_height'] = (!empty($data['thumb_height']))    ? (int) $data['thumb_height']    : (int) $this->config['thumb_height'];
            $this->_media_data['image_width'] = (!empty($data['image_width']))     ? (int) $data['image_width']     : (int) $this->config['image_width'];
            $this->_media_data['image_height'] = (!empty($data['image_height']))    ? (int) $data['image_height']    : (int) $this->config['image_height'];
            $this->_media_data['video_width'] = $this->config['video_width'];
            $this->_media_data['video_height'] = $this->config['video_height'];

            if (!empty($data['extras'])) {
                $this->_media_data['extras'] = $data['extras'];
            }

            if (!is_dir($this->_file_dir)) {
                mkdir($this->_file_dir, 0777, true);
                @shell_exec("chmod -R 777 $this->_file_dir");
            }
            
            if ($this->_media == 'youtube') {
                $this->_media_data['extras'] = json_decode(file_get_contents('https://www.youtube.com/oembed?url='.$data['video_url'].'&format=json'), 1);
                $this->_media_data['extras']['video_url'] = $data['video_url'];

                if (preg_match("/(youtube.com)/", $data['video_url'])) {
                    $this->_media_data['extras']['video_id'] = explode("v=", preg_replace("/(&)+(.*)/", null, $data['video_url']))[1];
                } elseif (preg_match("/(youtu.be)/", $data['video_url'])) {
                    $this->_media_data['extras']['video_id'] = explode("/", preg_replace("/(&)+(.*)/", null, $data['video_url']))[3];
                }
            } elseif ($this->_media == 'magazine') {
                $this->_file_dir = $this->_file_dir.'/'.str_replace('.pdf', '', $_FILES[$this->_field_name]['name']);

                if (!is_dir($this->_file_dir)) {
                    mkdir($this->_file_dir, 0777, true);
                    @shell_exec("chmod -R 777 $this->_file_dir");
                }

                if (!isset($_FILES[$this->_field_name]) || !is_uploaded_file($_FILES[$this->_field_name]["tmp_name"]) || $_FILES[$this->_field_name]["error"] != 0) {
                    header("HTTP/1.1 500 Internal Server Error");
                    echo "invalid upload";
                    exit(0);
                }
                 
                $this->file_name = $_FILES[$this->_field_name]['name'];
                $this->tmp_name = $_FILES[$this->_field_name]['tmp_name'];
                $this->path_info = pathinfo($this->file_name);
                $this->extension = strtolower($this->path_info['extension']);
                $this->new_file_name = str_replace(' ', '_', $this->path_info['filename']);
                $this->new_name = $this->new_file_name.'.pdf';

                while (move_uploaded_file($this->tmp_name, $this->_file_dir.'/'.$this->new_name)) {
                    @chmod($this->_file_dir.'/'.$this->new_name, 0777);
                }
            } else {
                if (!isset($_FILES[$this->_field_name]) ||
                    !is_uploaded_file($_FILES[$this->_field_name]["tmp_name"]) ||
                    $_FILES[$this->_field_name]["error"] != 0)
                {
                    header("HTTP/1.1 500 Internal Server Error");
                    echo "INVALID UPLOAD";
                    exit(0);
                }
                 
                $this->file_name = $_FILES[$this->_field_name]['name'];
                $this->tmp_name = $_FILES[$this->_field_name]['tmp_name'];
                $this->path_info = pathinfo($this->file_name);
                $this->extension = strtolower($this->path_info['extension']);
                $this->new_file_name = $this->link($this->path_info['filename']).'-'.time();
                $this->new_name = $this->new_file_name.'.'.$this->extension;
                    
                while (move_uploaded_file($this->tmp_name, $this->_file_dir.'/'.$this->new_name)) {
                    @chmod($this->_file_dir.'/'.$this->new_name, 0777);
                }
            }
            
            if (!empty($data['method'])) {
                $this->media = 'Image';
                return $this->editor();
            }
            
            if ($this->_media == 'video') {
                $this->media = 'Videos';
                return $this->videos();
            } elseif ($this->_media == 'youtube') {
                $this->media = 'youtube';
                return $this->youtube();
            } elseif ($this->_media == 'audio') {
                $this->media = 'Audio';
                return $this->audio();
            } elseif ($this->_media == 'document') {
                $this->media = 'Document';
                return $this->documents();
            } elseif ($this->_media == 'magazine') {
                $this->media = 'Magazine';
                return $this->magazines();
            }
                
            $this->media = 'Image';
            return $this->images();
        }
        
        return false;
    }

    public function images()
    {
        if (!isset($_REQUEST['no_resize'])) {
            shell_exec("convert ".$this->_file_dir."/".$this->new_name." -format \"%[EXIF:This is the title] %[EXIF:this is  a test]\n\" -auto-orient -quality 90 -filter Lanczos -interlace Plane -strip -resize ".$this->_media_data['image_width']."x ".$this->_file_dir."/".$this->new_name);
            shell_exec("convert ".$this->_file_dir."/".$this->new_name." -auto-orient -quality 90 -filter Lanczos -interlace Plane -strip -resize ".$this->_media_data['thumb_width']."x ".$this->_file_dir."/thumb-".$this->new_name);
            shell_exec("convert ".$this->_file_dir."/".$this->new_name." -auto-orient -quality 90 -filter Lanczos -interlace Plane -strip -resize 60x ".$this->_file_dir."/small-".$this->new_name);

            # Chmod to images
            if (!@chmod($this->_file_dir."/small-".$this->new_name, 0777)) {
                @shell_exec("chmod 777 ".$this->_file_dir."/small-".$this->new_name);
            }
            if (!@chmod($this->_file_dir."/thumb-".$this->new_name, 0777)) {
                @shell_exec("chmod 777 ".$this->_file_dir."/thumb-".$this->new_name);
            }
        }

        $_insert = [
            'belongs' => $this->_folder,
            'owner' => $this->_owner,
            'parent' => $this->_parent,
            'bid' => $this->_bid,
            'media' => $this->media,
            'imageId' => $this->_next_id,
            'image' => $this->new_name,
            'meta_info' => '',//json_encode($meta_info),
            'active' => $this->_is_active,
            'date' => date('Y-m-d H:i:s')
        ];
        
        $id = $this->sql_insert('media', $_insert);

        if ($this->_single) {
            $this->sql_delete('media', "belongs = '".$_insert['belongs']."' AND bid = '".$_insert['bid']."' AND media = 'image' AND id != '".$id."'");
        }

        list($width, $height) = getimagesize($this->_file_dir."/".$this->new_name);

        $_array = [
            'id' => $id,
            'belongs' => $this->_folder,
            'owner' => $this->_owner,
            'bid' => $this->_bid,
            'media' => $this->media,
            'meta' => '',//$meta_info,
            'size' => [$width, $height],
            'url' => _SITE_PATH.'/uploads/'.$this->_folder.'/'.$this->_bid.'/'.$this->new_name,
            'file' => $this->new_name
        ];

        return $_array;
    }

    public function youtube()
    {
        $_insert = [
            'belongs' => $this->_folder,
            'owner' => $this->_owner,
            'parent' => $this->_parent,
            'bid' => $this->_bid,
            'media' => $this->media,
            'imageId' => $this->_next_id,
            'image' => $this->_media_data['extras']['thumbnail_url'],
            'meta_info' => '',// json_encode($this->_media_data['extras']),
            'title' => $this->_media_data['extras']['title'],
            'text' => $this->_media_data['extras']['title'],
            'link' => $this->_media_data['extras']['video_url'],
            'active' => $this->_is_active,
            'date' => date('Y-m-d H:i:s'),
        ];
        
        $id = $this->sql_insert('media', $_insert);

        $_array = [
            'id' => $id,
            'title' => $this->_media_data['extras']['title'],
            'belongs' => $this->_folder,
            'owner' => $this->_owner,
            'bid' => $this->_bid,
            'media' => $this->media,
            'meta' => '',//$this->_media_data['extras'],
            'size' => [$this->_media_data['extras']['width'], $this->_media_data['extras']['height']],
            'url' => $this->_media_data['extras']['thumbnail_url'],
            'file' => $this->_media_data['extras']['thumbnail_url']
        ];

        return $_array;
    }

    public function videos()
    {
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/kernel/classes/media/class.ffprobe.php');

        $video_info = new ffprobe($this->_file_dir."/".$this->new_name);

        $meta_info = [
            'codec_name' => $video_info->streams[1]->codec_long_name,
            'codec_long_name' => $video_info->streams[1]->codec_long_name,
            'codec_type' => $video_info->streams[1]->codec_type,
            'width' => $video_info->streams[1]->width,
            'height' => $video_info->streams[1]->height,
            'aspect_ratio' => $video_info->streams[1]->display_aspect_ratio,
            'lang' => $video_info->streams[1]->tags->language,
            'duration' => $video_info->format->duration,
            'size' => $video_info->format->size
        ];

        shell_exec(escapeshellcmd("/usr/local/bin/ffmpeg -i ".$this->_file_dir."/".$this->new_name." -r 1 -vframes 1 -ss 00:00:3 -t 00:00:03 ".$this->_file_dir."/".$this->new_file_name.".jpg"));

        # Create thumbnails
        shell_exec("convert ".$this->_file_dir."/".$this->new_file_name.".jpg -auto-orient -quality 90 -filter Lanczos -interlace Plane -strip -resize ".$this->_media_data['image_width']."x ".$this->_file_dir."/".$this->new_file_name.".jpg");
        shell_exec("convert ".$this->_file_dir."/".$this->new_file_name.".jpg -auto-orient -quality 90 -filter Lanczos -interlace Plane -strip -resize ".$this->_media_data['thumb_width']."x ".$this->_file_dir."/thumb-".$this->new_file_name.".jpg");
        shell_exec("convert ".$this->_file_dir."/".$this->new_file_name.".jpg -auto-orient -quality 90 -filter Lanczos -interlace Plane -strip -resize 60x ".$this->_file_dir."/small-".$this->new_file_name.".jpg");

        # Create OGG file
        shell_exec('/usr/local/bin/ffmpeg -y -i '.$this->_file_dir.'/'.$this->new_name.' -b 1500k -vcodec libtheora -acodec libvorbis -ab 160000 -g 30 -s '.$this->_media_data['video_width'].'x'.$this->_media_data['video_height'].' '.$this->_file_dir.'/'.$this->new_file_name.'.ogg >/dev/null 2>/dev/null &');
        
        # Create MP4 file if needed
        if ($this->extension != 'mp4') {
            shell_exec('/usr/local/bin/ffmpeg -y -i '.$this->_file_dir.'/'.$this->new_name.' -b 1500k -g 30 -s '.$this->_media_data['video_width'].'x'.$this->_media_data['video_height'].' '.$this->_file_dir.'/'.$this->new_file_name.'.mp4 >/dev/null 2>/dev/null &');
            //unlink($this->_file_dir . "/" . $this->new_name);
        }

        $_insert = [
            'belongs' => $this->_folder,
            'owner' => $this->_owner,
            'parent' => $this->_parent,
            'bid' => $this->_bid,
            'media' => $this->media,
            'imageId' => $this->_next_id,
            'image' => $this->new_file_name,
            'meta_info' => '',//json_encode($meta_info),
            'active' => $this->_is_active,
            'date' => date('Y-m-d H:i:s')
        ];

        $id = $this->sql_insert('media', $_insert);

        $_array = [
            'id' => $id,
            'belongs' => $this->_folder,
            'owner' => $this->_owner,
            'bid' => $this->_bid,
            'media' => $this->media,
            'meta' => '',//$meta_info,
            'file' => $this->new_file_name.'.jpg'
        ];

        return $_array;
    }

    public function audio()
    {
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/kernel/classes/media/class.mp3.php');

        $mp3 = new mp3;
        $meta_info = $mp3->get_meta_info($this->_file_dir."/".$this->new_name);

        $_insert = [
            'belongs' => $this->_folder,
            'owner' => $this->_owner,
            'parent' => $this->_parent,
            'bid' => $this->_bid,
            'media' => $this->media,
            'imageId' => $this->_next_id,
            'image' => $this->new_name,
            'meta_info' => json_encode($meta_info),
            'active' => $this->_is_active,
            'date' => date('Y-m-d H:i:s')
        ];

        $id = $this->sql_insert('media', $_insert);

        $_array = [
            'id' => $id,
            'belongs' => $this->_folder,
            'owner' => $this->_owner,
            'bid' => $this->_bid,
            'media' => $this->media,
            'meta' => $meta_info,
            'file' => $this->new_name
        ];

        return $_array;
    }

    public function documents()
    {
        $meta_info = $this->extension;

        $_insert = [
            'belongs' => $this->_folder,
            'owner' => $this->_owner,
            'parent' => $this->_parent,
            'bid' => $this->_bid,
            'media' => $this->media,
            'imageId' => $this->_next_id,
            'image' => $this->new_name,
            'meta_info' => json_encode($meta_info),
            'active' => $this->_is_active,
            'date' => date('Y-m-d H:i:s')
        ];

        $id = $this->sql_insert('media', $_insert);

        $_array = [
            'id' => $id,
            'belongs' => $this->_folder,
            'owner' => $this->_owner,
            'bid' => $this->_bid,
            'media' => $this->media,
            'meta' => $meta_info,
            'file' => $this->new_name
        ];

        return $_array;
    }

    public function magazines()
    {
        $meta_info = $this->extension;
        $img_name = str_replace('.pdf', '', $this->new_name);

        //shell_exec("convert -density 300 " . $this->_file_dir . "/" . $this->new_name . "[0]  -scale 825x1125 " . dirname($this->_file_dir) . "/" . $img_name . ".jpg");
        shell_exec("convert -density 300 ".$this->_file_dir."/".$this->new_name."  -scale 825x1125 ".$this->_file_dir."/".$img_name."-%d.jpg");

        $_array = [
            'id' => $id,
            'belongs' => $this->_folder,
            'bid' => $this->_bid,
            'media' => $this->media,
            'meta' => $meta_info,
            'file' => $img_name.'/'.$img_name.'-0.jpg'
        ];

        return $_array;
    }

    public function get_photo_location($photo)
    {
        return false;
    }

    public function getGps($exifCoord, $hemi)
    {
        return false;
    }

    public function delete($belongs = '', $bid = '', $parent = 0, $media = '')
    {
        if (!empty($belongs) && !empty($bid)) {
            $bid = md5((string) $bid);

            foreach ($this->sql_get('media', 'image', ['belongs' => $belongs, 'bid' => $bid, 'parent' => $parent, 'media' => $media]) as $data) {
                $this->sql_delete('media', ['belongs' => $belongs,'bid' => $bid,'parent' => $parent,'media' => $media,'image' => $data['image']]);
                @unlink($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/uploads/'.$belongs.'/'.$bid.'/'.$data['image']);
            }

            return true;
        }

        return false;
    }

    public function editor()
    {
        list($width, $height) = getimagesize($this->_file_dir.'/'.$this->new_name);
        return ['url' => _SITE_PATH.'/uploads/'.$this->_folder.'/'.$this->_bid.'/'.$this->new_name, 'size' => [$width, $height]];
    }
}
