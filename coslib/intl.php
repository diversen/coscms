<?php

/**
 * File containing method for getting lanugage and countries. 
 * @package intl
 */

/**
 * class for getting lists of countries
 * @package intl
 */
class intl {

    /**
     * set locale according to configuration
     */
    public static function setLocale() {

        $locale = self::getLocale();
        // set locale for time and monetary
        // if the array locale is not sepcified we set time and money
        // according to locales
        setlocale(LC_TIME, $locale);
        setlocale(LC_MONETARY, $locale);
    }
    
    /**
     * set account timezone (account_timezone)
     */
    public static function setAccountTimezone () {

        // set user timezone - only in web mode
        if (!config::isCli()) {
            $timezone = cache::get('account_timezone', session::getUserId());
            if ($timezone) { 
                date_default_timezone_set($timezone);
            }
        }
    }

    /**
     * set default timezone (date_default_timezone)
     */
    public static function setTimezone() {
        
        // if no user timezone
        if (!config::getMainIni('date_default_timezone')) {
            config::setMainIni('date_default_timezone', 'Europe/Copenhagen');
        }
        
        date_default_timezone_set(config::getMainIni('date_default_timezone'));
    }

    /**
     * best guess for getting locales
     * If locale is set directly in configuration (or db override) we will
     * use this locale. 
     * @return string
     */
    public static function getLocale() {
        // check in main config setting 'locale'
        if (isset(config::$vars['coscms_main']['locale'])) {
            return config::$vars['coscms_main']['locale'];
        }

        // if locale is not set we use language .utf8
        if (isset(config::$vars['coscms_main']['language'])) {
            return config::$vars['coscms_main']['language'] . ".utf8";
        }

        // return system locale (e.g. 'C')
        return self::getSystemLocale();
    }

    /**
     * get machines system locales
     * @return string $locale
     */
    public static function getSystemLocale() {
        return setlocale(LC_CTYPE, 0);
    }

    /**
     * get unix system locales
     * @return array $ary array of system locales
     */
    public static function getSystemLocales() {
        $ary = array();
        if (!function_exists('exec')) {
            return false;
        }
        exec('locale -a', $ary);
        return $ary;
    }

    /**
     * get unix utf8 sysem locales 
     * @return array $ary array of utf8 system locales
     */
    public static function getSystemLocalesUTF8() {
        $all = self::getSystemLocales();
        if (!$all) {
            return false;
        }
        $ary = array();
        foreach ($all as $locale) {
            if (strstr($locale, 'utf8')) {
                $ary[] = array('id' => $locale, 'locale' => $locale);
            }
        }
        return $ary;
    }

    /**
     * checks if a locale is valid (used when checking web forms)
     * @param string $locale
     * @return boolean $res  
     */
    public static function validLocaleUTF8($locale) {
        $locales = self::getSystemLocalesUTF8();
        if (!$locales) {
            return false;
        }
        foreach ($locales as $val) {
            if ($val['id'] == $locale) {
                return true;
            }
        }
        return false;
    }

    /**
     * get countries as array where key is country
     * @return array $ary array with countries as key 
     */
    public static function getCountriesWhereKeyIsCountry() {
        $countries = self::getCountries();
        foreach ($countries as $key => $val) {
            $countries[$val] = $val;
            unset($countries[$key]);
        }
        return $countries;
    }

    /**
     * get all timezones as array
     * @return array $ary array with multiple arrays with both key and value
     *                    set to timezone e.g. Africa/Bissau 
     */
    public static function getTimezones() {
        static $regions = array(
            'Africa' => DateTimeZone::AFRICA,
            'America' => DateTimeZone::AMERICA,
            'Antarctica' => DateTimeZone::ANTARCTICA,
            'Aisa' => DateTimeZone::ASIA,
            'Atlantic' => DateTimeZone::ATLANTIC,
            'Europe' => DateTimeZone::EUROPE,
            'Indian' => DateTimeZone::INDIAN,
            'Pacific' => DateTimeZone::PACIFIC
        );
        $timezones = array();
        foreach ($regions as $mask) {
            $list = DateTimeZone::listIdentifiers($mask);
            foreach ($list as $val) {
                $timezones[] = array('id' => $val, 'zone' => $val);
            }
        }
        return $timezones;
    }

    /**
     * can check if a given timezone is valid 
     * @param string $timezone
     * @return boolean $res 
     */
    public static function validTimezone($timezone) {
        $timezones = self::getTimezones();
        foreach ($timezones as $key => $val) {
            if ($val['id'] == $timezone) {
                return true;
            }
        }
        return false;
    }

    /**
     * prepares countries for a dropdown list
     * @return array $ary array of arrays whereboth key and value is 
     *                    country 
     */
    public static function getCountriesForDropDown() {
        $countries = self::getCountriesWhereKeyIsCountry();
        $ary = array();
        foreach ($countries as $key => $val) {
            $insert = array('id' => $key, 'name' => $key);
            $ary[] = $insert;
        }
        return $ary;
    }

    /**
     * get a country list
     * @return array $ary array where contries is value
     */
    public static function getCountries() {
        return $country_list = array(
            "Afghanistan",
            "Albania",
            "Algeria",
            "Andorra",
            "Angola",
            "Antigua and Barbuda",
            "Argentina",
            "Armenia",
            "Australia",
            "Austria",
            "Azerbaijan",
            "Bahamas",
            "Bahrain",
            "Bangladesh",
            "Barbados",
            "Belarus",
            "Belgium",
            "Belize",
            "Benin",
            "Bhutan",
            "Bolivia",
            "Bosnia and Herzegovina",
            "Botswana",
            "Brazil",
            "Brunei",
            "Bulgaria",
            "Burkina Faso",
            "Burundi",
            "Cambodia",
            "Cameroon",
            "Canada",
            "Cape Verde",
            "Central African Republic",
            "Chad",
            "Chile",
            "China",
            "Colombi",
            "Comoros",
            "Congo (Brazzaville)",
            "Congo",
            "Costa Rica",
            "Cote d'Ivoire",
            "Croatia",
            "Cuba",
            "Cyprus",
            "Czech Republic",
            "Denmark",
            "Djibouti",
            "Dominica",
            "Dominican Republic",
            "East Timor (Timor Timur)",
            "Ecuador",
            "Egypt",
            "El Salvador",
            "Equatorial Guinea",
            "Eritrea",
            "Estonia",
            "Ethiopia",
            "Fiji",
            "Finland",
            "France",
            "Gabon",
            "Gambia, The",
            "Georgia",
            "Germany",
            "Ghana",
            "Greece",
            "Grenada",
            "Guatemala",
            "Guinea",
            "Guinea-Bissau",
            "Guyana",
            "Haiti",
            "Honduras",
            "Hungary",
            "Iceland",
            "India",
            "Indonesia",
            "Iran",
            "Iraq",
            "Ireland",
            "Israel",
            "Italy",
            "Jamaica",
            "Japan",
            "Jordan",
            "Kazakhstan",
            "Kenya",
            "Kiribati",
            "Korea, North",
            "Korea, South",
            "Kuwait",
            "Kyrgyzstan",
            "Laos",
            "Latvia",
            "Lebanon",
            "Lesotho",
            "Liberia",
            "Libya",
            "Liechtenstein",
            "Lithuania",
            "Luxembourg",
            "Macedonia",
            "Madagascar",
            "Malawi",
            "Malaysia",
            "Maldives",
            "Mali",
            "Malta",
            "Marshall Islands",
            "Mauritania",
            "Mauritius",
            "Mexico",
            "Micronesia",
            "Moldova",
            "Monaco",
            "Mongolia",
            "Morocco",
            "Mozambique",
            "Myanmar",
            "Namibia",
            "Nauru",
            "Nepa",
            "Netherlands",
            "New Zealand",
            "Nicaragua",
            "Niger",
            "Nigeria",
            "Norway",
            "Oman",
            "Pakistan",
            "Palau",
            "Panama",
            "Papua New Guinea",
            "Paraguay",
            "Peru",
            "Philippines",
            "Poland",
            "Portugal",
            "Qatar",
            "Romania",
            "Russia",
            "Rwanda",
            "Saint Kitts and Nevis",
            "Saint Lucia",
            "Saint Vincent",
            "Samoa",
            "San Marino",
            "Sao Tome and Principe",
            "Saudi Arabia",
            "Senegal",
            "Serbia and Montenegro",
            "Seychelles",
            "Sierra Leone",
            "Singapore",
            "Slovakia",
            "Slovenia",
            "Solomon Islands",
            "Somalia",
            "South Africa",
            "Spain",
            "Sri Lanka",
            "Sudan",
            "Suriname",
            "Swaziland",
            "Sweden",
            "Switzerland",
            "Syria",
            "Taiwan",
            "Tajikistan",
            "Tanzania",
            "Thailand",
            "Togo",
            "Tonga",
            "Trinidad and Tobago",
            "Tunisia",
            "Turkey",
            "Turkmenistan",
            "Tuvalu",
            "Uganda",
            "Ukraine",
            "United Arab Emirates",
            "United Kingdom",
            "United States",
            "Uruguay",
            "Uzbekistan",
            "Vanuatu",
            "Vatican City",
            "Venezuela",
            "Vietnam",
            "Yemen",
            "Zambia",
            "Zimbabwe"
        );
    }

    /**
     * transforms a decimal number to secified locale
     * @param string $locale e.g. en_US
     * @param decimal $decimal 
     * @return decimal $decimal
     */
    public static function formatCurrency($locale, $decimal) {
        $f = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        return $f->format($decimal);
    }

    /**
     * formats a dicimal according to locale
     * @param string $locale e.g. en_US
     * @param string $decimal
     * @param int $max_digits
     * @param int $min_digits
     * @return decimal $res
     */
    public static function formatDecimal($locale, $decimal, $min_digits = 2, $max_digits = 2) {
        $f = new NumberFormatter($locale, NumberFormatter::DECIMAL);
        if ($min_digits) {
            $f->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $min_digits);
        }
        if ($max_digits) {
            $f->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $max_digits); // by default some locales got max 2 fraction digits, that is probably not what you want
        }
        return $f->format($decimal);
    }

    /**
     * Returns a machine workable decimal according to locale. 
     * @param string $locale e.g. en_US
     * @param string $decimal
     * @param int $max_digits
     * @param int $min_digits
     * @return decimal $res
     */
    public static function formatDecimalFromLocale($locale, $decimal, $min_digits = 2, $max_digits = 2) {
        $f = new NumberFormatter($locale, NumberFormatter::DECIMAL);
        if ($min_digits) {
            $f->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $min_digits);
        }
        if ($max_digits) {
            $f->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $max_digits); // by default some locales got max 2 fraction digits, that is probably not what you want
        }
        return $f->parse($decimal);
    }

    /**
     * method for getting browser language
     * @return array $browser e.g. array ('language' => 'en', 'language_long => 'en_GB');
     */
    public static function getBrowserLang() {
        include_once "Zend/Locale.php";

        $zend_locale = new Zend_Locale(Zend_Locale::BROWSER);
        $browser = array();
        $browser['language'] = $zend_locale->getLanguage();
        $browser['language_long'] = $zend_locale->toString();
        return $browser;
    }

}
