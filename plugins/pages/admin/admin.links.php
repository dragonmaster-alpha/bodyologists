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

ob_start();

?>
    <li <?=($open_plugin == 'pages') ? 'class="current"' : ''?>>
        <a href="admin.php?plugin=pages">Pages</a>
    </li>
<?php

$admin_menu .= ob_get_contents();
ob_end_clean();

$_addon_dir = $_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/pages/addons';

if (is_dir($_addon_dir)) {
    # Also include any add-ons that may exists.
    $addons_list = scandir($_addon_dir);
    foreach ($addons_list as $addon) {
        if (is_dir($_addon_dir.'/'.$addon)) {
            if (file_exists($_addon_dir.'/'.$addon.'/admin.links.php')) {
                include($_addon_dir.'/'.$addon.'/admin.links.php');
            }
        }
    }
}
