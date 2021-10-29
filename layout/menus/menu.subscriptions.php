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

if (!isset($_SESSION['subscriptions']['done']) && !$members->is_user()) {?>
<aside class="hidden-phone no-print" style="padding-bottom: 40px;" id="subscriptions-menu">
    <h3><?=_SUBSCRIBE?></h3>
    <section>
        <small><?=_SUBSCRIBE_EXPLANATION?></small>
        <hr />
        <form action="index.php?plugin=subscriptions&amp;op=ajax" method="post" class="validate-form save" data-element="#subscriptions-menu">
            <div class="alert alert-error hide"></div>
            <div class="alert alert-success hide"></div>
            <div>
                <input type="text" value="" class="span12 required" name="name" placeholder="<?=_NAME?>" />
            </div>
            <div>
                <input type="text" value="" class="span12 required" name="email" placeholder="<?=_EMAIL?>" />
            </div>
            <hr />
            <div class="text-right">
                <button type="submit" class="btn btn-info">
                    <?=_SAVE?>
                </button>
            </div>
        </form>
    </section>
</aside>
<?php }?>