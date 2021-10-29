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
$items_info = $blogs->get_archives();

if (!$frm->empty_array($items_info)) {?>
<aside>
    <h3><?=_ARTICLES_ARCHIVES?></h3>
    <?php foreach ($items_info as $menu_blog) {?>
        <div class="calendar-date">
            <a href="index.php?plugin=blog&amp;op=archives&amp;year=<?=$menu_blog['year']?>&amp;month=<?=$menu_blog['month']?>" title="<?=$menu_blog['title']?>" rel="articles" itemprop="url">
                <div class="date-top">
                    <?=substr($menu_blog['month_name'], 0, 3)?>
                    <small class="date-bottom"><?=$menu_blog['year']?></small>
                </div>
            </a>
        </div>
    <?php }?>
</aside>
<?php }?>