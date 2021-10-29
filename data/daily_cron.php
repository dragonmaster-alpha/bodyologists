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

include_once('../mainfile.php');

$time = time();
$date = date('Y-m-d', time());
$datetime = date('Y-m-d H:i:s', time());

if ($frm->table_exists('items')) {
    # Unset item as new
    $frm->sql_update('items', ['is_new' => 0], "is_new = '1' AND keep_new_until < now()");

    # Start scheduled special
    $frm->sql_update('items_special', ['active' => 1], "active = '0' AND special_start > now() AND special_end < now()");

    # End scheduled special
    $frm->sql_update('items_special', ['active' => 0, 'special' => ''], "active = '1' AND special_end < now()");
}

if ($frm->table_exists('blog')) {
    # Activate blog articles when needed
    $frm->sql_update('blog', ['active' => 1], "active = '0' AND date < now()");
}

$newsletters_class = new Kernel_Classes_Newsletters();
$newsletters_class->send_out();
