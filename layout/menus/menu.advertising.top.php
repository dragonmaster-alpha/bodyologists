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
$top_ads = $ads->get_placed_ads($place, 1, 1);
 
if (count($top_ads) > 0) {
    foreach ($top_ads as $top_ad) {
        $ads->update_counter($top_ad['id']);
        
        if ($top_ad['ad_type'] == 'banner') {?>
            <a href="index.php?plugin=Advertising&amp;id=<?=$top_ad['id']?>" target="_blank"><img src="<?=$top_ad['image']?>" alt="<?=$top_ad['alt']?>" title="<?=$top_ad['alt']?>" /></a>
        <?php } else {?>
            <?=$top_ad['text']?>
        <?php
        }
    }
}
