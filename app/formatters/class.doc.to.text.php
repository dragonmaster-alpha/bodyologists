<?php

namespace App\Formatters;

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

/**
 * Usage
 *
 * $docObj                                              = new Doc2Txt("test.docx");  // docx converter
 * $docObj                                              = new Doc2Txt("test.doc"); // doc converter
 * $txt                                                 = $docObj->convertToText();
 * echo $txt;
 *
 **/

class DOC2TXT
{
    public $filename;
     
    public function __construct($filename)
    {
        $this->filename = $filename;
    }
     
    public function convertToText()
    {
        if (isset($this->filename) && !file_exists($this->filename)) {
            return 'File Not exists';
        }
         
        $fileArray = pathinfo($this->filename);
        $file_ext = $fileArray['extension'];

        if ($file_ext == "doc" || $file_ext == "docx") {
            if ($file_ext == "doc") {
                return $this->read_doc();
            }
             
            return $this->read_docx();
        }
         
        return "Invalid File Type";
    }
     
    private function read_doc()
    {
        $fileHandle = fopen($this->filename, "r");
        $line = @fread($fileHandle, filesize($this->filename));
        $lines = explode(chr(0x0D), $line);
        $outtext = '';

        foreach ($lines as $thisline) {
            $pos = strpos($thisline, chr(0x00));

            if ($pos === false || strlen($thisline) != 0) {
                $outtext .= $thisline." ";
            }
        }

        $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/", "", $outtext);
        return $outtext;
    }

    private function read_docx()
    {
        $striped_content = '';
        $content = '';

        $zip = zip_open($this->filename);

        if (!$zip || is_numeric($zip)) {
            return false;
        }

        while ($zip_entry = zip_read($zip)) {
            if (zip_entry_open($zip, $zip_entry) == false) {
                continue;
            }
            if (zip_entry_name($zip_entry) != 'word/document.xml') {
                continue;
            }

            $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
            zip_entry_close($zip_entry);
        }

        zip_close($zip);
        $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
        $content = str_replace('</w:r></w:p>', "\r\n", $content);
        $striped_content = strip_tags($content);

        return $striped_content;
    }
}
