<?php

namespace App;

use App\Models\Logs;

class Helper
{
    public static function getCountryLists($countryCode = null): array|string|null
    {
        $countries = [
            'AF' => 'Afghanistan',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
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
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'BN' => 'Brunei Darussalam',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'CV' => 'Cabo Verde',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo',
            'CD' => 'Congo (DRC)',
            'CR' => 'Costa Rica',
            'CI' => 'Côte d’Ivoire',
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
            'SZ' => 'Eswatini',
            'ET' => 'Ethiopia',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Laos',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macao',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'MX' => 'Mexico',
            'FM' => 'Micronesia',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'MK' => 'North Macedonia',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'QA' => 'Qatar',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome and Principe',
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
            'KR' => 'South Korea',
            'SS' => 'South Sudan',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syria',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        ];

        if ($countryCode == null) {
            return $countries;
        } else {
            return isset($countries[$countryCode]) ? $countries[$countryCode] : null;
        }
    }
    public static function platform($method = 'device', $ua)
    {
        $user_agent = $ua;
        $platform = 'Unknown';
        $os_array = array(
            '/windows nt 10/i'      =>  'Windows 10',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile',
            '/FBA[NV]|instagram/i'  =>  'FBBrowser'
        );
        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $platform = $value;
            }
        }
        if ($method == 'device') {
            if (preg_match('/windows|mac|linux/i', $platform)) {
                $platform = 'desktop';
            }
            if (preg_match('/iphone|ipod|ipad|android/i', $platform)) {
                $platform = 'mobile';
            }
            if (preg_match('/FBA[NV]|instagram/i', $platform)) {
                $platform = 'FBBrowser';
            }
        } elseif ($method == 'platform') {
            if (preg_match('/windows/i', $platform)) {
                $platform = 'windows';
            }
            if (preg_match('/mac/i', $platform)) {
                $platform = 'macos';
            }
            if (preg_match('/android/i', $platform)) {
                $platform = 'android';
            }
            if (preg_match('/linux/i', $platform)) {
                $platform = 'linux';
            }
            if (preg_match('/ubuntu/i', $platform)) {
                $platform = 'ubuntu';
            }
            if (preg_match('/blackberry/i', $platform)) {
                $platform = 'blackberry';
            }
            if (preg_match('/FBA[NV]|instagram/i', $platform)) {
                $platform = 'FBBrowser';
            }
        }

        return $platform;
    }

    public static function ip()
    {

        $ipaddress = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'unknown';
        }

        if ($ipaddress == '127.0.0.1' || $ipaddress == '::1' || $ipaddress == 'unknown') {
            return '8.8.8.8';
        }
        $ipaddress = explode(',', $ipaddress);
        return trim($ipaddress[0]);

        if (filter_var($ipaddress, FILTER_VALIDATE_IP)) {
            return $ipaddress;
        }



        return 'unknown';
    }

    public static function country($ip, $codeOnly = true)
    {
        $sessionHash = md5($ip);
        if (isset($_SESSION[$sessionHash])) {
            return $_SESSION[$sessionHash];
        } else {

            $url = 'http://pro.ip-api.com/json/' . $ip . '?fields=21229567&key=LlYVGewz67LJuV8';
            $data = self::http($url);
            $data = json_decode($data, true);
            if ($data['status'] == 'success') {
                if ($codeOnly) {
                    return $data['countryCode'];
                } else {
                    return $data;
                }
                $_SESSION[$sessionHash] = $data;
            } else {
                self::country($ip, $codeOnly);
            }
        }
    }
    public static function http($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public static function getBrowser()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        if (
            strpos($userAgent, 'Chrome') !== false &&
            strpos($userAgent, 'Edg') === false &&
            strpos($userAgent, 'OPR') === false &&
            strpos($userAgent, 'Brave') === false
        ) {
            return 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($userAgent, 'Edg') !== false) {
            return 'Edge';
        } elseif (strpos($userAgent, 'OPR') !== false || strpos($userAgent, 'Opera') !== false) {
            return 'Opera';
        } elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
            return 'Safari';
        } else {
            return 'Other';
        }
    }
    public static function block($link, $country = [], $reason = '')
    {
        Logs::create([
            'type' => 'block',
            'link_id' => $link->id,
            'ip' => self::ip(),
            'device' => self::platform('device', $_SERVER['HTTP_USER_AGENT']),
            'browser' => self::getBrowser(),
            'country' => $country['country'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'description' => $reason
        ]);
        if ($link->cloaking_method == 'redirect') {


            @ob_start();
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $link->cloaking_url);
            exit;
        } else {

            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.url('/blank/'.$link->meta_id.'?key='.md5(time())));
            exit;
        }
    }
}
