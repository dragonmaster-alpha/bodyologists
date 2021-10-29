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

class Vcard
{
    public $properties;
    public $filename;

    public function __construct()
    {
    }

    public function encode($string)
    {
        return $this->escape(quoted_printable_encode($string));
    }

    public function escape($string)
    {
        return str_replace(';', '\;', $string);
    }

    // taken from PHP documentation comments
    public function quoted_printable_encode($input, $line_max = 76)
    {
        $hex = ['0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F'];
        $lines = preg_split("/(?:\r\n|\r|\n)/", $input);
        $eol = "\r\n";
        $linebreak = "=0D=0A";
        $escape = "=";
        $output = '';

        for ($j = 0;$j < count($lines);$j++) {
            $line = $lines[$j];
            $linlen = strlen($line);
            $newline = '';

            for ($i = 0; $i < $linlen; $i++) {
                $c = substr($line, $i, 1);
                $dec = ord($c);

                if (($dec == 32) && ($i == ($linlen - 1))) { // convert space at eol only
                    $c = "=20";
                } elseif (($dec == 61) || ($dec < 32) || ($dec > 126)) {
                    $h2 = floor($dec / 16);
                    $h1 = floor($dec % 16);
                    $c = $escape.$hex["$h2"].$hex["$h1"];
                }

                if ((strlen($newline) + strlen($c)) >= $line_max) {
                    $output .= $newline.$escape.$eol; // soft line break; " =\r\n" is okay
                    $newline = "    ";
                }

                $newline .= $c;
            }

            $output .= $newline;

            if ($j < count($lines) - 1) {
                $output .= $linebreak;
            }
        }
        return trim($output);
    }

    public function setPhoneNumber($number, $type = '')
    {
        // type may be PREF | WORK | HOME | VOICE | FAX | MSG | CELL | PAGER | BBS | CAR | MODEM | ISDN | VIDEO or any senseful combination, e.g. "PREF;WORK;VOICE"
        $key = "TEL";

        if ($type != '') {
            $key .= ";".$type;
        }

        $key .= ";ENCODING=QUOTED-PRINTABLE";
        $this->properties[$key] = quoted_printable_encode($number);
    }
    
    // UNTESTED !!!
    public function setPhoto($type, $photo)
    { // $type = "GIF" | "JPEG"
        $this->properties["PHOTO;TYPE=$type;ENCODING=BASE64"] = base64_encode($photo);
    }
    
    public function setFormattedName($name)
    {
        $this->properties["FN"] = quoted_printable_encode($name);
    }
    
    public function setName($family = '', $first = '', $additional = '', $prefix = '', $suffix = '')
    {
        $this->properties["N"] = "$family;$first;$additional;$prefix;$suffix";
        $this->filename = "$first%20$family.vcf";
        if ($this->properties["FN"] == '') {
            $this->setFormattedName(trim("$prefix $first $additional $family $suffix"));
        }
    }
    
    public function setBirthday($date)
    {
        $this->properties["BDAY"] = $date;
    }
    
    public function setAddress($postoffice = '', $extended = '', $street = '', $city = '', $region = '', $zip = '', $country = '', $type = "WORK;PARCEL;POSTAL")
    {
        // $type may be DOM | INTL | POSTAL | PARCEL | HOME | WORK or any combination of these: e.g. "WORK;PARCEL;POSTAL"
        $key = "ADR";
        if ($type != '') {
            $key .= ";$type";
        }

        $key .= ";ENCODING=QUOTED-PRINTABLE";

        $this->properties[$key] = $this->encode($name).';'.$this->encode($extended).';'.$this->encode($street).';'.$this->encode($city).';'.$this->encode($region).';'.$this->encode($zip).';'.$this->encode($country);
        
        if ($this->properties["LABEL;$type;ENCODING=QUOTED-PRINTABLE"] == '') {
            //$this->setLabel($postoffice, $extended, $street, $city, $region, $zip, $country, $type);
        }
    }
    
    public function setLabel($postoffice = '', $extended = '', $street = '', $city = '', $region = '', $zip = '', $country = '', $type = "HOME;POSTAL")
    {
        $label = '';
        if ($postoffice != '') {
            $label .= "$postoffice\r\n";
        }
        if ($extended != '') {
            $label .= "$extended\r\n";
        }
        if ($street != '') {
            $label .= "$street\r\n";
        }
        if ($zip != '') {
            $label .= "$zip ";
        }
        if ($city != '') {
            $label .= "$city\r\n";
        }
        if ($region != '') {
            $label .= "$region\r\n";
        }
        if ($country != '') {
            $country .= "$country\r\n";
        }
        
        $this->properties["LABEL;$type;ENCODING=QUOTED-PRINTABLE"] = quoted_printable_encode($label);
    }
    
    public function setEmail($address)
    {
        $this->properties["EMAIL;INTERNET"] = $address;
    }
    
    public function setNote($note)
    {
        $this->properties["NOTE;ENCODING=QUOTED-PRINTABLE"] = quoted_printable_encode($note);
    }

    // $type may be WORK | HOME
    public function setURL($url, $type = '')
    {
        $key = "URL";

        if ($type != '') {
            $key .= ";$type";
        }

        $this->properties[$key] = $url;
    }
    
    public function getVCard()
    {
        $text = "BEGIN:VCARD\r\n";
        $text .= "VERSION:2.1\r\n";

        foreach ($this->properties as $key => $value) {
            $text .= "$key:$value\r\n";
        }

        $text .= "REV:".date("Y-m-d")."T".date("H:i:s")."Z\r\n";
        $text .= "MAILER:PHP vCard created by Network Strategics Software\r\n";
        $text .= "END:VCARD\r\n";
        
        return $text;
    }
    
    public function getFileName()
    {
        return $this->filename;
    }
}

//  USAGE EXAMPLE

//$v = new vCard();
//
//$v->setPhoneNumber("+49 23 456789", "PREF;HOME;VOICE");
//$v->setName("Mustermann", "Thomas", '', "Herr");
//$v->setBirthday("1960-07-31");
//$v->setAddress('', '', "Musterstrasse 20", "Musterstadt", '', "98765", "Deutschland");
//$v->setEmail("thomas.mustermann@thomas-mustermann.de");
//$v->setNote("You can take some notes here.\r\nMultiple lines are supported via \\r\\n.");
//$v->setURL("http://www.thomas-mustermann.de", "WORK");
//
//$output = $v->getVCard();
//$filename = $v->getFileName();
//
//Header("Content-Disposition: attachment; filename=$filename");
//Header("Content-Length: ".strlen($output));
//Header("Connection: close");
//Header("Content-Type: text/x-vCard; name=$filename");
//
//echo $output;
