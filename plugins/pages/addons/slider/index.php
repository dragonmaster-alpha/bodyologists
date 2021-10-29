<?php

use Plugins\Pages\Addons\Slider\Classes\Slider as Slider;

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

if (!defined('PLUGINS_FILE')) {
    echo _NO_ACCESS_DIV_TEXT;
    Header("Refresh: 5; url=index.php");
}

$slider_class = new Slider;
$slider_images = $slider_class->list_items();

if (!empty($slider_images)) {
    ?>
<div class="slider">
    <?foreach ($slider_images as $slider_image) {?>
    <div class="image" style="background-image: url(<?=_SITE_PATH?>/uploads/slider/<?=$slider_image['bid']?>/<?=$slider_image['image']?>)">
        <?if (!empty($slider_image['link'])) {?>
        <a href="<?=$slider_image['link']?>">
            <img src="images/blank.gif" alt="Click here..." />
        </a>
        <?}?>
    </div>
    <?} ?>
</div>
<?php
}
