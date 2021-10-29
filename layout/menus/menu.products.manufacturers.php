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

require_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/kernel/classes/class.multi.dimentinal.array.php');

$manufacturers_class = new Plugins_Products_Addons_Manufacturers_Classes_Manufacturers;
$items_array = new Multi_Dimentional_Array($manufacturers_class->list_items(0, 0, 1));
$manufacturers_menu = $items_array->render();

?>

<nav>
    <ul class="nav nav-tabs nav-stacked">
        <?=$helper->list_from_array($manufacturers_menu)?>
    </ul>
</nav>
