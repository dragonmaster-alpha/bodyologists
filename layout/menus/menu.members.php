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

global $members;

?>
<aside class="no-print hidden-phone">
    <h3><?=_MEMBERS_AREA?></h3>
    <section>
        <?php if ($members->is_user()) {?>
        <div class="members-menu">
            <a href="index.php?plugin=members">
                <span class="icon-keyhole"></span> <?=_MY_ACCOUNT?>
            </a>
            <a href="index.php?plugin=members&amp;file=messages">
                <span class="icon-comments-2"></span> <?=_MESSAGES?>
            </a>
            <a href="index.php?plugin=members&amp;file=profile">
                <span class="icon-user-2"></span> <?=_EDIT_PROFILE?>
            </a>
            <a href="index.php?plugin=members&amp;file=address" title="<?=_ADDRESS_BOOK?>" style="display: block;">
                <span class="icon-address"></span> <?=_ADDRESS_BOOK?>
            </a>
            <a href="index.php?plugin=members&amp;file=orders">
                <span class="icon-basket"></span> <?=_ORDERS_SUMMARY?>
            </a>
            <a href="index.php?plugin=members&amp;op=logout">
                <span class="icon-switch"></span> <?=_LOGOUT?>
            </a>
        </div>
        <?php } else {?>
            <form action="index.php?plugin=members" method="post" autocomplete="off">
                <input type="hidden" name="op" value="sign_in" />
                <div>
            		<div class="row">
                        <input type="email" name="username" class="form-control" placeholder="<?=_EMAIL?>" />
            		</div>
                    <div class="row">
                        <input type="password" name="passwd" class="form-control" placeholder="<?=_PASSWORD?>" />
            		</div>
                    <div class="row">
                        <div class="col-md-7">
                            <a href="index.php?plugin=members&amp;op=lost_password" title="<?=_FORGOT_PASSWORD?>" style="display: inline; padding: 0;">
                                <?=_FORGOT_PASSWORD?>
                            </a>
                        </div>
                        <div class="col-md-5 text-right">
                            <a href="index.php?plugin=members&amp;op=register" title="<?=_REGISTER_NOW?>" style="display: inline; padding: 0;">
                                <?=_REGISTER_NOW?>
                            </a>
                        </div>
                    </div>
                    <hr />
                    <?php if (!empty($members_settings['benefits'])) {?>
                    <div class="row">
                        <small class="col-md-12">
                            <?=$members_settings['benefits']?>
                        </small>
                    </div>
                    <?php } else {?>
                    <div class="row">
                        <small class="col-md-12">
                            <?=_SIGN_IN_EXPLANATION?>
                        </small>
                    </div>
                    <?php }?>
                </div>
                <hr />
                <div class="row text-right">
                    <div class="col-md-12">
                        <button class="btn btn-success" type="submit">
                            <span style="font-size: 14px;" class="icon-locked"></span>
                            <?=_SIGN_IN?>
                        </button>
                    </div>
                </div>
                <div class="">
                    
                </div>
            </form>
        <?php }?>
    </section>
</aside>