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

$price_ranges[] = [
    'min' => '0',
    'max' => '20.00'
];
$price_ranges[] = [
    'min' => '20.00',
    'max' => '50.00'
];
$price_ranges[] = [
    'min' => '50.00',
    'max' => '100.00'
];
$price_ranges[] = [
    'min' => '100.00',
    'max' => '150.00'
];
$price_ranges[] = [
    'min' => '150.00',
    'max' => '200.00'
];
$price_ranges[] = [
    'min' => '200.00',
    'max' => '250.00'
];
$price_ranges[] = [
    'min' => '250.00',
    'max' => '300.00'
];
$price_ranges[] = [
    'min' => '300.00',
    'max' => '350.00'
];
$price_ranges[] = [
    'min' => '350.00',
    'max' => '400.00'
];
$price_ranges[] = [
    'min' => '400.00',
    'max' => '450.00'
];
$price_ranges[] = [
    'min' => '450.00',
    'max' => '500.00'
];
$price_ranges[] = [
    'min' => '500.00',
    'max' => '1000.00'
];
$price_ranges[] = [
    'min' => '1000.00',
    'max' => '1500.00'
];
$price_ranges[] = [
    'min' => '1500.00',
    'max' => '2000.00'
];
$price_ranges[] = [
    'min' => '2000.00',
    'max' => '2500.00'
];
$price_ranges[] = [
    'min' => '2500.00',
    'max' => '3000.00'
];
$price_ranges[] = [
    'min' => '3000.00',
    'max' => '4000.00'
];

if (!empty($_REQUEST['category'])) {
    $actual_category = (int) $_REQUEST['category'];
}
?>
<aside>
    <h3><?=_SEARCH_BY_PRICE?></h3>
    <ul class="nav nav-pills nav-stacked">
    <?php
    foreach ($price_ranges as $range) {
        $sql = "
            SELECT COUNT(id) AS CNT
            FROM ".$frm->prefix."_items
            WHERE active = '1'
            AND alive = '0'
            AND price BETWEEN '".$range['min']."' AND '".$range['max']."'
        ";

        if (!empty($actual_category)) {
            $sql = "
                AND categories LIKE '%|".$actual_category."|%' 
            ";
        }

        $count_items = $frm->sql_fetchone($sql);

        if (!empty($count_items['CNT'])) {?>
        <li>
            <a href="index.php?plugin=search&amp;op=items&amp;action=prices<?=(!empty($actual_category) ? '&amp;category='.$actual_category : '')?>&amp;min=<?=$range['min']?>&amp;max<?=$range['max']?>">
                <?=$range['min']?> - <?=$range['max']?>
            </a>
        </li>
        <?php
        }
    }

    $sql = "
        SELECT COUNT(id) AS CNT
        FROM ".$frm->prefix."_items
        WHERE active = '1'
        AND alive = '0'
        AND price > '4000.00'
    ";

    if (!empty($actual_category)) {
        $sql = "
            AND categories LIKE '%|".$actual_category."|%' 
        ";
    }

    $count_items = $frm->sql_fetchone($sql);

    if (!empty($count_items['CNT'])) {?>
        <li>
            <a href="index.php?plugin=search&amp;op=items&amp;action=prices<?=(!empty($actual_category) ? '&amp;category='.$actual_category : '')?>&amp;min=4001.00">
                4000.00 and up
            </a>
        </li>
    <?php }?>
    </ul>
</aside>