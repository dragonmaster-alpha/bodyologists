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
<aside class="hidden-phone no-print" style="padding-bottom: 40px;">
    <h3><?=_NEED_HELP?></h3>
    <section>
        <small><?=_NEED_HELP_EXPLANATION?></small>
        <hr />
        <div class="row-fluid">
            <div class="span3">
                <b><?=_EMAIL?>:</b>
            </div>
            <div class="span9">
                <a href="mailto:<?=$frm->config['contactemail']?>"><?=$frm->config['contactemail']?></a>
            </div>
        </div>
    	<div class="row-fluid">
            <div class="span3">
                <b><?=_PHONE?>:</b>
            </div>
            <div class="span9">
                <a href="callto:<?=$frm->config['phone']?>"><?=$frm->config['phone']?></a>
            </div>
        </div>
    </section>
</aside>
