<?php

namespace App\Formatters;

ini_set("auto_detect_line_endings", true);

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
 * $read                                                = new CSVReader('read_it.csv', 2000);
 * $csv_data_array                                      = $read->read_csv();
 **/

class CSVReader
{
    public $file_path;
    public $file_exists = false;
    public $length_to_read;
    public $out_put = [];
    
    public function __construct($file = '', $length_to_read = 1000)
    {
        if (file_exists($file)) {
            $this->file_exists = true;
            $this->file_path = $file;
            $this->length_to_read = $length_to_read;
        }
    }

    public function read_csv()
    {
        $file_handler = null;
        $data_array = [];
        $file_handler = $this->open_csv_file();

        while ($data_array = fgetcsv($file_handler, $this->length_to_read, ',')) {
            $this->out_put[] = $data_array;
        }
        return $this->out_put;
    }

    public function open_csv_file()
    {
        $file_handler = null;
        
        if ($this->file_exists) {
            $file_handler = fopen($this->file_path, 'r')    ;
        }

        return $file_handler;
    }
}
