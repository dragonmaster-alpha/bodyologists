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
 * © 2002-2009 Web Design Enterprise Corp. All rights reserved.
 */

    $watermark = @imagecreatefrompng('../images/watermark.png') or exit('Cannot open the watermark file.');
    imageAlphaBlending($watermark, false);
    imageSaveAlpha($watermark, true);
    
    $image_string = @file_get_contents($_SERVER["DOCUMENT_ROOT"].$_GET['src']) or exit('Cannot open image file.');

    $image = @imagecreatefromstring($image_string) or exit('Not a valid image format.');
    $imageWidth = imageSX($image);
    $imageHeight = imageSY($image);
    
    $watermarkWidth = imageSX($watermark);
    $watermarkHeight = imageSY($watermark);
    
    $coordinate_X = ($imageWidth - 5) - ($watermarkWidth);
    $coordinate_Y = ($imageHeight - 5) - ($watermarkHeight);
    imagecopy($image, $watermark, $coordinate_X, $coordinate_Y, 0, 0, $watermarkWidth, $watermarkHeight);
    
    header('Content-Type: image/jpeg');
    imagejpeg($image, $SaveToFile, 100);
    imagedestroy($image);
    imagedestroy($watermark);
    exit;
