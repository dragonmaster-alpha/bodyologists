<?php


namespace App;

use App\Helper;

require_once 'app/class.helper.php';

class Html
{
    /**
     * Build the dorpdown element for the Categories
     *
     * @param string $name
     * @param string|null $preselected
     * @param string $class
     * @return string
     */
    public static function renderCategoriesDropdown(string $name, string $preselected = null, string $class = ''): string
    {
        $categories = Helper::getCategoriesList();
        $options = '';
        foreach ($categories as $main_category => $subcategories) {
            $options .= '<optgroup label='.strtoupper($main_category).'>' . strtoupper($main_category) . '</optgroup>';

            foreach ($subcategories as $key => $value) {
                $selected = ($preselected === $key) ? ' selected="selected"' : '' ;
                $options .= "<option {$selected} value='{$key}'>" . ucwords($value) . "</option>";
            }
        }

        $selectText = "Select an option";

        if($name == "q") {
            $selectText = "Category";
        }

        return
        "<select name='{$name}' class='{$class}'>'".
        "    <option value='' disabled selected>{$selectText}</option>".
             $options.
        "</select>";
    }

    /**
     * Build and HTML <select> element
     *
     * @param string $name
     * @param array $data
     * @param string|null $preselected
     * @param string $class
     * @param bool $multiple
     * @param string $placeholder
     * @return string
     */
    public static function renderDropdown(string $name, array $data, string $preselected = null, string $class = '', bool $multiple = false, string $placeholder = ''): string
    {
        $options = '';
        $preselectedArray = [];
        $allow_multiple = $multiple ? 'multiple="multiple"' : '';
        if ($multiple) {
            $preselectedArray = explode(', ', $preselected);
            $preselectedArray = str_replace(',/', ', ', $preselectedArray);

            foreach ($preselectedArray as &$preselect) {
                trim($preselect);
            }
        }

        foreach ($data as $datum) {
            if ($multiple) {
                $selected = in_array(trim($datum), $preselectedArray) ? ' selected="selected"' : '' ;
            } else {
                $selected = (trim($preselected) == trim($datum)) ? ' selected="selected"' : '' ;
            }
            $options .= "<option value='{$datum}' {$selected}>{$datum}</option>";
        }
        $placeholderOption = '';
        if ($placeholder != '') {
            $placeholderOption = "    <option value>{$placeholder}</option>";
        }

        return
            "<select name='{$name}' class='{$class}' {$allow_multiple} data-placeholder='Select an option...'>'".
            $placeholderOption.
                $options.
            '</select>';
    }
    /**
     * Build and HTML <select> element using a [key => value] array
     *
     * @param string $name
     * @param array $data
     * @param string|null $preselected
     * @param string $class
     * @param bool $multiple
     * @return string
     */
    public static function renderDropdownWithKeys(string $name, array $data, string $preselected = null, string $class = '', bool $multiple = false): string
    {
        $options = '';
        $allow_multiple = $multiple ? 'multiple="multiple"' : '';

        foreach ($data as $key => $datum) {
            $selected = ((string) $preselected === (string) $key) ? ' selected="selected"' : '' ;
            $options .= "<option value='{$key}' {$selected}>{$datum}</option>";
        }

        return
            "<select name='{$name}' class='{$class}' {$allow_multiple} data-placeholder='Select an option...'>'".
                $options.
            '</select>';
    }

    /**
     * Render an HTML datalist element with given ID and data
     *
     * @param string $name
     * @param array $data
     * @param array $preselected
     * @return string
     */
    public static function renderDatalist(string $name, array $data, $preselected = []): string
    {
        $list = '';
        $preselected = (array) $preselected;

        foreach ($data as $datum) {
            $selected = in_array($datum, $preselected) ? ' selected="selected"' : '' ;
            $list .= "<option value='{$datum}' {$selected}></option>";
        }

        return
            "<datalist id='{$name}'>".
                $list.
            "</datalist>";
    }

    /**
     * Render an hours selector <select> HTML element
     * @param string $name
     * @param string $preselected
     * @param bool $closedOption
     * @param int $from
     * @param int $to
     * @param string $class
     * @return string
     */
    public static function renderHoursDropdown(string $name, string $preselected = null, bool $closedOption = false, int $from = 01, int $to = 24, bool $ampm = false, string $class = ''): string
    {
        $options = [];

        $hours = array_map(
            static function ($item) use ($ampm) {
                $key = (string) $item;
                $value = ($ampm) ? self::formatAMPM($item) : $item ;

                return "$key => $value";
            },
            range($from, $to)
        );

        if ($closedOption) {
            $options['Closed'] = 'Closed';
        }

        foreach (array_values($hours) as $hour)
        {
            [$key, $value] = explode(' => ', $hour);
            $options[$key] = $value;
        }

        return self::renderDropdownWithKeys($name, $options, $preselected, $class);
    }

    /**
     * Given an integer, return it in AM/PM and zero-padded.
     * If it's not an integer returns it untouched.
     *
     * @param $time
     * @return string
     */
    public static function formatAMPM($time): string
    {
        if (!(int) $time && $time != 0) {
            return $time;
        }

        $is_pm = $time >= 12 && $time <= 23;
        $hour = $time > 12 ? $time - 12 : $time;

        return str_pad($hour, 2, '0', STR_PAD_LEFT) . ' ' . ($is_pm ? 'PM' : 'AM');
    }
}