<?php

namespace App;

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

class Breadcrumbs
{
    public $crumbs = [];

    public function __construct(array $data = [])
    {
        $this->set('/', _HOME);

        if (isset($_REQUEST['plugin']) && $_REQUEST['plugin'] != 'pages') {
            $this->set($_REQUEST['plugin'], ucwords($_REQUEST['plugin']));

            if (!empty($_REQUEST['addon'])) {
                $this->set($_REQUEST['plugin'].'/'.$_REQUEST['addon'], ucwords($_REQUEST['addon']));
            }
            if (!empty($_REQUEST['file'])) {
                $this->set($_REQUEST['plugin'].'/'.(!empty($_REQUEST['file']) ? $_REQUEST['file'].'/' : '').$_REQUEST['file'], ucwords($_REQUEST['file']));
            }
            if (!empty($_REQUEST['op'])) {
                $this->set($_REQUEST['plugin'].'/'.(!empty($_REQUEST['addon']) ? $_REQUEST['addon'].'/' : '').(!empty($_REQUEST['file']) ? $_REQUEST['file'].'/' : '').$_REQUEST['op'], ucwords($_REQUEST['op']));
            }
        }

        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function set($url = '', $name = '')
    {
        if (!empty($name) && !empty($url)) {
            $this->crumbs[] = [
                'url' => $url,
                'name' => $name
            ];
        }
    }

    public function get()
    {
        $return = '<ol class="breadcrumb" vocab="http://schema.org/" typeof="BreadcrumbList">';

        foreach ($this->crumbs as $crumb) {
            $count++;
            $return .= '<li class="breadcrumb-item" property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" href="'.$crumb['url'].'">'.$crumb['name'].'</a><meta property="position" content="'.$count.'" /></li>';
        }

        $return .= '</ol>';

        return $return;
    }

    public function schema()
    {
        $return['@context'] = 'http://schema.org';
        $return['@type'] = 'BreadcrumbList';

        foreach ($this->crumbs as $key => $crumb) {
            $count++;
            $return['itemListElement'][$key]['@type'] = 'ListItem';
            $return['itemListElement'][$key]['position'] = $count;
            $return['itemListElement'][$key]['item'] = [
                '@id' => $crumb['url'],
                'name' => $crumb['name']
            ];
        }

        return '<script type="application/ld+json">'.json_encode($return).'</script>';
    }
}
