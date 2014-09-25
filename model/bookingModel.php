<?php

namespace model;

class bookingModel {

    /**
     * get operating months and days therein(numbers)
     * encode month/year, bit of a bodge
     */
    public function getMonthsDays($dates, $dmax, $seatsneeded) {
        $months = array();
        foreach ($dates as $date) {
            $month = date('m/Y', $date->date);
            $day = date('j', $date->date);
            if (!isset($months[$month])) {
                $months[$month] = array();
            }
            if ($seatsneeded <= $dmax[$date->id()]) {
                $months[$month][$day] = $date->id;
            }
        }

        return $months;
    }
    
    /**
     * Get trainlimit
     */
    public function getTrainlimit($dateid, $timeid) {
        $limit = \ORM::for_table('trainlimit')->where(array(
                'dateid' => $dateid,
                'timeid' => $timeid,
        ))->find_one();
        if (!$limit) {
            throw new \Exception("No limit record found in DB for timeid=".$time->id()." dateid=".$date->id());
        }
        return $limit;       
    }

    /**
     * Get (2d) array of passenger remaining counts
     */
    public function getRemaining() {
        $dates = \ORM::for_table('traindate')->order_by_asc('date')->find_many();
        $times = \ORM::for_table('traintime')->order_by_asc('time')->find_many();
        $pcounts = array();
        $daymax = array();
        foreach ($dates as $date) {
            $pcounts[$date->id()] = array();
            $daymax[$date->id()] = 0;
            foreach ($times as $time) {
                $limit = $this->getTrainlimit($date->id(), $time->id());
                $sumadult = \ORM::for_table('purchase')->where('trainlimitid', $limit->id())->sum('adult');
                $sumchild = \ORM::for_table('purchase')->where('trainlimitid', $limit->id())->sum('child');
                $total = $limit->maxlimit - ($sumadult + $sumchild);
                $pcounts[$date->id()][$time->id()] = $total;
                if ($total > $daymax[$date->id()]) {
                    $daymax[$date->id()] = $total;
                }
            }
        }
        return array($pcounts, $daymax);
    }

    /*
     * Create select array for child's ages
     */
    public function getAges() {
    	$ages = array(
    		1 => '18-23 months',
    	);
    	for ($i=2; $i<=15; $i++) {
    		$ages[$i] = "$i years";
    	}
    	return $ages;
    }

    /**
     * Get the date in a readable format given dateid
     * @param unknown $dateid
     */
    public function getReadableDate($dateid) {
        $date = \ORM::for_table('traindate')->find_one($dateid);
        if (!$date) {
            throw new \Exception('Date not found in DB for id='.$dateid);
        }
        return date('d/M/Y', $date->date);
    }

    /**
     * Get the time in a readable format given timeid
     *
     */
    public function getReadableTime($timeid) {
        $time = \ORM::for_table('traintime')->find_one($timeid);
        if (!$time) {
            throw new \Exception('Time not found in DB for id='.$timeid);
        }
        return date('H:i', $time->time);
    }

    /**
     * Make the SagePay basket
     * @param unknown $br
     */
    private function makeBasket($br) {
        return '';
    }

    /**
     * Filter for SagePay's crypt fields
     * @param unknown $value
     * @return unknown
     */
    private function filter($value, $maxchars, $filter) {

        // use sagepay's clean functions
        $value = \sagelib::cleanInput($value, $filter);

        // truncate to maxchars
        $value = substr($value, 0, $maxchars);

        return $value;
    }

    /**
     * Construct SagePay crypt string
     * @param unknown $br
     */
    public function crypt($br) {
        global $CFG;

        // sort some basic stuff
        $description = "Bo'ness & Kinneil Railway Santa Steam Train booking on " .
            $this->getReadableDate($br->getDateid()) . ' departing ' . $this->getReadableTime($br->getTimeid()) .
            ' reference ' . $CFG->sage_prefix . $br->getReference();

        // build transaction data into string
        $cryptfields = array(
            'VendorTXCode' => $CFG->sage_prefix . $br->getReference(),
            'Amount' => number_format($br->getAmount(), 2),
            'Currency' => 'GBP',
            'Description' => $description,
            'SuccessURL' => $CFG->www . '/index.php/booking/return/success',
            'FailureURL' => $CFG->www . '/index.php/booking/return/failure',
            'CustomerName' => $this->filter($br->getTitle() . ' ' . $br->getFirstname() . ' ' . $br->getLastname(), 100,
                CLEAN_INPUT_FILTER_ALPHABETIC_AND_ACCENTED),
            'CustomerEmail' => $this->filter($br->getEmail(), 100, CLEAN_INPUT_FILTER_WIDEST_ALLOWABLE_CHARACTER_RANGE),
            'VendorEmail' => $this->filter($CFG->sage_email, 255, CLEAN_INPUT_FILTER_WIDEST_ALLOWABLE_CHARACTER_RANGE),
            'SendEmail' => '1',
            'eMailMessage' => $this->filter($CFG->sage_message, 7500, CLEAN_INPUT_FILTER_WIDEST_ALLOWABLE_CHARACTER_RANGE),
            'BillingSurname' => $this->filter($br->getLastname(), 20, CLEAN_INPUT_FILTER_ALPHABETIC_AND_ACCENTED),
            'BillingFirstnames' => $this->filter($br->getFirstname(), 20, CLEAN_INPUT_FILTER_ALPHABETIC_AND_ACCENTED),
            'BillingAddress1' => $this->filter($br->getAddress1(), 100, CLEAN_INPUT_FILTER_ALPHANUMERIC_AND_ACCENTED),
            'BillingAddress2' => $this->filter($br->getAddress2(), 100, CLEAN_INPUT_FILTER_ALPHANUMERIC_AND_ACCENTED),
            'BillingCity' => $this->filter($br->getCity(), 40, CLEAN_INPUT_FILTER_ALPHANUMERIC_AND_ACCENTED),
            'BillingPostCode' => $this->filter($br->getPostcode(), 10, CLEAN_INPUT_FILTER_ALPHANUMERIC),
            'BillingCountry' => $br->getCountry(),
            'BillingPhone' => $this->filter($br->getPhone(), 20, CLEAN_INPUT_FILTER_ALPHANUMERIC),
            'DeliverySurname' => $this->filter($br->getLastname(), 20, CLEAN_INPUT_FILTER_ALPHABETIC_AND_ACCENTED),
            'DeliveryFirstnames' => $this->filter($br->getFirstname(), 20, CLEAN_INPUT_FILTER_ALPHABETIC_AND_ACCENTED),
            'DeliveryAddress1' => $this->filter($br->getAddress1(), 100, CLEAN_INPUT_FILTER_ALPHANUMERIC_AND_ACCENTED),
            'DeliveryAddress2' => $this->filter($br->getAddress2(), 100, CLEAN_INPUT_FILTER_ALPHANUMERIC_AND_ACCENTED),
            'DeliveryCity' => $this->filter($br->getCity(), 40, CLEAN_INPUT_FILTER_ALPHANUMERIC_AND_ACCENTED),
            'DeliveryPostCode' => $this->filter($br->getPostcode(), 10, CLEAN_INPUT_FILTER_ALPHANUMERIC),
            'DeliveryCountry' => $br->getCountry(),
            'DeliveryPhone' => $this->filter($br->getPhone(), 20, CLEAN_INPUT_FILTER_ALPHANUMERIC),
            'Basket' => $this->filter($this->makeBasket($br), 7500, CLEAN_INPUT_FILTER_WIDEST_ALLOWABLE_CHARACTER_RANGE),
        );

        // translate to name=value array
        $namevalue = array();
        foreach ($cryptfields as $name => $value) {
            $namevalue[] = "$name=$value";
        }

        $cryptstring = implode('&', $namevalue);

        return \sagelib::encryptAndEncode($cryptstring, $CFG->sage_encrypt);
    }

    /**
     * Get the nth date/time in the database for output record
     */
    private function getNth($table, $sort, $id) {
        $records = \ORM::for_table($table)->order_by_asc($sort)->find_many();
        $count = 1;
        foreach ($records as $record) {
            if ($record->id == $id) {
                return $count;
            }
            $count++;
        }
        throw new Exception('ID '.$id.' not found in table '.$table);
    }

    /**
     * Convert boy/girl ages records into hex strings
     */
    private function encodeAges($br) {
        $ages = $br->getAges();
        $sexes = $br->getSexes();
        $boys = '';
        $girls = '';
        if ($sexes) {
            foreach ($sexes as $sex) {
                $age = array_shift($ages);
                if ($sex=='boy') {
                    $boys .= dechex($age);
                } else {
                    $girls .= dechex($age);
                }
            }
        }
        return array($boys, $girls);
    }

    /**
     * Update or create purchase record (checks for id)
     * @param unknown $br
     */
    public function updatePurchase($br) {
        global $CFG;

        // if br has a reference number this should match the database record
        if ($br->getReference()) {
            $purchase = \ORM::for_table('purchase')->find_one($br->getReference());
            if (!$purchase) {
                throw new \Exception('Cannot find record in DB for purchase id='.$br->getReference());
            }
        } else {
            $purchase = \ORM::for_table('purchase')->create();
        }

        // country
        $countries = $this->getCountries();
        $country = $countries[$br->getCountry()];

        // update all the data (can't update reference until sure we have an ID)
        $purchase->type = 'O';
        $purchase->bkgref = $CFG->sage_prefix . $br->getReference();
        $purchase->trainlimitid = $br->getTrainlimitid();
        $purchase->day = $this->getNth('traindate', 'date', $br->getDateid());
        $purchase->train = $this->getNth('traintime', 'time', $br->getTimeid());
        $purchase->surname = $br->getLastname();
        $purchase->title = $br->getTitle();
        $purchase->firstname = $br->getFirstname();
        $purchase->address1 = $br->getAddress1();
        $purchase->address2 = $br->getAddress2();
        $purchase->address3 = $br->getCity();
        $purchase->address4 = $br->getCounty();
        $purchase->postcode = $br->getPostcode();
        $purchase->phone = $br->getPhone();
        $purchase->email = $br->getEmail();
        $purchase->adult = $br->getAdults();
        $purchase->child = $br->getChildren();
        $purchase->infant = $br->getInfants();
        $purchase->oap = 0;
        list($purchase->childagesboy, $purchase->childagesgirl) = $this->encodeAges($br);
        $purchase->comment = '';
        $purchase->payment = floor($br->getAmount() * 100);
        $purchase->bkgdate = date('Ymd');
        $purchase->card = 'Y';
        $purchase->action = 'N';
        $purchase->eticket = 'N';
        $purchase->einfo = 'N';

        $purchase->save();
        $br->setReference($purchase->id());
        $br->save();

        return $br->getReference();
    }

    /**
     * Decrypt returndata from sage
     */
    public function decrypt($br, $crypt) {
        global $CFG;

        $decode = \sagelib::decodeAndDecrypt($crypt, $CFG->sage_encrypt);
        $pairs = explode('&', $decode);
        $data = array();
        foreach($pairs as $pair) {
            $split = explode('=', $pair);
            $data[$split[0]] = $split[1];
        }

        // Update the database record
        $purchase = \ORM::for_table('purchase')->find_one($br->getReference());
        if (!$purchase) {
            throw new \Exception('Cannot find record in DB for purchase id='.$br->getReference());
        }

        $purchase->bkgref = $data['VendorTxCode'];
        $purchase->status = $data['Status'];
        $purchase->statusdetail = $data['StatusDetail'];
        $purchase->txauthno = $data['TxAuthNo'];
        $purchase->last4digits = $data['Last4Digits'];
        $purchase->save();

        return $purchase;
    }

    public function getCountries() {
    	$countries = array
    	(
    			'AF' => 'Afghanistan',
    			'AX' => 'Aland Islands',
    			'AL' => 'Albania',
    			'DZ' => 'Algeria',
    			'AS' => 'American Samoa',
    			'AD' => 'Andorra',
    			'AO' => 'Angola',
    			'AI' => 'Anguilla',
    			'AQ' => 'Antarctica',
    			'AG' => 'Antigua And Barbuda',
    			'AR' => 'Argentina',
    			'AM' => 'Armenia',
    			'AW' => 'Aruba',
    			'AU' => 'Australia',
    			'AT' => 'Austria',
    			'AZ' => 'Azerbaijan',
    			'BS' => 'Bahamas',
    			'BH' => 'Bahrain',
    			'BD' => 'Bangladesh',
    			'BB' => 'Barbados',
    			'BY' => 'Belarus',
    			'BE' => 'Belgium',
    			'BZ' => 'Belize',
    			'BJ' => 'Benin',
    			'BM' => 'Bermuda',
    			'BT' => 'Bhutan',
    			'BO' => 'Bolivia',
    			'BA' => 'Bosnia And Herzegovina',
    			'BW' => 'Botswana',
    			'BV' => 'Bouvet Island',
    			'BR' => 'Brazil',
    			'IO' => 'British Indian Ocean Territory',
    			'BN' => 'Brunei Darussalam',
    			'BG' => 'Bulgaria',
    			'BF' => 'Burkina Faso',
    			'BI' => 'Burundi',
    			'KH' => 'Cambodia',
    			'CM' => 'Cameroon',
    			'CA' => 'Canada',
    			'CV' => 'Cape Verde',
    			'KY' => 'Cayman Islands',
    			'CF' => 'Central African Republic',
    			'TD' => 'Chad',
    			'CL' => 'Chile',
    			'CN' => 'China',
    			'CX' => 'Christmas Island',
    			'CC' => 'Cocos (Keeling) Islands',
    			'CO' => 'Colombia',
    			'KM' => 'Comoros',
    			'CG' => 'Congo',
    			'CD' => 'Congo, Democratic Republic',
    			'CK' => 'Cook Islands',
    			'CR' => 'Costa Rica',
    			'CI' => 'Cote D\'Ivoire',
    			'HR' => 'Croatia',
    			'CU' => 'Cuba',
    			'CY' => 'Cyprus',
    			'CZ' => 'Czech Republic',
    			'DK' => 'Denmark',
    			'DJ' => 'Djibouti',
    			'DM' => 'Dominica',
    			'DO' => 'Dominican Republic',
    			'EC' => 'Ecuador',
    			'EG' => 'Egypt',
    			'SV' => 'El Salvador',
    			'GQ' => 'Equatorial Guinea',
    			'ER' => 'Eritrea',
    			'EE' => 'Estonia',
    			'ET' => 'Ethiopia',
    			'FK' => 'Falkland Islands (Malvinas)',
    			'FO' => 'Faroe Islands',
    			'FJ' => 'Fiji',
    			'FI' => 'Finland',
    			'FR' => 'France',
    			'GF' => 'French Guiana',
    			'PF' => 'French Polynesia',
    			'TF' => 'French Southern Territories',
    			'GA' => 'Gabon',
    			'GM' => 'Gambia',
    			'GE' => 'Georgia',
    			'DE' => 'Germany',
    			'GH' => 'Ghana',
    			'GI' => 'Gibraltar',
    			'GR' => 'Greece',
    			'GL' => 'Greenland',
    			'GD' => 'Grenada',
    			'GP' => 'Guadeloupe',
    			'GU' => 'Guam',
    			'GT' => 'Guatemala',
    			'GG' => 'Guernsey',
    			'GN' => 'Guinea',
    			'GW' => 'Guinea-Bissau',
    			'GY' => 'Guyana',
    			'HT' => 'Haiti',
    			'HM' => 'Heard Island & Mcdonald Islands',
    			'VA' => 'Holy See (Vatican City State)',
    			'HN' => 'Honduras',
    			'HK' => 'Hong Kong',
    			'HU' => 'Hungary',
    			'IS' => 'Iceland',
    			'IN' => 'India',
    			'ID' => 'Indonesia',
    			'IR' => 'Iran, Islamic Republic Of',
    			'IQ' => 'Iraq',
    			'IE' => 'Ireland',
    			'IM' => 'Isle Of Man',
    			'IL' => 'Israel',
    			'IT' => 'Italy',
    			'JM' => 'Jamaica',
    			'JP' => 'Japan',
    			'JE' => 'Jersey',
    			'JO' => 'Jordan',
    			'KZ' => 'Kazakhstan',
    			'KE' => 'Kenya',
    			'KI' => 'Kiribati',
    			'KR' => 'Korea',
    			'KW' => 'Kuwait',
    			'KG' => 'Kyrgyzstan',
    			'LA' => 'Lao People\'s Democratic Republic',
    			'LV' => 'Latvia',
    			'LB' => 'Lebanon',
    			'LS' => 'Lesotho',
    			'LR' => 'Liberia',
    			'LY' => 'Libyan Arab Jamahiriya',
    			'LI' => 'Liechtenstein',
    			'LT' => 'Lithuania',
    			'LU' => 'Luxembourg',
    			'MO' => 'Macao',
    			'MK' => 'Macedonia',
    			'MG' => 'Madagascar',
    			'MW' => 'Malawi',
    			'MY' => 'Malaysia',
    			'MV' => 'Maldives',
    			'ML' => 'Mali',
    			'MT' => 'Malta',
    			'MH' => 'Marshall Islands',
    			'MQ' => 'Martinique',
    			'MR' => 'Mauritania',
    			'MU' => 'Mauritius',
    			'YT' => 'Mayotte',
    			'MX' => 'Mexico',
    			'FM' => 'Micronesia, Federated States Of',
    			'MD' => 'Moldova',
    			'MC' => 'Monaco',
    			'MN' => 'Mongolia',
    			'ME' => 'Montenegro',
    			'MS' => 'Montserrat',
    			'MA' => 'Morocco',
    			'MZ' => 'Mozambique',
    			'MM' => 'Myanmar',
    			'NA' => 'Namibia',
    			'NR' => 'Nauru',
    			'NP' => 'Nepal',
    			'NL' => 'Netherlands',
    			'AN' => 'Netherlands Antilles',
    			'NC' => 'New Caledonia',
    			'NZ' => 'New Zealand',
    			'NI' => 'Nicaragua',
    			'NE' => 'Niger',
    			'NG' => 'Nigeria',
    			'NU' => 'Niue',
    			'NF' => 'Norfolk Island',
    			'MP' => 'Northern Mariana Islands',
    			'NO' => 'Norway',
    			'OM' => 'Oman',
    			'PK' => 'Pakistan',
    			'PW' => 'Palau',
    			'PS' => 'Palestinian Territory, Occupied',
    			'PA' => 'Panama',
    			'PG' => 'Papua New Guinea',
    			'PY' => 'Paraguay',
    			'PE' => 'Peru',
    			'PH' => 'Philippines',
    			'PN' => 'Pitcairn',
    			'PL' => 'Poland',
    			'PT' => 'Portugal',
    			'PR' => 'Puerto Rico',
    			'QA' => 'Qatar',
    			'RE' => 'Reunion',
    			'RO' => 'Romania',
    			'RU' => 'Russian Federation',
    			'RW' => 'Rwanda',
    			'BL' => 'Saint Barthelemy',
    			'SH' => 'Saint Helena',
    			'KN' => 'Saint Kitts And Nevis',
    			'LC' => 'Saint Lucia',
    			'MF' => 'Saint Martin',
    			'PM' => 'Saint Pierre And Miquelon',
    			'VC' => 'Saint Vincent And Grenadines',
    			'WS' => 'Samoa',
    			'SM' => 'San Marino',
    			'ST' => 'Sao Tome And Principe',
    			'SA' => 'Saudi Arabia',
    			'SN' => 'Senegal',
    			'RS' => 'Serbia',
    			'SC' => 'Seychelles',
    			'SL' => 'Sierra Leone',
    			'SG' => 'Singapore',
    			'SK' => 'Slovakia',
    			'SI' => 'Slovenia',
    			'SB' => 'Solomon Islands',
    			'SO' => 'Somalia',
    			'ZA' => 'South Africa',
    			'GS' => 'South Georgia And Sandwich Isl.',
    			'ES' => 'Spain',
    			'LK' => 'Sri Lanka',
    			'SD' => 'Sudan',
    			'SR' => 'Suriname',
    			'SJ' => 'Svalbard And Jan Mayen',
    			'SZ' => 'Swaziland',
    			'SE' => 'Sweden',
    			'CH' => 'Switzerland',
    			'SY' => 'Syrian Arab Republic',
    			'TW' => 'Taiwan',
    			'TJ' => 'Tajikistan',
    			'TZ' => 'Tanzania',
    			'TH' => 'Thailand',
    			'TL' => 'Timor-Leste',
    			'TG' => 'Togo',
    			'TK' => 'Tokelau',
    			'TO' => 'Tonga',
    			'TT' => 'Trinidad And Tobago',
    			'TN' => 'Tunisia',
    			'TR' => 'Turkey',
    			'TM' => 'Turkmenistan',
    			'TC' => 'Turks And Caicos Islands',
    			'TV' => 'Tuvalu',
    			'UG' => 'Uganda',
    			'UA' => 'Ukraine',
    			'AE' => 'United Arab Emirates',
    			'GB' => 'United Kingdom',
    			'US' => 'United States',
    			'UM' => 'United States Outlying Islands',
    			'UY' => 'Uruguay',
    			'UZ' => 'Uzbekistan',
    			'VU' => 'Vanuatu',
    			'VE' => 'Venezuela',
    			'VN' => 'Viet Nam',
    			'VG' => 'Virgin Islands, British',
    			'VI' => 'Virgin Islands, U.S.',
    			'WF' => 'Wallis And Futuna',
    			'EH' => 'Western Sahara',
    			'YE' => 'Yemen',
    			'ZM' => 'Zambia',
    			'ZW' => 'Zimbabwe',
    	);

    	return $countries;
    }
}

