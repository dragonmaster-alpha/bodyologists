<?php

namespace App\Formatters;

use App\Format as Format;

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

class Addresses extends Format
{
    public $address;

    public function __construct($address = '')
    {
        try {
            if (empty($address)) {
                throw new Exception("You must submit an address to parse", 1);
            }

            $this->address = $this->format_address($address);
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function verity_address()
    {
        try {
            $parsed_address = $this->parse_address_google();
        
            if (!is_array($parsed_address)) {
                return false;
            }
            
            $result_address = $this->format_address($parsed_address['street_number'].' '.$parsed_address['address'].' '.$parsed_address['city'].' '.$parsed_address['state'].' '.$parsed_address['zip']);
        
            if ($this->address == $result_address) {
                return true;
            }
            
            return $parsed_address;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function get_zipcode_info()
    {
        try {
            $parsed_zipcode = $this->parse_zipcode_google();

            if (!is_array($parsed_zipcode)) {
                return false;
            }
            
            return $parsed_zipcode;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    public function check_pobox()
    {
        if (preg_match("/[P|p]*(OST|ost)*\.*\s*[O|o|0]*(ffice|FFICE)*\.*\s*[B|b][O|o|0][X|x]/", $this->address)) {
            return true;
        }

        return false;
    }
    
    private function parse_address_google()
    {
        try {
            $url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address='.urlencode($this->address);
            $results = json_decode(file_get_contents($url), true);

            $parts = [
                'street_number' => ['street_number'],
                'address' => ['route'],
                'city' => ['locality'],
                'state' => ['administrative_area_level_1'],
                'zip' => ['postal_code'],
                'country' => ['country']
            ];

            if (!empty($results['results'][0]['address_components'])) {
                $ac = $results['results'][0]['address_components'];

                foreach ($parts as $need => &$types) {
                    foreach ($ac as &$a) {
                        if (in_array($a['types'][0], $types)) {
                            $address_out[$need] = $a['short_name'];
                        } elseif (empty($address_out[$need])) {
                            $address_out[$need] = '';
                        }
                    }
                }

                return $address_out;
            }
            
            return false;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }

    private function parse_zipcode_google()
    {
        try {
            $url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address='.urlencode($this->address);
            $results = json_decode(file_get_contents($url), 1);
            $parts = [
                'city' => ['locality'],
                'state' => ['administrative_area_level_1'],
                'zip' => ['postal_code'],
                'country' => ['country']
            ];

            if (!empty($results['results'][0]['address_components'])) {
                $ac = $results['results'][0]['address_components'];

                foreach ($parts as $need => &$types) {
                    foreach ($ac as &$a) {
                        if (in_array($a['types'][0], $types)) {
                            $address_out[$need] = $a['short_name'];
                        } elseif (empty($address_out[$need])) {
                            $address_out[$need] = '';
                        }
                    }
                }

                return $address_out;
            }

            return false;
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }
    }
    
    private function format_address($address)
    {
        $address = strtolower($address);
        $street_names = ['aly' => 'alley','anx' => 'annex','arc' => 'arcade','ave' => 'avenue','byu' => 'bayoo','bch' => 'beach','bnd' => 'bend','blf' => 'bluff','blfs' => 'bluffs','btm' => 'bottom','blvd' => 'boulevard','br' => 'branch','brg' => 'bridge','brk' => 'brook','brks' => 'brooks','bg' => 'burg','bgs' => 'burgs','byp' => 'bypass','cp' => 'camp','cyn' => 'canyon','cpe' => 'cape','cswy' => 'causeway','ctr' => 'center','ctrs' => 'centers','cir' => 'circle','cirs' => 'circles','clf' => 'cliff','clfs' => 'cliffs','clb' => 'club','cmn' => 'common','cor' => 'corner','cors' => 'corners','crse' => 'course','ct' => 'court','cts' => 'courts','cv' => 'cove','cvs' => 'coves','crk' => 'creek','cres' => 'crescent','crst' => 'crest','xing' => 'crossing','xrd' => 'crossroad','curv' => 'curve','dl' => 'dale','dm' => 'dam','dv' => 'divide','dr' => 'drive','drs' => 'drives','est' => 'estate','ests' => 'estates','expy' => 'expressway','ext' => 'extension','exts' => 'extensions','fall' => 'fall','fls' => 'falls','fry' => 'ferry','fld' => 'field','flds' => 'fields','flt' => 'flat','flts' => 'flats','frd' => 'ford','frds' => 'fords','frst' => 'forest','frg' => 'forge','frgs' => 'forges','frk' => 'fork','frks' => 'forks','ft' => 'fort','fwy' => 'freeway','gdn' => 'garden','gdns' => 'gardens','gtwy' => 'gateway','gln' => 'glen','glns' => 'glens','grn' => 'green','grns' => 'greens','grv' => 'grove','grvs' => 'groves','hbr' => 'harbor','hbrs' => 'harbors','hvn' => 'haven','hts' => 'heights','hwy' => 'highway','hl' => 'hill','hls' => 'hills','holw' => 'hollow','inlt' => 'inlet','is' => 'island','iss' => 'islands','isle' => 'isle','jct' => 'junction','jcts' => 'junctions','ky' => 'key','kys' => 'keys','knl' => 'knoll','knls' => 'knolls','lk' => 'lake','lks' => 'lakes','land' => 'land','lndg' => 'landing','ln' => 'lane','lgt' => 'light','lgts' => 'lights','lf' => 'loaf','lck' => 'lock','lcks' => 'locks','ldg' => 'lodge','loop' => 'loop','mall' => 'mall','mnr' => 'manor','mnrs' => 'manors','mdw' => 'meadow','mdws' => 'meadows','mews' => 'mews','ml' => 'mill','mls' => 'mills','msn' => 'mission','mhd' => 'moorhead','mtwy' => 'motorway','mt' => 'mount','mtn' => 'mountain','mtns' => 'mountains','nck' => 'neck','orch' => 'orchard','oval' => 'oval','opas' => 'overpass','park' => 'park','park' => 'parks','pkwy' => 'parkway','pkwy' => 'parkways','pass' => 'pass','psge' => 'passage','path' => 'path','pike' => 'pike','pne' => 'pine','pnes' => 'pines','pl' => 'place','pln' => 'plain','plns' => 'plains','plz' => 'plaza','pt' => 'point','pts' => 'points','prt' => 'port','prts' => 'ports','pr' => 'prairie','radl' => 'radial','ramp' => 'ramp','rnch' => 'ranch','rpd' => 'rapid','rpds' => 'rapids','rst' => 'rest','rdg' => 'ridge','rdgs' => 'ridges','riv' => 'river','rd' => 'road','rds' => 'roads','rte' => 'route','row' => 'row','rue' => 'rue','run' => 'run','shl' => 'shoal','shls' => 'shoals','shr' => 'shore','shrs' => 'shores','skwy' => 'skyway','spg' => 'spring','spgs' => 'springs','spur' => 'spur','spur' => 'spurs','sq' => 'square','sqs' => 'squares','sta' => 'station','strm' => 'stream','st' => 'street','sts' => 'streets','smt' => 'summit','ter' => 'terrace','trwy' => 'throughway','trce' => 'trace','trak' => 'track','trl' => 'trail','tunl' => 'tunnel','tpke' => 'turnpike','upas' => 'underpass','un' => 'union','uns' => 'unions','vly' => 'valley','vlys' => 'valleys','via' => 'viaduct','vw' => 'view','vws' => 'views','vlg' => 'village','vlgs' => 'villages','vl' => 'ville','vis' => 'vista','walk' => 'walk','walk' => 'walks','wall' => 'wall','way' => 'way','ways' => 'ways','wl' => 'well','wls' => 'wells'];

        foreach ($street_names as $key => $value) {
            if (strpos($address, $key.' ')) {
                $address = str_replace($key, $value, $address);
                break;
            }
        }

        return preg_replace('/\\b(\d+)(?:st|nd|rd|th)\\b/', '$1', $address);
    }
}
