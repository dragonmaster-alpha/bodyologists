<?php

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
 * © 2002-2009 Web Design Enterprise Corp. All rights reserved.
 */

require_once('../mainfile.php');
//session_start();
session_regenerate_id(true);

$administrator->security_check();

# Crate CSRF token
require_once(__DIR__.'/../app/security/class.csrf.php');
$csrf = App\Security\Csrf::get_token('token');

if ($administrator->is_admin()) {
    header("Location: admin.php");
    exit;
}

unset($_SESSION['authenticated']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Admin Console</title>
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="images/ico/apple-touch-icon-114x114-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="images/ico/apple-touch-icon-72x72-precomposed.png">
<link rel="apple-touch-icon-precomposed" href="images/ico/apple-touch-icon-57x57-precomposed.png">
<link rel="shortcut icon" href="images/ico/favicon.png">
<link rel="stylesheet" href="css/style.default.css" type="text/css" />

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
<body class="loginpage">
	<div class="loginbox">
    	<div class="loginboxinner">
            <div class="logo">
            	<h1><span>ADMIN </span>AREA</h1>
                <p>premium admin console</p>
            </div><!--logo-->
            <br clear="all" /><br />
            <div class="nousername">
				<div class="loginmsg">The password you entered is incorrect.</div>
            </div>
            <form id="login" action="admin.php" method="post">
                <div class="username">
                	<div class="usernameinner">
                    	<input type="text" name="username" id="username" />
                    </div>
                </div>
                <div class="password">
                	<div class="passwordinner">
                    	<input type="password" autocomplete="off" name="password" id="password" />
                    </div>
                </div>
                <button>Sign In</button>
            </form>
        </div>
    </div>
    <script type="text/javascript">
        var token = '<?=$csrf?>';
    </script>
    <script type="text/javascript" src="js/plugins/jquery-1.7.min.js"></script>
    <script type="text/javascript" src="js/plugins/jquery-ui-1.8.16.custom.min.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.cookie.js"></script>
    <script type="text/javascript" src="js/custom/general.js"></script>
    <script type="text/javascript" src="js/custom/index.js"></script>
</body>
</html>