<?php

namespace Plugins\Search\Classes;

use Kernel\Classes\Format as Format;

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

class Search extends Format
{
    private $plugin;

    public function __construct()
    {
        $this->plugin = 'search';
    }

    private function load(array $data = [])
    {
        try {
            if (count($data) == 0) {
                throw new Exception('Submitted data is empty', 1);
            }

            $return = $this->filter($data, 1);

            return $return;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
}
