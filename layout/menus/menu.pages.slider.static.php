<?php   

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

if (stristr($_SERVER['PHP_SELF'], basename(__FILE__))) {
    Header("Location: "._SITE_PATH."/");
    exit();
}

$slider_images = $helper->files_list($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/uploads/slider/c4ca4238a0b923820dcc509a6f75849b', '.jpg');

?>
<div id="slides">
    <?php
    foreach ($slider_images as $slider_image) {?>
        <div class="slider">
            <img src="<?=_SITE_PATH?>/uploads/slider/c4ca4238a0b923820dcc509a6f75849b/<?=$slider_image?>" />
        </div>
    <?php }?>
</div>