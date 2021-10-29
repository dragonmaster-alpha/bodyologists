<?php

namespace App\Xml;

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

class RSSReader
{
    public $title = '';
    public $link = '';
    public $description = '';
    public $haveText = null;
    public $text_qty = 20;
    public $posts_qty = 10;
    public $count_posts = 0;
    public $inside_item = false;

    /**
     * start elements for reading rss
     * @param  string $parser
     * @param  string $name
     * @param  string $attrs
     */
    public function startElement($parser, $name, $attrs = '')
    {
        global $current_tag;
        $current_tag = $name;

        if ($current_tag == "ITEM") {
            $this->inside_item = true;
        }
    }
    /**
     * closes elements fro reading rss
     * @param  string $parser
     * @param  string $tagName
     * @param  string $attrs
     */
    public function endElement($parser, $tagName, $attrs = '')
    {
        global $current_tag, $frm;

        if ($tagName == "ITEM" && $this->count_posts < $this->posts_qty) {
            printf('<div>');
            printf('<p><a href="%s" title="'.htmlspecialchars(trim($this->title)).'" target="_blank">%s</a></p>', trim($this->link), trim($this->title));
            if (isset($this->haveText)) {
                printf('<p style="padding: 0 20px; color: #FFF;">%s</p>', $frm->reduceText($this->description, $this->text_qty));
            }
            printf('</div>');

            $this->title = '';
            $this->description = '';
            $this->link = '';
            $this->inside_item = false;

            $this->count_posts++;
        }

        $count_posts++;
    }
    /**
     * parses rss data
     * @param  string $parser
     * @param  string $data
     * @return object
     */
    public function characterData($parser, $data)
    {
        global $current_tag;

        if ($this->inside_item) {
            switch ($current_tag) {
                case "TITLE":

                    $this->title .= $data;

                break;

                case "DESCRIPTION":

                    $this->description .= $data;

                break;

                case "LINK":

                    $this->link .= $data;

                break;

                default:
                break;
            }
        }
    }
    /**
     * parse rss results
     * @param  string $xml_parser
     * @param  string $rss_parser
     * @param  string $file
     * @return string
     */
    public function parse_results($xml_parser, $rss_parser, $file)
    {
        xml_set_object($xml_parser, $rss_parser);
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler($xml_parser, "characterData");

        $fp = fopen("$file", "r") or die("Error reading XML file, $file");

        while ($data = fread($fp, 4096)) {
            xml_parse($xml_parser, $data, feof($fp)) or die(sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser)));
        }

        fclose($fp);
        xml_parser_free($xml_parser);
    }
}
