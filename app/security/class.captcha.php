<?php

namespace App\Security;

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

class Captcha
{
    public $lib_dir = '../images/validator/';
    public $backgrounds = ['01.gif'];
    public $fonts = ['solmetra1.ttf', 'solmetra2.ttf', 'solmetra3.ttf', 'solmetra4.ttf'];
    public $font_sizes = [13, 14, 15];
    public $colors = [[221, 27, 27], [94, 71, 212], [212, 71, 210], [8, 171, 0], [234, 142, 0]];
    public $shadow_color = [255, 255, 255];
    public $hide_shadow = false;
    public $char_num = 5;
    public $chars = ['A', 'C', 'D', 'E', 'F', 'H', 'J', 'K', 'L', 'M', 'N', 'Q', 'P', 'R', 'S', 'T', 'Y', '3', '4', '6', '7', '9'];
    public $session_var = 'iubUIYgiug'; // random string
    public $no_session = false;
    public $work_dir = 'work';
    public $work_ext = 'spaf';
    public $tag_ttl = 120;
    public $tag_cookie = 'kgiuYGIYUgoUYfoLUylg';
    public $gc_prob = 1;
    public $img_func_suffix = 'png';

    public function __construct()
    {
        if (!isset($this->session_var)) {
            $this->session_var = 'iubUIYgiug';
        }
        if (!isset($this->no_session)) {
            $this->no_session = false;
        }
        if (isset($this->no_session) && $this->no_session) {
            if ($this->work_dir == '') {
                $this->work_dir = dirname(__FILE__).'/';
            } elseif (substr($this->work_dir, 0, 1) != '/') {
                $this->work_dir = dirname(__FILE__).'/'.$this->work_dir;
            }
            if (substr($this->work_dir, -1) != '/') {
                $this->work_dir .= '/';
            }
            if (mt_rand(1, 100) < $this->gc_prob) {
                $this->launchGC();
            }
        } else {
            if (!isset($_SESSION)) {
                session_start();
            }
        }
    }

    public function setLibDir($dir)
    {
        $this->lib_dir = $dir;
    }

    public function tagUser()
    {
        if ($this->no_session) {
            $tag = $this->getRandomString($this->char_num);
            $cookie = md5(microtime().$_SERVER['REMOTE_ADDR']);

            setcookie($this->tag_cookie, $cookie, 0, '/');
            $_COOKIE[$this->tag_cookie] = $cookie;
            $this->writeFile($this->work_dir.$cookie.'.'.$this->work_ext, $tag);
        } else {
            $_SESSION[$this->session_var] = $this->getRandomString($this->char_num);
        }
        return true;
    }

    public function getUserTag()
    {
        if ($this->no_session) {
            if (!isset($_COOKIE[$this->tag_cookie]) || isset($_GET['regen'])) {
                $this->tagUser();
            }
            if (!file_exists($this->work_dir.$_COOKIE[$this->tag_cookie].'.'.$this->work_ext)) {
                $this->tagUser();
            }
            return @file_get_contents($this->work_dir.$_COOKIE[$this->tag_cookie].'.'.$this->work_ext);
        }
         
        if (!isset($_SESSION[$this->session_var]) || isset($_GET['regen'])) {
            $this->tagUser();
        }
        return $_SESSION[$this->session_var];
    }

    public function validRequest($req)
    {
        return strtolower($this->getUserTag()) == strtolower($req) ? true : false;
    }

    public function getRandomString($chars = 5)
    {
        $str = '';
        $cnt = sizeof($this->chars);

        for ($i = 0; $i < $chars; $i++) {
            $str .= $this->chars[mt_rand(0, $cnt - 1)];
        }
        
        return $str;
    }

    public function streamImage()
    {
        $background = $this->backgrounds[mt_rand(0, sizeof($this->backgrounds) - 1)];
        $this->setImageFormat($background);

        $function = "imagecreatefrom".$this->img_func_suffix;
        $image = $function($this->lib_dir.$background);
        $colors = [];
        $color_count = sizeof($this->colors);
        
        for ($i = 0; $i < $color_count; $i++) {
            $colors[] = imagecolorallocate($image, $this->colors[$i][0], $this->colors[$i][1], $this->colors[$i][2]);
        }
        
        $shadow = imagecolorallocate($image, $this->shadow_color[0], $this->shadow_color[1], $this->shadow_color[2]);
        $word = $this->getUserTag();
        $width = imagesx($image);
        $height = imagesy($image);
        $lenght = strlen($word);
        $step = floor(($width / $lenght) * 0.9);
        
        for ($i = 0; $i < $lenght; $i++) {
            $char = substr($word, $i, 1);
            $font_size = $this->font_sizes[mt_rand(0, sizeof($this->font_sizes) - 1)];
            $data = [
                'size' => $font_size,
                'angle' => mt_rand(-20, 20),
                'x' => $step * $i + 5,
                'y' => mt_rand($font_size + 5, $height - 5),
                'color' => $colors[mt_rand(0, $color_count - 1)],
                'font' => $this->lib_dir.$this->fonts[mt_rand(0, sizeof($this->fonts) - 1)]
             ];

            if (!isset($this->hide_shadow) || !$this->hide_shadow) {
                imagettftext($image, $font_size, $data['angle'], $data['x'] + 1, $data['y'] + 1, $shadow, $data['font'], $char);
            }

            imagettftext($image, $font_size, $data['angle'], $data['x'], $data['y'], $data['color'], $data['font'], $char);
        }
        
        $function = "image".$this->img_func_suffix;
        header('Content-Type: image/'.$this->img_func_suffix);
        $function($image);
        imagedestroy($image);
        return true;
    }

    public function setImageFormat($file)
    {
        $arr = explode('.', $file);
        $ext = strtolower($arr[sizeof($arr) - 1]);
        
        switch ($ext) {
            case 'gif':
            case 'png':
            case 'jpeg':
                $this->img_func_suffix = $ext;
            break;
            
            case 'jpg':
                $this->img_func_suffix = 'jpeg';
            break;
            
            default:
                die('ERROR: Unsupported format!');
            break;
        }
    }

    public function destroy()
    {
        if ($this->no_session) {
            @unlink($this->work_dir.$_COOKIE[$this->tag_cookie].'.'.$this->work_ext);
            unset($_COOKIE[$this->tag_cookie]);
            setcookie($this->tag_cookie, '', 0, '/');
        } else {
            unset($_SESSION[$this->session_var]);
        }

        return true;
    }

    public function launchGC()
    {
        if ($dir = @opendir($this->work_dir)) {
            while (false !== ($file = @readdir($dir))) {
                $fdata = pathinfo($file);
                if ($fdata['extension'] == $this->work_ext && (filemtime($this->work_dir.$file) < (time() - ($this->tag_ttl * 60)))) {
                    @unlink($this->work_dir.$file);
                }
            }
            @closedir($dir);
        }

        return true;
    }

    public function writeFile($file, $content)
    {
        $fl = @fopen($file, 'w');
        $ret = @fwrite($fl, $content);
        @fclose($fl);
        
        return $ret;
    }
}
