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
<aside>
    <h3><?=_CONTACT_US?></h3>
    <section>
        <form action="sendMail.php" method="post"  class="validate-form save">
            <div class="alert alert-error hide"></div>
            <div class="alert alert-success hide"></div>
            <input type="hidden" value="Quick contact from <?=$frm->site_domain()?>" name="subject" />
            <input type="hidden" value="ajax" name="method" />
            <div>
                <input type="text" class="span12" name="name" placeholder="<?=_NAME?>" />
            </div>
            <div>
                <input type="text" class="span12" name="email" placeholder="<?=_EMAIL?>" />
            </div>
            <div>
                <input type="text" class="span12" name="phone" placeholder="<?=_PHONE?>" />
            </div>
            <div>
                <textarea name="message" class="span12" placeholder="<?=_MESSAGE?>"></textarea>
            </div>
            <hr />
            <div class="text-right">
                <button class="btn btn-success">
                    <?=_SUBMIT?>
                </button>
            </div>
        </form>
    </section>
</aside>