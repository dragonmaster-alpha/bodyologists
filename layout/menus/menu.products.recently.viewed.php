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

if (isset($_SESSION['products']['viewed'])) {?>
<aside>
	<h3><?=_RECENTLY_VIEWED_ITEMS?></h3>
    <ul class="nav nav-tabs nav-stacked"> 
    <?php
    foreach ($_SESSION['products']['viewed'] as $viewed_item) {
        $item = $products->get_items_info((int) $viewed_item);
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/products/layout/layout.items.side.phtml');
    }
    ?>
    </ul>
</aside>
<?php }?>
