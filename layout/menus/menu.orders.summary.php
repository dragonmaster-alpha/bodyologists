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

?>
<aside class="no-print" style="padding-bottom: 40px;">
    <h3><?=_ORDER_SUMMARY?></h3>
    <section>
        <div>
            <?php if (!empty($_SESSION['shopping_cart']['saved_amount'])) {?>
                <div class="clear">
                    <div style="float: left; width: 60%;">
                        <?=_DISCOUNT?>:  
                    </div>
                    <div style="float: left; width: 40%; text-align: right;">
                        - $<?=$_SESSION['shopping_cart']['saved_amount']?>
                    </div>
                </div>
            <?php }?>
            <div class="clear">
                <div style="float: left; width: 60%;">
                    <?=_MERCHANDISE?>: 
                </div>
                <div style="float: left; width: 40%; text-align: right;">
                    $<?=$_SESSION['shopping_cart']['subtotal']?>
                </div>
            </div>
                <div class="clear">
                    <div style="float: left; width: 60%;">
                        <?=_SHIPPING_HANDLING?>: 
                    </div>
                    <div id="shipping_cost" style="float: left; width: 40%; text-align: right;">
                        $<?=(!empty($_SESSION['shopping_cart']['shipping'])) ? $_SESSION['shopping_cart']['shipping'] : '0.00'?>
                    </div>
                </div>
            <?php if (!empty($_SESSION['shopping_cart']['taxes'])) {?>
                <div class="clear">
                    <div style="float: left; width: 60%;">
                        <?=_TAX?>: 
                    </div>
                    <div style="float: left; width: 40%; text-align: right;">
                        $<?=$_SESSION['shopping_cart']['taxes']?>
                    </div>
                </div>
            <?php }?>
            <hr />
            <div class="clear">
                <div style="float: left; width: 60%; font-size: 16px;">
                    <?=_TOTAL?>: 
                </div>
                <div id="estimated" style="float: left; width: 40%; text-align: right; font-size: 16px;">
                    $<?=$_SESSION['shopping_cart']['total']?>
                </div>
            </div>
        </div>
    </section>
</aside>
