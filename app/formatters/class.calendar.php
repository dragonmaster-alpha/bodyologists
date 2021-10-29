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

class Calendar
{
    public $date;
    public $year;
    public $month;
    public $day;
    
    public $week_start_on = false;
    public $week_start = 7;// sunday
    
    public $link_days = true;
    public $link_to;
    public $formatted_link_to;
    
    public $mark_today = true;
    public $today_date_class = 'today';
    
    public $mark_selected = true;
    public $selected_date_class = 'selected';
    
    public $mark_passed = true;
    public $passed_date_class = 'passed';
    
    public $highlighted_dates;
    public $default_highlighted_class = 'highlighted';
    
    public function __construct($date = null, $year = null, $month = null)
    {
        $self = htmlspecialchars($_SERVER['PHP_SELF']);
        $this->link_to = $self;
        
        if (is_null($year) || is_null($month)) {
            if (!is_null($date)) {
                $this->date = date("Y-m-d", strtotime($date)); // strtotime the submitted date to ensure correct format
            } else {
                $this->date = date("Y-m-d"); // no date submitted, use today's date
            }
            $this->set_date_parts_from_date($this->date);
        } else {
            $this->year = $year;
            $this->month = str_pad($month, 2, '0', STR_PAD_LEFT);
        }
    }
    
    public function set_date_parts_from_date($date)
    {
        $this->year = date("Y", strtotime($date));
        $this->month = date("m", strtotime($date));
        $this->day = date("d", strtotime($date));
    }
    
    public function day_of_week($date)
    {
        $day_of_week = date("N", $date);
        if (!is_numeric($day_of_week)) {
            $day_of_week = date("w", $date);
            if ($day_of_week == 0) {
                $day_of_week = 7;
            }
        }
        return $day_of_week;
    }
    
    public function output_calendar($year = null, $month = null, $calendar_class = 'calendar')
    {
        if ($this->week_start_on !== false) {
            echo "The property week_start_on is replaced due to a bug present in version before 2.6. of this class! Use the property week_start instead!";
            exit;
        }
        //--------------------- override class methods if values passed directly
        $year = (is_null($year))? $this->year : $year;
        $month = (is_null($month))? $this->month : str_pad($month, 2, '0', STR_PAD_LEFT);
        //------------------------------------------- create first date of month
        $month_start_date = strtotime($year."-".$month."-01");
        //------------------------- first day of month falls on what day of week
        $first_day_falls_on = $this->day_of_week($month_start_date);
        //----------------------------------------- find number of days in month
        $days_in_month = date("t", $month_start_date);
        //-------------------------------------------- create last date of month
        $month_end_date = strtotime($year."-".$month."-".$days_in_month);
        //----------------------- calc offset to find number of cells to prepend
        $start_week_offset = $first_day_falls_on - $this->week_start;
        $prepend = ($start_week_offset < 0) ? 7 - abs($start_week_offset) : $first_day_falls_on - $this->week_start;
        //-------------------------- last day of month falls on what day of week
        $last_day_falls_on = $this->day_of_week($month_end_date);
        //------------------------------------------------- start table, caption
        $output = "<table class=\"".$calendar_class."\">\n";
        $output .= "<caption>".ucfirst(strftime("%B %Y", $month_start_date))."</caption>\n";
        $col = '';
        $th = '';
        for ($i = 1, $j = $this->week_start, $t = (3 + $this->week_start) * 86400; $i <= 7; $i++, $j++, $t += 86400) {
            $localized_day_name = gmstrftime('%A', $t);
            $col .= "<col class=\"".strtolower($localized_day_name)."\" />\n";
            $th .= "\t<th title=\"".ucfirst($localized_day_name)."\">".strtoupper($localized_day_name{0})."</th>\n";
            $j = ($j == 7) ? 0 : $j;
        }
        //------------------------------------------------------- markup columns
        $output .= $col;
        //----------------------------------------------------------- table head
        $output .= "<thead>\n";
        $output .= "<tr>\n";
        $output .= $th;
        $output .= "</tr>\n";
        $output .= "</thead>\n";
        //---------------------------------------------------------- start tbody
        $output .= "<tbody>\n";
        $output .= "<tr>\n";
        //---------------------------------------------- initialize week counter
        $weeks = 1;
        //--------------------------------------------------- pad start of month
        
        //------------------------------------ adjust for week start on saturday
        for ($i = 1;$i <= $prepend;$i++) {
            $output .= "\t<td class=\"pad\">&nbsp;</td>\n";
        }
        //--------------------------------------------------- loop days of month
        for ($day = 1, $cell = $prepend + 1; $day <= $days_in_month; $day++,$cell++) {
            /*
            if this is first cell and not also the first day, end previous row
            */
            if ($cell == 1 && $day != 1) {
                $output .= "<tr>\n";
            }
            //-------------- zero pad day and create date string for comparisons
            $day = str_pad($day, 2, '0', STR_PAD_LEFT);
            $day_date = $year."-".$month."-".$day;
            
            //-------------------------- compare day and add classes for matches
            if ($this->mark_today == true && $day_date == date("Y-m-d")) {
                $classes[] = $this->today_date_class;
            }
            
            if ($this->mark_selected == true && $day_date == $this->date) {
                $classes[] = $this->selected_date_class;
            }
            
            if ($this->mark_passed == true && $day_date < date("Y-m-d")) {
                $classes[] = $this->passed_date_class;
            }
            
            if (is_array($this->highlighted_dates)) {
                if (in_array($day_date, $this->highlighted_dates)) {
                    $classes[] = $this->default_highlighted_class;
                }
            }
            
            //----------------- loop matching class conditions, format as string
            if (isset($classes)) {
                $day_class = ' class="';
                foreach ($classes as $value) {
                    $day_class .= $value." ";
                }
                $day_class = substr($day_class, 0, -1).'"';
            } else {
                $day_class = '';
            }
            
            //---------------------------------- start table cell, apply classes
            $output .= "\t<td".$day_class." title=\"".ucwords(strftime("%A, %B %e, %Y", strtotime($day_date)))."\">";
            
            //----------------------------------------- unset to keep loop clean
            unset($day_class, $classes);
            
            //-------------------------------------- conditional, start link tag
            switch ($this->link_days) {
                case 0:
                    $output .= $day;
                break;
                
                case 1:
                    if (empty($this->formatted_link_to)) {
                        $output .= "<a href=\"".$this->link_to."?date=".$day_date."\">".$day."</a>";
                    } else {
                        $output .= "<a href=\"".strftime($this->formatted_link_to, strtotime($day_date))."\">".$day."</a>";
                    }
                break;
                
                case 2:
                    if (is_array($this->highlighted_dates)) {
                        if (in_array($day_date, $this->highlighted_dates)) {
                            if (empty($this->formatted_link_to)) {
                                $output .= "<a href=\"".$this->link_to."?date=".$day_date."\">";
                            } else {
                                $output .= "<a href=\"".strftime($this->formatted_link_to, strtotime($day_date))."\">";
                            }
                        }
                    }
                    $output .= $day;
                    if (is_array($this->highlighted_dates)) {
                        if (in_array($day_date, $this->highlighted_dates)) {
                            if (empty($this->formatted_link_to)) {
                                $output .= "</a>";
                            } else {
                                $output .= "</a>";
                            }
                        }
                    }
                break;
            }
            //------------------------------------------------- close table cell
            $output .= "</td>\n";
            //------- if this is the last cell, end the row and reset cell count
            if ($cell == 7) {
                $output .= "</tr>\n";
                $cell = 0;
            }
        }
        //----------------------------------------------------- pad end of month
        if ($cell > 1) {
            for ($i = $cell;$i <= 7;$i++) {
                $output .= "\t<td class=\"pad\">&nbsp;</td>\n";
            }
            $output .= "</tr>\n";
        }
        //--------------------------------------------- close last row and table
        $output .= "</tbody>\n";
        $output .= "</table>\n";
        //--------------------------------------------------------------- return
        return $output;
    }
}
