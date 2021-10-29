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

if (isset($_REQUEST['plugin'])) {
    $place = $_REQUEST['plugin'];
} elseif (is_index()) {
    $place = 'Home';
} else {
    $place = 'Pages';
}

$ads = new Plugins_Advertising_Classes_Advertising;
$bottom_ads = $ads->get_placed_ads($place, 1, 3);

if (count($bottom_ads) > 0) {
    foreach ($bottom_ads as $bottom_ad) {
        $ads->update_counter($bottom_ad['id']);
        
        if ($left_ad['ad_type'] == 'banner') {?>
            <a href="index.php?plugin=Advertising&amp;id=<?=$bottom_ad['id']?>" target="_blank"><img src="<?=$bottom_ad['image']?>" alt="<?=$bottom_ad['alt']?>" title="<?=$bottom_ad['alt']?>" /></a>
        <?php } else {?>
            <?=$bottom_ad['text']?>
        <?php
        }
    }
}
