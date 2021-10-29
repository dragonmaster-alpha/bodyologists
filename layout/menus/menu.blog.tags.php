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
$items_tags = $blogs->get_all_tags();

$tags = explode(',', $items_tags);
if (!empty($tags)) {
    foreach ($tags as $tag) {
        if (!empty($tag)) {?>
            <div class="d-inline-block m-2">
                <a href="blog?tag=<?=trim($tag)?>" class="btn btn-outline-secondary rounded-pill">
                    <?=$tag?>
                </a>
            </div>
        <?php
        }
    }
}
