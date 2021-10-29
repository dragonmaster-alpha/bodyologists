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
 *
 * $csv 												= new CSVWriter($data);
 * $csv->headers('test');
 * $csv->output();
 *
 **/

class CSVWriter
{
    public $data = [];
    public $deliminator;

    public function __construct($data, $deliminator = ',')
    {
        if (!is_array($data)) {
            throw new Exception('CSV only accepts data as arrays');
        }
  
        $this->data = $data;
        $this->deliminator = $deliminator;
    }

    public function output()
    {
        foreach ($this->data as $row) {
            $quoted_data = array_map(['App\Formatters\CSVWriter', 'wrap_with_quotes'], $row);
            echo sprintf("%s\n", implode($this->deliminator, $quoted_data));
        }
    }

    public function headers($name = '')
    {
        if (empty($name)) {
            $name = date('Y-m-d-H-i');
        }

        header('Content-Type: application/csv');
        header("Content-disposition: attachment; filename={$name}.csv");
    }
  
    private function wrap_with_quotes($data)
    {
        $data = preg_replace('/"(.+)"/', '""$1""', $data);
        return sprintf('"%s"', $data);
    }
}
