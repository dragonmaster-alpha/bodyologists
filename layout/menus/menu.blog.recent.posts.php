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
$items_info = $blogs->get_latest_items(0, 10, 1);

if (!empty($items_info)) {?>
    <aside>
        <h3><?=_RECENT_POSTS?></h3>
        <?php foreach ($items_info as $menu_blog) {?>
        <section>
            <div class="date" datetime="<?=date('Y-m-d', strtotime($menu_blog['date']))?>">
                <?=$helper->format_date($menu_blog['date'])?>
            </div>
            <a href="<?=$menu_blog['url']?>" title="<?=$menu_blog['title']?>" rel="articles">
                <?=$menu_blog['title']?>
            </a>
        </section>
        <?php }?>
    </aside>
<?php }?>