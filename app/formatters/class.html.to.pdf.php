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
 * $obj = new HTML2PDF('http://localhost/phpclasses/sql2pdfreport/first_test.html');  //change this according to your URL
 * $obj->get_pdf();
 *
 **/

class HTML2PDF
{
    public $url;
    public $pdf_version;
    private $pdf_width = 850;
    private $remote_app = 'http://services.phpresgroup.org/pdf/public_html/html2ps.php';
    private $debug = false;

    public function __construct($url = '', $pdf_version = '1.3')
    {
        $this->url = $url;
        $this->pdf_version = $pdf_version;
        $this->pdf_width = $pdf_width;
        $this->remote_app = $remote_app;
    }

    public function get_pdf()
    {
        $pdf_file_name = basename($this->url).'.pdf';

        header("Content-type: application/pdf");
        header("Content-Disposition: attachment; filename=".$pdf_file_name);
        return $this->generate_pdf();
    }
          
    public function generate_pdf()
    {
        $watermarkhtml = $_SERVER['HTTP_HOST'];
        
        $string = [
            'process_mode' => 'single',
            'URL' => urlencode($this->url),
            'pixels' => $this->pdf_width,
            'scalepoints' => 1,
            'renderimages' => 1,
            'renderlinks' => 1,
            'renderfields' => 1,
            'media' => 'Letter',
            'cssmedia' => 'screen',
            'leftmargin' => 10,
            'rightmargin' => 10,
            'topmargin' => 15,
            'bottommargin' => 15,
            'encoding' => '',
            'headerhtml' => '',
            'footerhtml' => '',
            'watermarkhtml' => $watermarkhtml,
            '&method' => 'fpdf',
            'pdfversion' => $this->pdf_version,
            'output' => 0,
            'convert' => 'Convert+File'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->remote_app);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $string));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($this->debug) {
            echo '<pre>';
            print_r($response);
            echo '</pre>';
        }

        return $response;
    }
}
