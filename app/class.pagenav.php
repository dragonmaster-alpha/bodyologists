<?php

namespace App;

/**
 * @author
 * Web Design Enterprise
 * Phone: 786.234.6361
 * Website: www.webdesignenterprise.com
 * E-mail: info@webdesignenterprise.com
 *
 * @copyright
 * This work is licensed under the Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 United States License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/3.0/legalcode
 *
 * Be aware, violating this license agreement could result in the prosecution and punishment of the infractor.
 *
 * © 2002-2009 Web Design Enterprise Corp. All rights reserved.
 */

class PageNav
{
    public $total;
    public $per_page;
    public $current;
    public $url;

    /**
     * PageNav::__construct()
     *
     * @param integer $total
     * @param integer $per_page
     * @param integer $current
     * @param string $start_name
     * @param string $extra_arg
     * @return
     */
    public function __construct($total = 0, $per_page = 0, $current = 0, $start_name = 'start', $extra_arg = '')
    {
        $this->total = (int) $total;
        $this->per_page = (int) $per_page;
        $this->current = (int) $current;
        
        if (isset($_REQUEST['plugin'])) {
            $_format[] = $_REQUEST['plugin'];

            if (!empty($_REQUEST['addon'])) {
                $_format[] = $_REQUEST['addon'];
            }
            if (!empty($_REQUEST['file'])) {
                $_format[] = $_REQUEST['file'];
            }
            if (!empty($_REQUEST['op'])) {
                $_format[] = $_REQUEST['op'];
            }
            if (!empty($_REQUEST['url'])) {
                $_format[] = $this->cleanQueryString($_REQUEST['url']);
            }
        }

        $this->url = implode('/', $_format).'?'.(!empty($extra_arg) ? $extra_arg : '').$start_name.'=';
    }

    /**
     * PageNav::renderNav()
     *
     * @param integer $offset
     * @return
     */
    public function renderNav($offset = 3)
    {
        if ($this->total <= $this->per_page) {
            return $return;
        }

        $total_pages = @ceil($this->total / $this->per_page);

        if ($total_pages > 1) {
            $prev = $this->current - $this->per_page;

            if ($prev >= 0) {
                $return .= '<li class="page-item"><a class="page-link" aria-label="Previous" href="'.$this->url.$prev.'"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>';
            }

            $counter = 1;
            $current_page = (int) floor(($this->current + $this->per_page) / $this->per_page);

            while ($counter <= $total_pages) {
                if ($counter == $current_page) {
                    $return .= '<li class="page-item active"><span class="page-link">'.$counter.'</span></li>';
                } elseif (($counter > $current_page - $offset && $counter < $current_page + $offset) || $counter == 1 || $counter == $total_pages) {
                    if ($counter == $total_pages && $current_page < $total_pages - $offset) {
                        $return .= '<li class="page-item"><span>...</span></li>';
                    }

                    $return .= '<li class="page-item"><a class="page-link" href="'.$this->url.(($counter - 1) * $this->per_page).'">'.$counter.'</a></li>';

                    if ($counter == 1 && $current_page > 1 + $offset) {
                        $return .= '<li class="page-item"><span>...</span></li>';
                    }
                }
                $counter++;
            }

            $next = $this->current + $this->per_page;

            if ($this->total > $next) {
                $return .= '<li class="page-item"><a class="page-link" aria-label="Next" href="'.$this->url.$next.'"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a></li>';
            }
        }

        return $return;
    }

    /**
     * PageNav::renderSelect()
     *
     * @param bool $show_button
     * @return
     */
    public function renderSelect($show_button = false)
    {
        if ($this->total < $this->per_page) {
            return;
        }

        $total_pages = ceil($this->total / $this->per_page);
        
        if ($total_pages > 1) {
            $return = '<form name="pagenavform">';
            $return .= 'Display next: <select name="pagenavselect" onchange="location=this.options[this.options.selectedIndex].value;">';
            $counter = 1;
            $current_page = (int) floor(($this->current + $this->per_page) / $this->per_page);

            while ($counter <= $total_pages) {
                if ($counter == $current_page) {
                    $return .= '<option value="'.$this->url.(($counter - 1) * $this->per_page).'" selected="selected">'.$counter.'</option>';
                } else {
                    $return .= '<option value="'.$this->url.(($counter - 1) * $this->per_page).'">'.$counter.'</option>';
                }
                $counter++;
            }

            $return .= '</select>';

            if ($show_button) {
                $return .= '&nbsp;<input type="submit" value=" GO " />';
            }

            $return .= '</form>';
        }

        return $return;
    }

    public function cleanQueryString($url = '', $key = 'start')
    {
        return str_replace('?', '', preg_replace('/(&|(?<=\?))'.$key.'=.*?(?=&|$)/', '', $url));
    }
}
