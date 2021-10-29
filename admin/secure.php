<?php

defined('DOC_ROOT') || define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT']);
defined('APP_DIR') || define('APP_DIR', DOC_ROOT.'/app');

use App\Log;

/**
 * @author
 * Web Design Enterprise
 * Phone: 786.234.6361
 * Website: www.webdesignenterprise.com
 * E-mail: info@webdesignenterprise.com
 *
 * @copyright
 * This work is licensed under the Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 United States License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/3.0/legalcode
 *
 * Be aware, violating this license agreement could result in the prosecution and punishment of the infractor.
 *
 * � 2002-2009 Web Design Enterprise Corp. All rights reserved.
 */

require_once(DOC_ROOT.'/mainfile.php');

$ip = $_SERVER['REMOTE_ADDR'];
$actual_time = date('Y-m-d H:i:s', time() - (_SECURITY_BANNED_TIME * 60 * 60));

$count_security = $frm->sql_count('banned', "ip = '".$ip."' AND date > '".$actual_time."'");

if (empty($count_security)) {
    header("Location: index.php");
    exit(0);
}
 
    $frm->sql_update('banned', ['date' => date('Y-m-d H:i:s')], ['ip' => $ip]);

require_once APP_DIR.'/class.log.php';
    new Log('[HACK ATTEMPT] Banned person keep on trying to hack into the administration area');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Forbidden | Admin Console</title>
<link rel="stylesheet" href="css/style.default.css" type="text/css" />
<link id="addonstyle" rel="stylesheet" href="css/style.contrast.css" type="text/css">
<script type="text/javascript" src="js/plugins/jquery-1.7.min.js"></script>
<script type="text/javascript" src="js/plugins/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="js/plugins/jquery.cookie.js"></script>
<script type="text/javascript" src="js/custom/general.js"></script>
<!--[if IE 9]>
    <link rel="stylesheet" media="screen" href="css/style.ie9.css"/>
<![endif]-->
<!--[if IE 8]>
    <link rel="stylesheet" media="screen" href="css/style.ie8.css"/>
<![endif]-->
<!--[if lt IE 9]>
    <script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
<![endif]-->
</head>
<body>
<div class="bodywrapper">
    <div class="topheader orangeborderbottom5" style="height: 50px;">
        <div class="left">
            <h1 class="logo"><a href="dashboard.html">Admin <span>Console</span></a></h1>
            <span class="slogan">advanced administration system</span>
        </div>
    </div>
    <div class="contentwrapper padding10">
        <div class="errorwrapper error403">
            <div class="errorcontent">
                <h1><span class="icon-blocked" style="font-size: 40px; padding-right: 20px; position: relative; top: 5px;"></span> Forbidden Access</h1>
                <hr />
                <h3>This section of the site is for AUTHORIZED PERSONNEL ONLY.</h3>
                <p>You had <strong>(<?=_SECURITY_FAILED_ATTEMPTS?>)</strong> failed login attempts. Due to security concerns you have been banned from accessing this website.</p>
                <p><strong>The site administrator has being notified about this issues and all data referring your failed attempts has being recorded. Be aware, if something happen to this website related to any hacking attempt we will inform the authorities and give them all our recorded information for them to prosecute and punish you.</strong></p>
                <br />
                <button class="stdbtn btn_orange" onclick="location.href='http://google.com'">Get out of here</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<??>