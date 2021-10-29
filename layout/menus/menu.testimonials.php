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

# Menu title: Information
$testimonials = new Plugins_Terminal_Classes_Terminal;
$menu_testimonials = $testimonials->get_rand(1);

if (!empty($menu_testimonials)) {
    ?>
<nav>
    <ul class="nav nav-tabs nav-stacked">
    <?php
    foreach ($menu_testimonials as $menu_testimonial) {?>
    <li>
        <a href="index.php?plugin=testimonials">
        	<?=$menu_testimonial['title']?>
        </a>
    </li>
    <?php } ?>
	</ul>
</nav>
<?php
}
