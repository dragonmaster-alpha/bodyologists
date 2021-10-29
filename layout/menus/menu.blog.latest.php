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

if ($items_info == null) {
    $blogs = new Plugins\Blog\Classes\Blog;
    $items_info = $blogs->get_latest_items(0, 2);
}

if (!empty($items_info)) {?>
    <div class="container">
        <div class="row">
            <div class="col-md-9">
            <h2 class="slim text-center-xs text-center-sm">Check out our blog for deals and tips!</h2>
            </div>
            <div class="col-md-3 padding-top-20 text-right hidden-sm hidden-xs">
                <a href="blog" class="btn btn-danger btn-lg">
                    View all articles and Tips
                </a>
            </div>
        </div>
        <ul class="multiple-articles">
            <?php foreach ($items_info as $article) {?>
            <li>
                <script type="application/ld+json"><?=$article['ld_json']?></script>
                <div class="images">
                    <a href="<?=$article['url']?>">
                        <img src="<?=_SITE_PATH?>/uploads/blog/<?=$article['media']['bid']?>/<?=$article['media']['image']?>" alt="<?=$article['title']?>">
                    </a>
                </div>
                <div class="title">
                    <a href="<?=$article['url']?>">
                        <?=$article['title']?>
                    </a>
                </div>
                <p><?=$helper->reduce_words($helper->filter($article['text'], 1), 40)?></p>
            </li>
            <?php }?>
        </ul>
    </div>
<?php }?>