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

$blogs = new Plugins\Blog\Classes\Blog;
$authors = $blogs->get_author_info($item['poster']);

if (count($authors) > 0) {
    foreach ($authors as $author) {?>

        <div class="col-12 mt-4 mb-2">
            <h2 style="font-weight: 100">About The Author</h2>
        </div>
        <div class="col-12 px-4 bg-light">
            <div class="row">
                <div class="col-3 text-center py-5">
                    <img src="<?=_SITE_PATH?>/uploads/staff/<?=md5($author['aid'])?>/thumb-<?=$author['media']['image']?>" class="rounded-circle" alt=""/>
                </div>
                <div class="col-9 py-3">
                    <h3 role="heading"><?=$author['name']?></h3>
                    <div class="authortext">
                        <?=$author['about']?>
                    </div>
                </div>
            </div>
        </div>

    <?php } ?>
<?php
}?>