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
$beauty_count = $blogs->count(1, 'beauty');
$fitness_count = $blogs->count(1, 'fitness');
$health_count = $blogs->count(1, 'health');

?>
<div class="d-lg-block d-none">
    <div class="separator "></div>
    <h3 role="heading" class="text-center">Categories</h3>
    <div class="row">
        <div class="col-md-12">
            <a href="blog?category=beauty">
                <img src="images/blog/BEAUTY.png" alt="BEAUTY" />
                <span style="position: absolute; top: 20px; right: 30px; display: block; width: 45px; height: 40px; text-align: center; color: #fff; font-size: 24px"><?=$beauty_count?></span>
            </a>
        </div>
    </div>
    <div class="separator xs"></div>
    <div class="row">
        <div class="col-md-12">
            <a href="blog?category=fitness">
                <img src="images/blog/FITNESS.png" alt="FITNESS" />
                <span style="position: absolute; top: 20px; right: 30px; display: block; width: 45px; height: 40px; text-align: center; color: #fff; font-size: 24px"><?=$fitness_count?></span>
            </a>
        </div>
    </div>
    <div class="separator xs"></div>
    <div class="row">
        <div class="col-md-12">
            <a href="blog?category=health">
                <img src="images/blog/HEALTH.png" alt="HEALTH" />
                <span style="position: absolute; top: 20px; right: 30px; display: block; width: 45px; height: 40px; text-align: center; color: #fff; font-size: 24px"><?=$health_count?></span>
            </a>
        </div>
    </div>
</div>
<div class="col-12 d-lg-none d-block">
    <div class="row blog-categories-row">
        <div class="col-4 blog-category-wrapper-left">
            <div class="row" style="margin-right: 0;">
                <div class="col-md-12" style="padding-right: 0;">
                    <a href="blog?category=health">
                        <img class="blog-category-health" src="images/blog/HEALTH.png" alt="HEALTH" />
                        <span style="position: absolute; top: 6px; right: 16%; display: block; width: 0; height: 40px; text-align: center; color: #333; font-size: 19px"><?=$health_count?></span>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-4 blog-category-wrapper-middle">
            <div class="row" style="margin-right: 0;">
                <div class="col-md-12" style="padding-right: 0;">
                    <a href="blog?category=beauty">
                        <img class="blog-category-beauty" src="images/blog/BEAUTY.png" alt="BEAUTY" />
                        <span style="position: absolute; top: 6px; right: 16%; display: block; width: 0; height: 40px; text-align: center; color: #333; font-size: 19px"><?=$beauty_count?></span>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-4 blog-category-wrapper-right">
            <div class="row" style="margin-right: 0;">
                <div class="col-md-12" style="padding-right: 0;">
                    <a href="blog?category=fitness">
                        <img class="blog-category-fitness" src="images/blog/FITNESS.png" alt="FITNESS" />
                        <span style="position: absolute; top: 6px; right: 16%; display: block; width: 0; height: 40px; text-align: center; color: #333; font-size: 19px"><?=$fitness_count?></span>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>


