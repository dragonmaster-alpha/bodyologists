<?php

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
 * � 2002-2009 Web Design Enterprise Corp. All rights reserved.
 */

header("Content-Type: image/jpeg");

$text = base64_decode($_GET['email']);
$pic_width = strlen($text) * 6;
$pic_height = 12;
$pic = imagecreatetruecolor($pic_width + 1, $pic_height + 1);
$grey = imagecolorallocate($pic, 0x6F, 0x6F, 0x6F);
$trans_temp = imagecolorallocate($pic, 254, 254, 254);

imagefilledrectangle($pic, 0, 0, $pic_width, $pic_height, $trans_temp);
ImageString($pic, 2, 0, 0, $text, $grey);
ImageJPEG($pic);
ImageDestroy($pic);
