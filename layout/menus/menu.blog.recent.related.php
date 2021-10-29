<?php

use Plugins\Blog\Classes\Blog as Blog;

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

$blogs = new Blog;
$items_info = $blogs->get_related($item['id'], 4);

if (!empty($items_info)) {?>

    <?php foreach ($items_info as $menu_blog) {?>
    <div class="row bg-light mb-3 no-gutters">
        <div class="col-4 text-center">
            <img src="<?=_SITE_PATH?>/uploads/blog/<?=$menu_blog['media']['bid']?>/thumb-<?=$menu_blog['media']['image']?>" alt="<?=$menu_blog['title']?>" class="img-fluid" />
            <?=date('M d', strtotime($menu_blog['date']))?>
        </div>
        <div class="col-8 text-center">
            <a href="<?=$menu_blog['url']?>" title="<?=$menu_blog['title']?>" rel="articles" style="font-size: 1rem; line-height: 1.1rem;">
                <?=$menu_blog['title']?>
            </a>
        </div>
    </div>
    <?php }?>
<?php }?>