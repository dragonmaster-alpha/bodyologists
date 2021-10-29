<?php

namespace App;

use Exception;
use Plugins\Members\Classes\Members;

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
class Helper extends Format
{

    public const CIPHER_METHOD = 'AES128';
    public const CIPHER_KEY = 'bodyologists';
    public const CIPHER_OPTIONS = OPENSSL_RAW_DATA;
    public const CIPHER_VECTOR = 'sixteencharacter';

    public function __construct()
    {
        $this->config = parent::get_config();
    }

    /**
     * @param string $string
     * @return string
     */
    public static function encrypt(string $string): string
    {
        return bin2hex(
            openssl_encrypt(
                $string,
                self::CIPHER_METHOD,
                self::CIPHER_KEY,
                self::CIPHER_OPTIONS,
                self::CIPHER_VECTOR
            )
        );
    }

    /**
     * @param $string
     * @return string
     */
    public static function decrypt($string): string
    {
        return openssl_decrypt(
            hex2bin($string),
            self::CIPHER_METHOD,
            self::CIPHER_KEY,
            self::CIPHER_OPTIONS,
            self::CIPHER_VECTOR
        );
    }

    /**
     * Generates a UUID-formatted unique string.
     * This IS *NOT* a real UUID.
     *
     * @return string
     * @throws Exception
     */
    public static function getPseudoUUID(): string
    {
        $hash = bin2hex(random_bytes(16));

        $head = substr($hash, 0, 8);
        $hash = str_replace($head, '', $hash);

        $tail = substr($hash, -12, 12);
        $hash = str_replace($tail, '', $hash);

        $middle = implode('-', str_split($hash, 4));

        return "{$head}-{$middle}-{$tail}";
    }

    /**
     * Removes any empty fields recursively
     * @see https://gist.github.com/david4worx/1a991a705a52d8f9b16b
     *
     * @param mixed $input
     * @param null|callable $callback
     * @return mixed
     */
    public static function removeEmptyFieldsRecursive($input, $callback = null)
    {
        if (!is_array($input)) {
            return $input;
        }
        if (null === $callback) {
            $callback = static function ($v) {
                return !empty($v);
            };
        }
        $input = array_map(
            static function ($v) use ($callback) {
                return self::removeEmptyFieldsRecursive($v, $callback);
            },
            $input
        );

        return array_filter($input, $callback);
    }

    /**
     * @param array $array
     * @param array $avoid_keys
     */
    public static function ucFirstRecursive(array &$array, $avoid_keys = []): void
    {
        foreach ($array as $key => &$value) {
            if (in_array($key, $avoid_keys, true)) {
                continue;
            }
            if (is_array($value)) {
                self::ucFirstRecursive($value, $avoid_keys);
            } elseif (is_string($value)) {
                $value = ucfirst(strtolower($value));
            }
        }
    }

    /**
     * @param string $string
     * @return string|null
     */
    public static function ucfirstOnMultiline(string $string = null): ?string
    {
        if (!$string) {
            return $string;
        }

        $exploded = explode("\r\n",$string);
        $lower = array_map('strtolower', $exploded);
        $upper = array_map('ucfirst', $lower);

        return implode("\r\n", $upper);
    }


    /**
     * if plugin have a language file include it
     * @param string $plugin [description]
     */
    public function get_plugin_lang($plugin = '')
    {
        $plugin = (string)$plugin;

        if (!empty($plugin)) {
            if (file_exists(
                $_SERVER['DOCUMENT_ROOT'] . _SITE_PATH . '/plugins/' . $plugin . '/language/lang-' . $_SESSION['lang'] . '.php'
            )) {
                include_once($_SERVER['DOCUMENT_ROOT'] . _SITE_PATH . '/plugins/' . $plugin . '/language/lang-' . $_SESSION['lang'] . '.php');
            }
        }

        return false;
    }

    /**
     * Return all available main and subcategories as assoc array
     *
     * @return array
     */
    public static function getCategoriesList(): array
    {
        return [
            'Health' => [
                'Massage therapists' => 'Massage therapist',
                'Physical therapists' => 'Physical therapist',
                'Yoga instructors' => 'Yoga instructor',
                'Chiropractors' => 'Chiropractor',
                'Acupuncturists' => 'Acupuncturist',
                'Physicians' => 'Physician',
                'Dentists' => 'Dentist',
                'Nurses' => 'Nurse',
                'Occupational therapists' => 'Occupational therapist',
                'Naturopaths-homeopaths' => 'Naturopathic / Homeopathic',
                'Dietitians-nutritionists' => 'Dietitian / Nutritionist',
            ],
            'Fitness' => [
                'Personal trainers' => 'Personal trainer',
                'Sport coaches' => 'Sports coach',
                'Dance-zumba-aerobics instructors' => 'Dance / Zumba / Aerobics instructor',
                'Martial arts' => 'Martial art',
            ],
            'Beauty' => [
                'Make-up artist' => 'Make-up artist',
                'Estheticians' => 'Esthetician',
                'Hairdressers-hair stylists' => 'Hairdresser / Hair stylist',
                'Nail technicians' => 'Nail technician',
                'Barbers' => 'Barber',
                'Tanning spray specialists' => 'Tanning spray specialist',
                'Tattoo artists' => 'Tattoo artist',
            ]
        ];
    }

    /**
     * Return all the available conditions
     *
     * @return array
     */
    public static function getConditionsList(): array
    {
        return [
            'Addiction',
            'Allergies',
            'Arthritis',
            'Asthma',
            'Auto/Work/Sports Injuries',
            'Back and Disc Problems',
            'Back Pain',
            'Balance and Vestibular Disorders',
            'Birthing',
            'Blood Pressure',
            "BPPV",
            'Breathing/Lungs',
            'Brusing/Hematoma',
            'Bursitis',
            'Cancer/Oncology',
            'Carpal Tunnel',
            'Cholesterol',
            'Chronic Fatigue',
            'Chronic Pain',
            'Chronic Venous Insufficiency',
            'Colds, Cough and Flu',
            'Congestive Heart Failure',
            'Cosmetic Acupuncture',
            'Depression/Anxiety',
            'Dermatology',
            'Diabetes',
            'Diet and Nutrition',
            'Digestive Disorders',
            'Disc Pain',
            'Eating Disorders',
            'Elbow Pain',
            'Emotional Wellbeing',
            'Facial Rejuvenation',
            'Fibromyalgia',
            'Fibrosis',
            'Foot Pain',
            'Frozen Shoulder',
            'Gout',
            'Headache/Migraine',
            'Hearing/Speech',
            'Hip Pain',
            'HIV/AIDS',
            'Immune Diseases/Disorders',
            'Immune System',
            'Infertility',
            'Insomnia',
            'Joint Pain',
            'Lipedema',
            'Lymphedema',
            'Migraine',
            'Molar Endodontics',
            'Muscle Aches, Sprains and Strains',
            'Muscular Dystrophy',
            'Muscle Tension',
            'Neck and Shoulder Pain',
            'Numbness',
            'Nutrition',
            'Orthopedics',
            'Osteoporosis',
            'Pain Management',
            'Pediatric',
            'Pediatric/Healthy Kids',
            'Pinched Nerve',
            'Post Surgical Conditions',
            'Pregnancy',
            'Root Canal Therapy',
            'Root Planing',
            'Sciatica',
            'Scoliosis',
            'Sexual Dysfunction',
            'Skin Disorders',
            'Skin Problems/Dermatology',
            'Smoking',
            'Spinal Problems',
            'Sports Medicine',
            'Stress',
            'Swelling/Edema',
            'Tendonitis',
            'Thoracic Outlet Syndrome',
            'Tingling',
            'Tinnitus',
            'TMJ',
            'Urology',
            'Varicose Veins',
            'Vertigo',
            'Water Retention',
            'Weight Loss',
            'Weight Management',
            'Wellness',
            'Wellness/Healing/Prevention',
            'Whiplash',
            "Women's Health/ObGyn",
            'Yeast',
        ];
    }

    /**
     * Return all available specialties
     *
     * @return array
     */
    public static function getSpecialtiesList(): array
    {
        return [
            'Activator Methods',
            'Acupressure',
            'Advanced BioStructural Correction',
            'Allergy & Immunology',
            'Anesthesiology',
            'Anti-Aging Programs',
            'Apitherapy',
            'Applied Kinesiology',
            'Aquatic Massage Therapy',
            'Atlas Orthogonal',
            'Auricular',
            'Auricular Acupuncture',
            'Bamboo Massage',
            'Bio Feedback',
            'Bio-Energetic Synchronization',
            'Bioidentical Hormone Therapy',
            'Biologically Based Therapy',
            'Biomechanics',
            'Biotherapeutic Drainage Therapy',
            'Blue Light',
            'Botanical Medicine',
            'Botox (Botulinum Toxin)',
            'Bowen Therapy',
            'Cardiology',
            'Certified LEAP Therapist',
            'Cervical Care',
            'Cervical Drop',
            'Chair Massage',
            'Chelation Therapy',
            'Chinese Acupuncture',
            'Chiropractic Biophysics',
            'Clinical Kinesiology',
            'Connective Tissue Massage',
            'Constitutional Hydrotherapy',
            'Contact Reflex Analysis',
            'Cosmetic Dentist',
            'Cosmetic Dentistry',
            'Counseling',
            'Cox',
            'Cranial',
            'CranioSacral Therapy',
            'Craniosacral Therapy',
            'Cupping',
            'Cupping Therapy',
            'Deep Tissue Massage',
            'Dermal Fillers',
            'Dermatology',
            'Detox',
            'Detoxification Programs',
            'Diet Therapy',
            'Directional Non-Force',
            'Diversified',
            'Electro acupuncture',
            'Electrotherapy',
            'Endocrinology',
            'Energy Medicine',
            'Environmental Medicine',
            'Family medicine',
            'Feldenkrais',
            'Five Element Acupuncture',
            'Flexion-Distraction',
            'FotoFacial/IPL Treatments',
            'Fractional Resurfacing',
            'Functional Medicine',
            'Gastroenterology ',
            'General Dentistry',
            'General Family Practice',
            'Geriatric Massage',
            'Geriatric Medicine',
            'Gonstead',
            'Grostic',
            'Gua Sha',
            'Gynecology',
            'Halitosis Treatment',
            'HCG Weight Loss',
            'Hematology',
            'Herbal Medicine',
            'Hole in One',
            'Holistic Dentist',
            'Homeopathy',
            'Hormone Balancing',
            'Hydrogen Peroxide Therapy',
            'Hydrotherapy',
            'Hygienist',
            'Implant Dentist',
            'Insulin Potentiation Therapy',
            'Integrative Manual Therapy',
            'Internal MedicineObstetrics & Gynecology',
            'Intuitive Eating',
            'Ionized Oxygen Therapy',
            'IV Therapy',
            'Japanese Acupuncture',
            'Joint Mobilization',
            'Juvederm',
            'Kinesiology',
            'Korean Acupuncture',
            'Laser Dentist',
            'Laser Hair Removal',
            'Laser Liposuction',
            'Laser Resurfacing',
            'Leander',
            'LipoDissolve',
            'Logan Basic',
            'Lymphatic Drainage Massage',
            'Lymphedema management',
            'Manipulative and body-based Methods',
            'Manual Adjusting',
            'Medical Nutrition Therapy',
            'Menu Planning',
            'Meric',
            'Mind-Body Intervention',
            'Mindful Eating',
            'Motion Palpation',
            'Moxibustion',
            'Myofascial Release',
            'Myoskeletal Alignment ',
            'Natural Hormone Replacement',
            'Naturopath Acupuncture',
            'Network',
            'Neural Therapy',
            'Neurology',
            'Neuromuscular Therapy',
            'Nimmo',
            'Non-Diet Approach',
            'Nutrition',
            'Nutrition Coaching',
            'Nutrition Counseling',
            'Nutrition Therapy',
            'On-site Massage',
            'Oncology ',
            'Ophthalmology',
            'Oral Surgery',
            'Oriental Medicine',
            'Orthodontist',
            'Orthopaedic Sports Medicine',
            'Orthopaedic Surgery',
            'Otolaryngology',
            'Oxygen Therapy',
            'Ozone Therapy',
            'Pain Management',
            'Pain Medicine ',
            'Palmer Package',
            'Pathology',
            'Pediatric',
            'Pediatric Dentist',
            'Pelvic Pain',
            'Periodontist',
            'Pettibon',
            'Photo-Oxidation Therapy',
            'Photodynamic Therapy',
            'Pierce Stillwagon',
            'Plastic Surgery',
            'Platelet Rich Plasma Therapy',
            'Podiatry',
            'Prenatal/Pregnancy Massage',
            'Preventative Dentist',
            'Preventive Medicine',
            'Pro-Adjuster',
            'ProLipo',
            'Prolotherapy',
            'Proprioceptive Neuromuscular Facilitation',
            'Prosthodontist',
            'Psychiatry',
            'Qigong',
            'Radiation Oncology',
            'Radiesse',
            'Radiology ',
            'Radiology-Diagnostic',
            'Reflexology',
            'Rehabilitation',
            'Restylane',
            'Rheumatology ',
            'Root Canals',
            'Rossiter Stretching',
            'Sacral Occipital Technique',
            'Sedation Dentist',
            'Shiatsu/Acupressure',
            'Skin Care Cosmetic Products',
            'Sleep',
            'Sleep Medicine',
            'Smoothbeam',
            'Soft Tissue Orthopedics',
            'Spinal Decompression',
            'Sports Massage',
            'Sports Medicine',
            'Sports Performance',
            'Stone Massage',
            'Structural Muscular Balancing',
            'Supervision of Food Preparation',
            'Swedish Massage',
            'Terminal Point',
            'Thai Massage',
            'Therapeutic Exercise',
            'Thermage',
            'Thompson',
            'TMJ Specialist',
            'Toggle Recoil',
            'Torque Release',
            'Total Body Modification',
            'Traction',
            'Traditional Hawaiian',
            'Trigger Point Therapy',
            'Tui Na Massage',
            'Upper Cervical',
            'Urology',
            'Vascular',
            'Vitamin Therapy',
            'Zero Balancing',
        ];
    }

    /**
     * Return all available insurance companies
     *
     * @return array
     */
    public static function getInsurancesList(): array
    {
        return [
            'Aetna',
            'AMERIGROUP',
            'Anthem',
            'Beech Street',
            'BlueCross and/or BlueShield',
            'California Physicians` Service ',
            'Cambia Health Solutions Inc.',
            'Carefirst ',
            'Careplus',
            'CCN',
            'Centene ',
            'ChoiceCare',
            'Cigna',
            'Compbenefits',
            'Coventry',
            'Delta',
            'Delta Dental',
            'Empire BlueCross',
            'First Choice Health',
            'First Health',
            'Florida Blue',
            'FSA (Flex Spending Account)',
            'GHI',
            'Great-West',
            'Guardian',
            'HCSC Group',
            'Health Net of California, Inc.',
            'Highmark Group',
            'Horizon Healthcare',
            'HSA (Health Savings Account)',
            'Humana',
            'Independence Blue Cross Group',
            'Integrated Health Plan',
            'Kaiser Foundation Group',
            'Lifetime Healthcare Group',
            'Magellan Behavioral Health',
            'Medicaid',
            'Medical Mutual',
            'Medicare',
            'Metlife',
            'Metropolitan Group',
            'Molina ',
            'Multiplan',
            'Out of Network',
            'Oxford',
            'Payment plan',
            'PHCS',
            'PIP/Auto Accidents',
            'Prestige',
            'Slip and Fall',
            'Staywells',
            'TRICARE',
            'UHC of California',
            'United',
            'United Concordia',
            'United Health care',
            'Wellcare',
            'Work Comp',
        ];
    }

    /**
     * Return list of person gender
     *
     * @return array
     */
    public static function getGendersList(): array
    {
        return [
            'Male',
            'Female',
        ];
    }

    /**
     * Return all available languages
     *
     * @return array
     */
    public static function getLanguagesList(): array
    {
        return [
            'Afrikaans',
            'Albanian',
            'Amharic',
            'Arabic',
            'Armenian',
            'Azerbaijani',
            'Basque',
            'Belarusian',
            'Bengali',
            'Bosnian',
            'Bulgarian',
            'Catalan',
            'Cebuano',
            'Chichewa',
            'Chinese (Simplified)',
            'Chinese (Traditional)',
            'Corsican',
            'Croatian',
            'Czech',
            'Danish',
            'Dutch',
            'English',
            'Esperanto',
            'Estonian',
            'Filipino',
            'Finnish',
            'French',
            'Frisian',
            'Galician',
            'Georgian',
            'German',
            'Greek',
            'Gujarati',
            'Haitian Creole',
            'Hausa',
            'Hawaiian',
            'Hebrew',
            'Hindi',
            'Hmong',
            'Hungarian',
            'Icelandic',
            'Igbo',
            'Indonesian',
            'Irish',
            'Italian',
            'Japanese',
            'Javanese',
            'Kannada',
            'Kazakh',
            'Khmer',
            'Korean',
            'Kurdish (Kurmanji)',
            'Kyrgyz',
            'Lao',
            'Latin',
            'Latvian',
            'Lithuanian',
            'Luxembourgish',
            'Macedonian',
            'Malagasy',
            'Malay',
            'Malayalam',
            'Maltese',
            'Maori',
            'Marathi',
            'Mongolian',
            'Myanmar (Burmese)',
            'Nepali',
            'Norwegian',
            'Pashto',
            'Persian',
            'Polish',
            'Portuguese',
            'Punjabi',
            'Romanian',
            'Russian',
            'Samoan',
            'Scots Gaelic',
            'Serbian',
            'Sesotho',
            'Shona',
            'Sindhi',
            'Sinhala',
            'Slovak',
            'Slovenian',
            'Somali',
            'Spanish',
            'Sundanese',
            'Swahili',
            'Swedish',
            'Tajik',
            'Tamil',
            'Telugu',
            'Thai',
            'Turkish',
            'Ukrainian',
            'Urdu',
            'Uzbek',
            'Vietnamese',
            'Welsh',
            'Xhosa',
            'Yiddish',
            'Yoruba',
            'Zulu',
        ];
    }

    /**
     * Return all states as [abbrev => name]
     *
     * @return array
     */
    public static function getStatesList(): array
    {
        return [
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'DC' => 'District of Columbia (DC)',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NC' => 'N. Carolina',
            'ND' => 'N. Dakota',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'PR' => 'Puerto Rico',
            'RI' => 'Rhode Island',
            'SC' => 'S. Carolina',
            'SD' => 'S. Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VI' => 'Virgin Islands',
            'VA' => 'Virginia',
            'WV' => 'W. Virginia',
            'WA' => 'Washington',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
        ];
    }

    /**
     * Get the title of an html formated page
     * it is used to set subjects as the title of its view file (layout)
     * @param mixed $string
     * @return string
     */
    public function get_title($string = '')
    {
        if (strlen($string) > 0) {
            preg_match("/\<title\>(.*)\<\/title\>/", $string, $title);
            return $title[1];
        }
    }

    /**
     * Formats the text of plugins when needed
     * this code will replace all codes with its photo gallery, videos, catpcha etc
     * @param mixed $string
     * @return string
     */
    public function format_addons($string = '')
    {
        # Replace static variables with site general configuration variables
        $array_in = [
            '##COMPANY_NAME##',
            '##FULL_ADDRESS##',
            '##ADDRESS##',
            '##SUITE##',
            '##CITY##',
            '##STATE##',
            '##ZIPCODE##',
            '##COUNTRY##',
            '##PHONE##',
            '##FAX##',
            '##CONTACT_EMAIL##',
            '##SALES_EMAIL##',
            '##SUPPORT_EMAIL##',
            '##FEEDBACK_EMAIL##',
            '[captcha]'
        ];
        $array_out = [
            $this->config['contactname'],
            $this->config['address'] . ' ' . $this->config['address2'] . '<br />' . $this->config['city'] . ', ' . $this->config['state'] . ' ' . $this->config['zipcode'],
            $this->config['address'],
            $this->config['address2'],
            $this->config['city'],
            $this->config['state'],
            $this->config['zipcode'],
            $this->config['country'],
            $this->config['phone'],
            $this->config['fax'],
            $this->config['contactemail'],
            $this->config['salesemail'],
            $this->config['supportemail'],
            $this->config['feedbackemail'],
            $this->display_catpcha()
        ];

        $string = str_replace($array_in, $array_out, $string);

        # Check and convert included photo galleries
        if (stristr($string, '[gallery')) {
            /* Commented lines are to allow multiple galleries on the page */
            /**/
            $how_many = substr_count($string, '[gallery=');
            /**/
            $nex_gal = 0;
            /**/
            do {
                if (preg_match_all("@(?:<p>)*\s*\[gallery\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i", $string, $matches)) {
                    if (is_array($matches)) {
                        foreach ($matches[1] as $key => $v0) {
                            $bid = md5((string)$v0);
                            $result = '<div class="gallery">';

                            foreach (
                                $this->sql_get(
                                    'media',
                                    'image, media, link, title',
                                    ['belongs' => 'gallery', 'bid' => $bid],
                                    'imageId ASC'
                                ) as $data
                            ) {
                                $image = parent::filter($data['image'], 1);
                                $count++;

                                if ($data['media'] == 'youtube') {
                                    $result .=
                                        '<div>
                                        <a href="' . str_replace(
                                            'https://youtu.be/',
                                            'http://www.youtube.com/embed/',
                                            $data['link']
                                        ) . '" class="box frame " rel="box[gallery]" data-width="853" data-height="480">
                                            <img src="' . $image . '" />
                                        </a>
                                    </div>';
                                } else {
                                    $result .=
                                        '<div>
                                        <a href="' . _SITE_PATH . '/uploads/gallery/' . $bid . '/' . $image . '" class="box" rel="box[gallery]">
                                            <img src="' . _SITE_PATH . '/uploads/gallery/' . $bid . '/thumb-' . $image . '" alt="' . $data['title'] . '" />
                                        </a>
                                    </div>';
                                }
                            }
                            $result .= '</div>';
                            $string = str_replace("[gallery=$v0]", $result, $string);
                        }
                    }
                }
                /**/
                $nex_gal++;
                /**/
            } while ($nex_gal < $how_many);
        }

        # Check and convert included video galleries -- MAKE SURE THE VIDEO PLAYES IS INTO THE KERNEL --
        if (stristr($string, '[video')) {
            if (preg_match_all("@(?:<p>)*\s*\[video\s*=\s*(\w+|^\+)\]\s*(?:</p>)*@i", $string, $matches)) {
                if (is_array($matches)) {
                    foreach ($matches[1] as $key => $v0) {
                        $bid = md5((string)$v0);
                        foreach (
                            $this->sql_get(
                                'media',
                                'id, image',
                                ['belongs' => 'videos', 'bid' => $bid],
                                'imageId ASC'
                            ) as $data
                        ) {
                            $data = parent::filter($data, 1);
                            $result .= '
                                <video id="video-area-' . $data['id'] . '" class="video-js vjs-default-skin" controls autoplay preload="auto" width="590" height="340" data-setup="{}">
                                    <source src="' . _SITE_PATH . '/uploads/videos/' . $bid . '/' . str_replace(
                                    '.jpg',
                                    '',
                                    $data['image']
                                ) . '.mp4" type="video/mp4" />
                                    <source src="' . _SITE_PATH . '/uploads/videos/' . $bid . '/' . str_replace(
                                    '.jpg',
                                    '',
                                    $data['image']
                                ) . '.ogg" type="video/ogg" />
                                    <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that supports HTML5 video</p>
                                </video>
                            ';
                        }

                        $string = str_replace("[video=$v0]", $result, $string);
                    }
                }
            }
        }

        # Check and conver included CAPTCHA static variable into a CAPTCHA area
        if (stristr($string, '[captcha]')) {
            $string = str_replace("[captcha]", $this->display_captcha, $string);
        }

        return $string;
    }

    /**
     * Display catpcha on static pages when needed
     * @return string
     */
    public function display_catpcha($placeholderText = '', $additionalClasses = '')
    {
        ob_start(); ?>
        <div class="row">
            <div class="col-md-5 col-xs-5">
                <img src="data/img.php?regen=y&amp;<?= time() ?>" width="100" height="30" id="captcha-image"/>
            </div>
            <div class="col-md-5 col-xs-5">
                <input type="text" name="captcha_code" value="" class="required form-control <?= $additionalClasses; ?>"
                       autocomplete="off" maxlength="5" style="text-transform: uppercase" placeholder="<?= $placeholderText; ?>"/>
            </div>
            <div class="col-md-2 col-xs-2">
                <button onclick="$('#captcha-image').attr('src', 'data/img.php?regen=y&amp;<?= time() ?>');"
                        class="btn btn-info tipN captcha-reload" title="<?= _CAPTCHA_RELOAD_CONFIRM_CODE ?>"
                        type="button">
                    <span class="icon-refresh icon-fw"></span>
                </button>
            </div>
        </div>
        <?php
        $captcha_code = ob_get_clean();

        return $captcha_code;
    }

    /**
     * Collect and return states based on country, if given country does not exist in DB it will return an input
     * @param string $select_name name of the input to be returned
     * @param string $value any specific value assigned
     * @param string $country country to query from
     * @param string $id id of the input to be returned
     * @param mixed $extra
     * @return string
     */
    public function get_states($select_name = 'state', $value = '', $country = 'US', $extra = [])
    {
        $data = $this->sql_get('states', 'prefix, state', ['country' => $country], 'state ASC');

        if (count($data) > 0) {
            $return = '<select name="' . $select_name . '"';

            if (count($extra) > 0) {
                foreach ($extra as $name_info => $value_info) {
                    $return .= ' ' . $name_info . '="' . $value_info . '"';
                }
            }

            $return .= '>';

            if (array_key_exists('placeholder', $extra)) {
                $return .= '<option selected="selected" disabled value="">' . $extra['placeholder'] . '</option>';
            }

            foreach ($data as $item) {
                $return .= '<option value="' . $item['prefix'] . '"';

                if (is_array($value)) {
                    if (in_array($item['prefix'], $value)) {
                        $return .= ' selected="selected"';
                    }
                } else {
                    if (stripos($value, $item['prefix']) !== false) {
                        $return .= ' selected="selected"';
                    }
                }

                $return .= '>' . $item['state'] . '</option>';
            }

            $return .= '</select>';
        } else {
            $return = '<input type="text" value="' . $value . '" name="' . $select_name . '" id="' . $select_name . '" ';

            if (count($extra) > 0) {
                foreach ($extra as $name_info => $value_info) {
                    $return .= ' ' . $name_info . '="' . $value_info . '"';
                }
            }

            $return .= ' />';
        }

        return $return;
    }

    /**
     * Collect a list of the countries available in DB
     * @param string $name name of the input to be returned
     * @param string $value any specific value assigned
     * @param string $js_script javascript code to be executed when selection change
     * @param mixed $extra
     * @return string
     */
    public function get_countries($name = '', $value = 'US', $js_script = '', $extra = [])
    {
        $return = '<select name="' . $name . '" ';
        $return .= (!empty($js_script)) ? $js_script : '';

        if (count($extra) > 0) {
            foreach ($extra as $name_info => $value_info) {
                $return .= ' ' . $name_info . '="' . $value_info . '"';
            }
        }

        $return .= '>';

        if (array_key_exists('placeholder', $extra)) {
            $return .= '<option selected="selected" disabled value="">' . $extra['placeholder'] . '</option>';
        }

        foreach (
            $this->sql_get(
                'countries',
                'countries_name, countries_iso_code_2',
                '',
                'countries_name ASC'
            ) as $item
        ) {
            $return .= '<option value="' . $item['countries_iso_code_2'] . '"';

            if ($value == $item['countries_iso_code_2']) {
                $return .= ' selected="selected"';
            }

            $return .= '>' . $item['countries_name'] . '</option>';
        }

        $return .= '</select>';

        return $return;
    }

    /**
     * Collect and display available languages on DB
     * @return array       returns array with the language information
     */
    public function get_languages()
    {
        foreach ($this->sql_get('languages', '*', '', 'language ASC') as $data) {
            $return[] = parent::filter($data, 1);
        }

        return $return;
    }

    /**
     * Create html select options from array
     * @param array $_array array to work with
     * @param string $value selected value in case there is one
     * @param int $level level to start from
     * @return string          returns a formatted html options list
     */
    public function select_from_array(array $_array = [], $value = '', $level = 0)
    {
        foreach ($_array as $node) {
            $indent = str_repeat(' &nbsp; &nbsp; &nbsp; &nbsp; ', $level);

            echo '<option value="' . $node['id'] . '"';

            if ($node['id'] == $value) {
                echo ' selected="selected"';
            }

            echo '>' . $indent . $node['name'] . '</option>';

            if (!empty($node['children'])) {
                $this->select_from_array($node['children'], $value, $level + 1);
            }
        }
    }

    /**
     * Create list from multidimensional array
     * @param array $_array array to work with
     * @param int $level the level to start from
     * @return string          returns a formatted html list
     */
    public function list_from_array(array $_array = [], $level = 0)
    {
        foreach ($_array as $node) {
            if ($node['url'] == 'index') {
                $node['url'] = _SITE_PATH . '/';
            }

            echo '<li><a href="' . (!empty($node['children']) ? '#" onclick="javascript:void(0)' : $node['url']) . '" ' . ($node['url'] == str_replace(
                    _SITE_PATH . '/',
                    '',
                    $_SERVER["REQUEST_URI"]
                ) ? ' class="actual-page"' : '') . ' target="' . (stripos(
                    $node['url'],
                    '://'
                ) !== false ? '_blank' : '_self') . '">' . $node['name'] . '</a>';

            if (!empty($node['children'])) {
                echo "<ul>";
                $this->list_from_array($node['children'], $level++);
                echo "</ul>";
            }

            echo "</li>";
        }
    }

    /**
     * Create list from multidimensional array
     * @param array $_array array to work with
     * @param int $level the level to start from
     * @return string          returns a formatted html list
     */
    public function list_from_array_mobile(array $_array = [], $level = 0)
    {
        foreach ($_array as $node) {
            if ($node['url'] == 'index') {
                $node['url'] = _SITE_PATH . '/';
            }

            echo '<li><a class="no-padding" href="' . (!empty($node['children']) ? '#" onclick="javascript:void(0)' : $node['url']) . '" ' . ($node['url'] == str_replace(
                    _SITE_PATH . '/',
                    '',
                    $_SERVER["REQUEST_URI"]
                ) ? ' class="actual-page"' : '') . ' target="' . (stripos(
                    $node['url'],
                    '://'
                ) !== false ? '_blank' : '_self') . '"><button class="menu-button">' . $node['name'] . '</button></a>';

            if (!empty($node['children'])) {
                echo "<ul>";
                $this->list_from_array($node['children'], $level++);
                echo "</ul>";
            }

            echo "</li>";
        }
    }

    /**
     * Search an array/ multidimensional array based on given KEY and VALUE
     * @param array $array Array to search from
     * @param string $key array KEY to match
     * @param string $value array VALUE to search for
     * @return [type]        return an array with all that array infomration
     */
    public function search_array(array $array = [], $key = '', $value = '')
    {
        $results = [];

        if (is_array($array)) {
            if ($array[$key] == $value) {
                $results[] = $array;
            } else {
                foreach ($array as $sub_array) {
                    $results = array_merge($results, $this->search_array($sub_array, $key, $value));
                }
            }
        }

        return $results;
    }

    /**
     * Get weeks info from a given month
     * @param string $date date to get info from
     * @param bool $rollover
     * @return [type]           [description]
     */
    public function get_month_weeks($date, $rollover)
    {
        $cut = substr($date, 0, 8);
        $daylen = 86400;
        $timestamp = strtotime($date);
        $first = strtotime($cut . "00");
        $elapsed = ($timestamp - $first) / $daylen;
        $i = 1;
        $weeks = 1;

        for ($i; $i <= $elapsed; $i++) {
            $dayfind = $cut . (strlen($i) < 2 ? '0' . $i : $i);
            $daytimestamp = strtotime($dayfind);
            $day = strtolower(date('l', $daytimestamp));

            if ($day == strtolower($rollover)) {
                $weeks++;
            }
        }

        return $weeks;
    }

    /**
     * Short numbers adding H for hundreds, K for thousand, M for million, B for billion
     * @param integral $number
     * @param int $decimals numbers to leave after periods
     * @return string            return formatted number
     */
    public function shorten_number($number = 0, $decimals = 0)
    {
        $number = preg_replace("/[^0-9.]/", '', $number);

        if ($number >= 100 && $number < 1000) {
            $return = number_format($number / 100, $decimals) . 'H';
        } elseif ($number >= 1000 && $number < 1000000) {
            $return = number_format($number / 1000, $decimals) . 'K';
        } elseif ($number >= 1000000 && $number < 1000000000) {
            $return = number_format($number / 1000000, $decimals) . 'M';
        } elseif ($number >= 1000000000) {
            $return = number_format($number / 1000000000, $decimals) . 'B';
        } else {
            $return = $number;
        }

        return $return;
    }

    /**
     * Check is a plugin exists
     * @param  [type]  $plugin plugin name
     * @return bool
     */
    public function is_plugin($plugin = '')
    {
        return (is_dir($_SERVER['DOCUMENT_ROOT'] . _SITE_PATH . '/plugins/' . $plugin) ? true : false);
    }

    /**
     * Check is a plugin addon exists
     * @param  [type]  $plugin plugin name
     * @param mixed $addon
     * @return bool
     */
    public function is_addon($plugin = '', $addon = '')
    {
        return (is_dir(
            $_SERVER['DOCUMENT_ROOT'] . _SITE_PATH . '/plugins/' . $plugin . '/addons/' . $addon
        ) ? true : false);
    }

    /**
     * Check is a plugin admin area exists
     * @param  [type]  $plugin plugin name
     * @return bool
     */
    public function has_admin($plugin = '')
    {
        return (is_dir($_SERVER['DOCUMENT_ROOT'] . _SITE_PATH . '/plugins/' . $plugin . '/admin') ? true : false);
    }

    /**
     * return loaded plugins
     * @param array
     */
    public function list_plugins()
    {
        foreach ($this->sql_get('plugins', 'name', ['active' => 1]) as $data) {
            $return[] = parent::filter($data['name'], 1);
        }
        return $return;
    }

    /**
     * Calculate human formated file size
     * @param internal $size file size on bits
     * @return string
     */
    public function file_size($size = 0)
    {
        $filesizename = [' Bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB'];
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i];
    }

    /**
     * List files within a directory that match given extension
     * @param  [type] $dir directory to search
     * @param  [type] $ext only the file that matches this extension (optional)
     * @return [type]      array
     */
    public function files_list($dir = '', $ext = '')
    {
        if (!empty($dir)) {
            foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
                if (is_file($dir . '/' . $file) && preg_match("/" . $ext . "$/i", $file)) {
                    $return[] = $file;
                }
            }

            return $return;
        }

        return false;
    }

    /**
     * Minify Css code
     * @param string $_data html code to minify
     * @return string   minified data
     */
    public function compress_css($_data)
    {
        $_data = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $_data);
        $_data = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $_data);

        return $_data;
    }

    /**
     * Minify js code
     * @param string $_data html code to minify
     * @return string   minified data
     */
    public function compress_js($_data)
    {
        $_data = str_replace(["\n", "\r"], '', $_data);
        $_data = preg_replace('!\s+!', ' ', $_data);
        $_data = str_replace([' {', ' }', '{ ', '; '], ['{', '}', '{', ';'], $_data);

        return $_data;
    }

    /**
     * Checks if a url address is right
     * @param string $email
     * @param mixed $url
     * @return string
     */
    public function check_url($url = '')
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * Check url within the system
     * @param string $table DB table to check from
     * @param string $url URL to check
     * @return bool
     */
    public function check_new_url($table = '', $url = '')
    {
        $table = parent::filter($table, 1, 1);
        $url = $this->link($url) . '.html';

        if (!empty($table) && !empty($url) && $this->check_url($url)) {
            if ($this->table_exists($table)) {
                return ($this->sql_count($table, ['url' => $url]) > 0) ? true : false;
            }
        }

        return true;
    }

    /**
     * Get the current url, with scheme
     *
     * @return string
     */
    public static function get_current_url(): string
    {
        # Fix: Get the scheme from referrer url which has it present.
        $parts = parse_url($_SERVER['HTTP_REFERER']);

        return $parts['scheme'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Checks if a IP address is right
     * @param mixed $ip
     * @return bool
     */
    public function check_ip($ip = '')
    {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * format given array and send json headers and response
     * @param array $data array to be converted into json data
     * @return json
     */
    public function json_response($data)
    {
        header('Content-type: application/json');
        echo json_encode($data);
        exit(0);
    }

    /**
     * Format user data to be displayed
     * @param array $user_data user data!
     * @param string $date date!
     * @return html formated content
     */
    public function show_user_info(array $user_data = [], $date = '')
    {
        if (!empty($user_data)) {
            $avatar = !empty($user_data['avatar']) ?
                _SITE_PATH . '/uploads/avatar/' . md5((string)$user_data['id']) . '/thumb-' . $user_data['avatar'] :
                'images/no-image-60x60.jpg';
            echo
                '<div class="user-data">' .
                '<div class="avatar" style="background-image: url(' . $avatar . ');">' .
                '<img src="images/blank.gif" alt="' . $user_data['full_name'] . '" />' .
                '</div>' .
                '<div class="text">' .
                '<strong>' . $user_data['full_name'] . '</strong><br />' .
                _MEMBER_SINZE . ': ' . $this->format_date($user_data['date']) . '<br />' .
                _PHONE . ': ' . $user_data['phone'] .
                '</div>' .
                '</div>';
        }
    }

    /**
     * Format user data to be displayed
     * @param array $user_data array containing data info
     * @return html formated content
     */
    public function show_user_info_short(array $user_data = [])
    {
        if (!empty($user_data)) {
            $avatar = !empty($user_data['avatar']) ?
                _SITE_PATH . '/uploads/avatar/' . md5((string)$user_data['id']) . '/thumb-' . $user_data['avatar'] :
                'images/no-image-60x60.jpg';
            echo
                '<div class="user-data">' .
                '<div class="avatar" style="float: none; margin: 5px auto; background-image: url(' . $avatar . ');">' .
                '<img src="images/blank.gif" alt="' . $user_data['full_name'] . '" />' .
                '</div>' .
                '<div class="text-center">' .
                $user_data['full_name'] .
                '</div>' .
                '</div>';
        }
    }

    /**
     * Format user data to be displayed
     * @param array $user_data array containing data info
     * @return html formated content
     */
    public function show_user_info_short_medium(array $user_data = [])
    {
        if (!empty($user_data)) {
            $avatar = !empty($user_data['avatar']) ?
                _SITE_PATH . '/uploads/avatar/' . md5((string)$user_data['id']) . '/thumb-' . $user_data['avatar'] :
                'images/no-image-60x60.jpg';

            echo
                '<div class="user-data">' .
                '<div class="avatar avatar-medium" style="float: none; margin: 5px auto; background-image: url(' . $avatar . ');">' .
                '<a href="' . $user_data['url'] . '">' .
                '<img src="images/blank.gif" alt="' . $user_data['full_name'] . '" />' .
                '</a>' .
                '</div>' .
                '<div class="text-center">' .
                $user_data['display_name'] .
                '</div>' .
                '<div class="separator sm"></div>' .
                '<div class="text-center bio-area">' .
                $this->reduce_text($user_data['bio'], 200) .
                '</div>' .
                '</div>';
        }
    }

    /**
     * Format user data to be displayed (smaller)
     * @param array $user_data user data!
     * @param string $date date!
     * @return html formated content
     */
    public function show_user_small_avatar(array $user_data = [], $date = '')
    {
        if (!empty($user_data)) {
            $avatar = !empty($user_data['avatar']) ?
                _SITE_PATH . '/uploads/avatar/' . md5((string)$user_data['id']) . '/small-' . $user_data['avatar']
                : 'images/no-image-60x60.jpg';

            echo
                '<div class="small-user-data">' .
                '<div class="avatar" style="background-image: url(' . $avatar . ');">' .
                '<img src="images/blank.gif" alt="' . $user_data['full_name'] . '" />' .
                '</div>' .
                '<div class="text">' .
                '<strong>' . $user_data['full_name'] . '</strong><br />' .
                $this->format_sort_date(strtotime($date)) .
                '</div>' .
                '</div>';
        }
    }

    /**
     * Format user data to be display their avatar only
     * @param array $user_data user data!
     * @param bool $small Use the small version
     * @return void formated content
     */
    public function show_user_avatar_only(array $user_data = [], $small = true)
    {
        if (!empty($user_data)) {
            $avatar = !empty($user_data['avatar']) ?
                _SITE_PATH . '/uploads/avatar/' . md5((string)$user_data['id']) . '/'.($small ? 'small-':'') . $user_data['avatar'] :
                'images/no-image-60x60.jpg';

            echo
                '<div class="small-user-data">' .
                '<div class="avatar" style="background-image: url(' . $avatar . ');">' .
                '<img src="images/blank.gif" alt="' . $user_data['full_name'] . '" />' .
                '</div>' .
                '</div>';
        }
    }

    /**
     * Return the HEX color for the given category or default to Beauty
     *
     * @param string $category
     * @return string
     */
    public static function getCategoryColor(?string $category): string
    {
        $category_colors = [
            'Health' => '#b9dfc9',
            'Beauty' => '#baa3aa',
            'Fitness' => '#f7a08b',
        ];

        return $category_colors[$category] ?? '#baa3aa';
    }

    public static function encode($content, $doubleEncode = true)
    {
        return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
    }
}
