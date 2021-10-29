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

$ip = $_SERVER['REMOTE_ADDR'];

if (!isset($_SESSION['browser'])) {
    if (function_exists('get_browser') && ini_get('browscap')) {
        $browser = get_browser(null, true);
    } else {
        return false;
    }

    $_SESSION['browser'] = $frm->filter($browser, 1, 1);
}

if (function_exists('geoip_record_by_name') && !isset($_SESSION['location'])) {
    $_SESSION['location'] = geoip_record_by_name($ip);
    $_SESSION['location']['country'] = $_SESSION['location']['country_code'];
    $_SESSION['location']['state'] = $_SESSION['location']['region'];
}

$date = date('Y-m-d');
$time = date('H:i:s');

$url_path = pathinfo($_SERVER["REQUEST_URI"]);

if (empty($url_path['basename'])) {
    $url_path['basename'] = 'index.html';
}

$referer = $_SERVER["HTTP_REFERER"];
$referer_path = parse_url($referer);

$sql_stats_visitors = '';

if (!$frm->table_exists('stats_referers')) {
    $frm->sql_query("
        CREATE TABLE wde_stats_referers (
            id int(11) unsigned NOT NULL auto_increment,
            ip varchar(128) NOT NULL default '1',
            referer varchar(128) NOT NULL,
            referer_url varchar(255) NOT NULL,
            social_network tinyint(1) NOT NULL,
            search_engine tinyint(1) NOT NULL,
            search_term varchar(255) NOT NULL,
            date date NOT NULL,
            time time NOT NULL,
            PRIMARY KEY  (id),
            KEY referer (referer),
            KEY search_engine (search_engine),
            KEY search_term (search_term),
            KEY date (date),
            KEY time (time)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
}
if (!$frm->table_exists('stats_visitors')) {
    $frm->sql_query("
        CREATE TABLE wde_stats_visitors (
            id int(11) unsigned NOT NULL auto_increment,
            ip varchar(128) NOT NULL default '1',
            country varchar(2) NOT NULL,
            region varchar(64) NOT NULL,
            city varchar(128) NOT NULL,
            platform varchar(32) NOT NULL,
            is_mobile tinyint(1) default NULL,
            browser varchar(32) NOT NULL,
            browser_version smallint(4) unsigned NOT NULL,
            cookies tinyint(1) default NULL,
            javascript tinyint(1) default NULL,
            java tinyint(1) default NULL,
            css tinyint(1) default NULL,
            date date NOT NULL,
            time time NOT NULL,
            visits bigint(16) unsigned default NULL,
            PRIMARY KEY  (id),
            KEY country (country),
            KEY region (region),
            KEY city (city),
            KEY platform (platform),
            KEY browser (browser),
            KEY date (date),
            KEY time (time),
            KEY visits (visits),
            KEY ip (ip)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
}

if (!$frm->table_exists('stats_visits')) {
    $frm->sql_query("
        CREATE TABLE wde_stats_visits (
            id int(11) unsigned NOT NULL auto_increment,
            ip varchar(128) NOT NULL default '1',
            url varchar(255) NOT NULL,
            date date NOT NULL,
            time time NOT NULL,
            visits bigint(16) unsigned default NULL,
            PRIMARY KEY  (id),
            KEY url (url),
            KEY date (date),
            KEY time (time),
            KEY visits (visits),
            KEY ip (ip)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
}

if (preg_match('/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|philips|phone|playbook|sagem|sharp|sie-|silk|smartphone|sony|symbian|t-mobile|telus|up\.browser|up\.link|vodafone|wap|webos|wireless|xda|xoom|zte)/i', $_SERVER['HTTP_USER_AGENT'])) {
    $is_mobile = 1;
}
if (preg_match('/(facebook.com|twitter.com|linkedin.com|pinterest.com|plus.google.com|deviantart.com|livejournal.com|tagged.com|orkut.com|cafemom.com|ning.com|meetup.com|mylife.com|ask.fm|instagram.com|myspace.com|hi5.com|friendster.com|bebo.com|habbo.com|classmates.com|tagged.com|myyearbook.com|fixter.com|myheritage.com|multiply.com|gaiaonline.com|blackplanet.com|skyrock.com|perfspot.com|zorpia.com|netlog.com|tuenti.com|httpnk.pl|irc-galleria.net|studivz.net|xing.com|renren.com|kaixin001.com|hyves.nl|millatfacebook.com|ibibo.com|sonico.com|wer-kennt-wen.de|nate.com|mixi.jp|iwiw.hu)/i', $referer)) {
    $comming_from_social_net = 1;
}
if (preg_match('/(google.com|bing.com|yahoo.com|startpage.com|duckduckgo.com|altavista.com|ask.com|blekko.com|clusty.com|cuil.com|dogpile.com|dogreatgood.com|entireweb.com|excite.com|faroo.com|gigablast.com|hakia.com|imhalal.com|leapfish.com|lycos.com|monstercrawler.com|http:omgili.com|scrubtheweb.com|searchhippo.com|secretsearchenginelabs.com|spezify.com|stinkyteddy.com|stumpedia.com|archive.org|wolframalpha.com)/i', $referer)) {
    $comming_from_search_engine = 1;
}

// Insert visitor info
if (!isset($_SESSION['stats']['checked'])) {
    $sql_stats_visitors .= "
        INSERT INTO ".$frm->prefix."_stats_visitors
        VALUES (
            NULL,
            '".$ip."',
            '".$_SESSION['location']['country_code']."',
            '".$_SESSION['location']['region']."',
            '".$_SESSION['location']['city']."',
            '".$_SESSION['browser']['platform']."',
            '".$is_mobile."',
            '".$_SESSION['browser']['browser']."',
            '".$_SESSION['browser']['majorver']."',
            '".$_SESSION['browser']['cookies']."',
            '".$_SESSION['browser']['javascript']."',
            '".$_SESSION['browser']['javaapplets']."',
            '".$_SESSION['browser']['cssversion']."',
            '".$date."',
            '".$time."',
            '1'
        );
    ";
} else {
    $sql_stats_visitors .= "
        UPDATE ".$frm->prefix."_stats_visitors
        SET visits = visits + 1
        WHERE ip = '".$ip."'
        AND date = '".$date."';
    ";
}

if (!empty($referer) && !strpos($referer, $frm->site_domain())) {
    if (!empty($comming_from_search_engine)) {
        if (strpos($referer_path['query'], 'q=') || strpos($referer_path['query'], 'query=') || strpos($referer_path['query'], 'p=')) {
            parse_str($referer_path['query'], $q);
            if (!empty($q['q'])) {
                $search_term = $q['q'];
            } elseif (!empty($q['query'])) {
                $search_term = $q['query'];
            } else {
                $search_term = $q['p'];
            }
        }
    }

    $sql_stats_visitors .= "
        INSERT INTO ".$frm->prefix."_stats_referers
        VALUES (
            NULL,
            '".$ip."',
            '".$referer_path['host']."',
            '".$referer."',
            '".$comming_from_social_net."',
            '".$comming_from_search_engine."',
            '".$search_term."',
            '".$date."',
            '".$time."'
        );
    ";
}

if (!isset($_SESSION['stats']['CNT'])) {
    $sql_stats_visitors .= "
        INSERT INTO ".$frm->prefix."_stats_visits
        VALUES (
            NULL,
            '".$ip."',
            '".$url_path['basename']."',
            '".$date."',
            '".$time."',
            '1'
        );
    ";
} else {
    $sql_stats_visitors .= "
        UPDATE ".$frm->prefix."_stats_visits
        SET visits = visits+1
        WHERE ip= '".$ip."'
        AND url = '".$url_path['basename']."'
        AND date = '".$date."';
    ";
}

$frm->sql_query($sql_stats_visitors);

$_SESSION['stats']['CNT']++;
$_SESSION['stats']['checked'] = 1;
