<?php

use App\Security\Captcha as Captcha;

include_once('../mainfile.php');

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
require_once('../app/security/class.captcha.php');
$spaf_obj = new Captcha();
$spaf_obj->streamImage();
