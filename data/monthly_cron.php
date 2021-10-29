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

# Virus scan
if (file_exists('/usr/local/cpanel/3rdparty/bin/clamscan')) {
    $output = shell_exec('/usr/local/cpanel/3rdparty/bin/clamscan -ri '.$_SERVER['DOCUMENT_ROOT']);
        
    if (!empty($output)) {
        file_put_contents($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/data/virus-scan.txt', $output);
        $frm->send_emails('Virus scan from '.$frm->site_domain(), $output, 'virus-scan@'.$frm->site_domain(), 'Virus Scan Result', 'dev@miamiweb.org', 'WDE');
    }
}
