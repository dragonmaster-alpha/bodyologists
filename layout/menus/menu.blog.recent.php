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
$menu_items = $blogs->list_items(0, 5, 1, $item['category']);

if (!empty($menu_items)) {?>
    <div>
        <h3 role="heading" class="text-center">Similar Articles</h3>
        <div class="separator sm"></div>
        <?php foreach ($menu_items as $menu_item) {
    if ($menu_item['id'] != $item['id']) {?>
        <div class="row" style="padding: 20px; background-color: #fff">
            <div class="col-md-4">
                <a href="<?=$menu_item['url']?>">
                    <img src="<?=_SITE_PATH?>/uploads/blog/<?=$menu_item['media']['bid']?>/<?=$menu_item['media']['image']?>" alt="<?=$menu_item['title']?>" class="img-fluid" />
                </a>
            </div>
            <div class="col-md-8" style="font-size: 14px; line-height: 14px">
                <?=$menu_item['title']?>
            </div>
        </div>
        <div class="separator sm"></div>
    <?php
    }
}?>
</div>
<?php }?>