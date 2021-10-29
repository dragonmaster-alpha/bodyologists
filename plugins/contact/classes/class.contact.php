<?php

namespace Plugins\Contact\Classes;

use Kernel\Classes\Format as Format;


class Search extends Format
{
    private $plugin;

    public function __construct()
    {
        $this->plugin = 'contact';
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
