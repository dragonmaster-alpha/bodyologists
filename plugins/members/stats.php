<?php

use App\ObjectType;
use App\Stats;
use Plugins\Members\Classes\Members;

require_once APP_DIR.'/class.object_type.php';

if (!$members->is_user()) {
    $_SESSION['referer'] = 'members/stats';
    $helper->redirect('members');
}
$current_user_id = Members::currentUserId();
$stats = new Stats();
$period = $stats->getValidPeriod($_REQUEST['period'] ?? null);
$label = key($period);
$since = ucwords(str_replace('_',' ', $label));

$pageViews = $stats->getPageViewsByPeriod($current_user_id, $label);
$profileSource = $stats->getProfileViewSource($current_user_id, $label);


ob_start('ob_gzhandler');
include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/stats/layout.stats.phtml');
$modcontent = ob_get_clean();
include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');