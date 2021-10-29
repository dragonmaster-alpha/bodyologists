<?php

namespace App\Media;

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

class mp3
{
    public $aTV23 = ['TIT2','TALB','TPE1','TPE2','TRCK','TYER','TLEN','USLT','TPOS','TCON','TENC','TCOP','TPUB','TOPE','WXXX','COMM','TCOM'];
    public $aTV23t = ['title','album','author','album author','track','year','length','lyric','desc','genre','encoded','copyright','publisher','original artist','url','comments','composer'];
    public $aTV22 = ['TT2','TAL','TP1','TRK','TYE','TLE','ULT'];
    public $aTV22t = ['title','album','author','track','year','length','lyric'];

    // constructor
    public function __construct()
    {
    }

    // functions
    public function get_meta_info($sFilepath)
    {
        $iFSize = filesize($sFilepath);
        $vFD = fopen($sFilepath, 'r');
        $sSrc = fread($vFD, $iFSize);
        fclose($vFD);

        if (substr($sSrc, 0, 3) == 'ID3') {
            $aInfo['FileName'] = $sFilepath;
            $aInfo['Version'] = hexdec(bin2hex(substr($sSrc, 3, 1))).'.'.hexdec(bin2hex(substr($sSrc, 4, 1)));
        }

        if ($aInfo['Version'] == '4.0' || $aInfo['Version'] == '3.0') {
            for ($i = 0; $i < count($this->aTV23); $i++) {
                if (strpos($sSrc, $this->aTV23[$i].chr(0)) != false) {
                    $s = '';
                    $iPos = strpos($sSrc, $this->aTV23[$i].chr(0));
                    $iLen = hexdec(bin2hex(substr($sSrc, ($iPos + 5), 3)));

                    $data = substr($sSrc, $iPos, 9 + $iLen);

                    for ($a = 0; $a < strlen($data); $a++) {
                        $char = substr($data, $a, 1);

                        if ($char >= ' ' && $char <= '~') {
                            $s .= $char;
                        }
                    }
                    if (substr($s, 0, 4) == $this->aTV23[$i]) {
                        $iSL = 4;

                        if ($this->aTV23[$i] == 'USLT') {
                            $iSL = 7;
                        } elseif ($this->aTV23[$i] == 'TALB') {
                            $iSL = 5;
                        } elseif ($this->aTV23[$i] == 'TENC') {
                            $iSL = 6;
                        }

                        $aInfo[$this->aTV23t[$i]] = substr($s, $iSL);
                    }
                }
            }
        }

        if ($aInfo['Version'] == '2.0') {
            for ($i = 0; $i < count($this->aTV22); $i++) {
                if (strpos($sSrc, $this->aTV22[$i].chr(0)) != false) {
                    $s = '';
                    $iPos = strpos($sSrc, $this->aTV22[$i].chr(0));
                    $iLen = hexdec(bin2hex(substr($sSrc, ($iPos + 3), 3)));

                    $data = substr($sSrc, $iPos, 6 + $iLen);

                    for ($a = 0; $a < strlen($data); $a++) {
                        $char = substr($data, $a, 1);
                        if ($char >= ' ' && $char <= '~') {
                            $s .= $char;
                        }
                    }

                    if (substr($s, 0, 3) == $this->aTV22[$i]) {
                        $iSL = 3;

                        if ($this->aTV22[$i] == 'ULT') {
                            $iSL = 6;
                        }

                        $aInfo[$this->aTV22t[$i]] = substr($s, $iSL);
                    }
                }
            }
        }
        
        return $aInfo;
    }
}
