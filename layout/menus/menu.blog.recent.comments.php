<?php   

use Plugins\Blog\Classes\Blog as Blog;
use Plugins\Comments\Classes\Comments as Comments;

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
$comments = new Comments;
$blog_comments = $comments->list_items(0, 10, 'blog');

if (!empty($blog_comments)) {
    foreach ($blog_comments as $key => $value) {
        $items_info[$key] = $value;
        $items_info[$key]['url'] = $blogs->get_items_url((int) $value['parent']);
    }

    if (!empty($items_info)) {?>
        <aside>
            <h3><?=_RECENT_COMMENTS?></h3>
            <?php foreach ($items_info as $menu_comments) {?>
            <section itemscope itemtype="http://schema.org/Comment">
                <div class="date" datetime="<?=date('Y-m-d', strtotime($menu_comments['date']))?>" itemprop="dateCreated">
                    <?=$helper->format_date($menu_comments['date'])?>
                </div>
                <a href="<?=$menu_comments['url']?>#comment_<?=$menu_comments['id']?>" title="<?=$helper->reduce_text($menu_comments['text'])?>" rel="comments" itemprop="url">
                    <span itemprop="name">
                        <?=$helper->reduce_text($menu_comments['text'])?>
                    </span>
                </a>
            </section>
            <?php
            }?>
        </aside>
    <?php
    }
}
