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

$categories_class = new Plugins_Products_Addons_Categories_Classes_Categories;
$items_array = new Multi_Dimentional_Array($categories_class->get_sorting_categories());
$categories_menu = $items_array->render();

?>

<aside>
	<h3><?=_CATEGORIES?></h3>
    <ul class="nav nav-tabs nav-stacked">
        <?=$helper->list_from_array($categories_menu)?>
    </ul>
</aside>
