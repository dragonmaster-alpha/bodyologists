<?php

if (!defined('PLUGINS_FILE')) {
    echo _NO_ACCESS_DIV_TEXT;
    Header("Refresh: 5; url=index.php");
}

$plugin_name = basename(dirname(__FILE__));
$helper->get_plugin_lang($plugin_name);


try {


    $meta['title'] = "Contact";
    $meta['name'] = "Contact";
    $country = (isset($_SESSION['register']['country'])) ? $_SESSION['register']['country']: 'US';

    ob_start('ob_gzhandler');
    include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/contact/layout/layout.index.phtml');
    $modcontent = ob_get_clean();
    include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
} catch (Exception $e) {
    die('ERROR ON LINE: '.__LINE__.': '.$e->getMessage());
}
