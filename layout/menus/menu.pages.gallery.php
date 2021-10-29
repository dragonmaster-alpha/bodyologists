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

require_once('plugins/pages/addons/gallery/classes/class.gallery.php');

$galleries = new Plugins_Pages_Classes_Gallery;
$menu_galleries = $galleries->list_items(0, 12, '', 'gallery', '', 'date', 'DESC');

if (!empty($menu_galleries)) {
    ?>

    <script type="text/javascript">
        jQuery(function($){
            $('#gallery_items').jcarousel();
        });
    </script>
    <h2><?=_PHOTO_GALLERY?></h2>
    <ul id="gallery_items" class="jcarousel-skin-tango">
		<?php
        foreach ($menu_galleries as $menu_gallery) {
            ?>
		<li>
            <img src="<?=_SITE_PATH?>/uploads/Gallery/<?=$menu_gallery['bid']?>/small-<?=$menu_gallery['image']?>" />
		</li>
		<?php
        } ?>
	</ul>
<?php
}
