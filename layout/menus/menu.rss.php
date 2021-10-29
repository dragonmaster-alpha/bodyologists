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

require_once($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/kernel/classes/class.RSSReader.php');

$count = 1;
foreach ($frm->sql_get('rss', 'id, title, url, display_description, description_words', ['active' => 1, 'date DESC']) as $data) {
    $data['count'] = $count;
    $rss_feed[] = $frm->filter($data, 1);
    ;
    $count++;
}

foreach ($rss_feed as $rss) {?>
<aside>
	<h3><?=$rss['title']?></h3>
	<ul class="nav nav-tabs nav-stacked">
		<?php
        $xml_parser = xml_parser_create();
        $rss_parser = new RSSParser();

        if ($rss['display_description']) {
            $rss_parser->haveText = true;
            $rss_parser->textQty = $rss['description_words'];
        }
        $rss_parser->parse_results($xml_parser, $rss_parser, $rss['url']);
        ?>
	</ul>
</aside>
<?php }?>