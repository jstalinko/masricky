<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Models\Link;
use App\Models\Logs;
use Illuminate\Http\Request;

class CloakingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $slug = $request->id;
        $reqUri = $request->getRequestUri();
        $getPath = $request->getPathInfo();
        $link = Link::where('slug', $reqUri)->where('active', true)->firstOrFail();
        $link->clicks = $link->clicks+1;
        $link->save();
        

        $ip = '8.8.8.8';

        $ua = $request->userAgent();
        $platform = Helper::platform('device', $ua);
        $country = Helper::country($ip, false);


        /** determine lock platform  */
        if ($link->lock_platform != 'All') {
            if (strtoupper($link->lock_platform) !== strtoupper($platform)) {
                // log to block
                // block
                Helper::block($link, $country, 'BLOCKED BY LOCK PLATFORM');
            }
        }
        /** end lock platform */

        /** determine lock country */
        if ($link->lock_country != 'All' || !empty($link->lock_country)) {
            if (!in_array($country['countryCode'], $link->lock_country)) {
                Helper::block($link, $country, 'BLOCKED BY LOCK COUNTRY');
            }
        }
        /** end lock country */

        /** Determine lock referer */
        if ($link->lock_referer !== 'All') {
            if (!isset($_SERVER['HTTP_REFERER']) || empty($_SERVER['HTTP_REFERER'])) {
                Helper::block($link, $country, 'ACCESS DENIED BLOCKED NO REFERER');
            } else {
                $referer = $_SERVER['HTTP_REFERER'];

                switch ($link->lock_referer) {
                    case 'FacebookAds':
                        if (stripos($referer, 'facebook.com') === false && stripos($referer, 'fb.com') === false) {

                            Helper::block($link, $country, 'ACCESS DENIED NOT A FACEBOOK ADS REFERER');
                        }
                        break;

                    case 'GoogleAds':
                        if (stripos($referer, 'google.com') === false && stripos($referer, 'googleadservices.com') === false) {
                            Helper::block($link, $country, 'ACCESS DENIED NOT A GOOGLE ADS REFERER');
                        }
                        break;

                    default:
                        Helper::block($link, $country, 'ACCESS DENIED NOT A FACEBOOK ADS OR GOOGLE ADS REFERER');
                }
            }
        }


       
        Logs::create([
            'link_id' => $link->id,
            'type' => 'allow',
            'referer' => $_SERVER['HTTP_REFERER'] ?? 'NO REFERER',
            'ip' => Helper::ip(),
            'device' => Helper::platform('device',$request->userAgent()),
            'country' => $country['country'],
            'browser' => Helper::getBrowser(),
            'user_agent' => $request->userAgent(),
            'description' => 'User Allowed'
        ]);
        if($link->random_target_url)
        {
            $target_urls = explode("\n" , str_replace("\r" , "" , $link->target_url));
            shuffle( $target_urls );
            $allow_url = $target_urls[0];
        }else{
            $allow_url = trim(str_replace("\n","" , $link->target_url));
        }

       // dd($link->target_url);
       
        return redirect($allow_url , 301,[]);
    }
}
