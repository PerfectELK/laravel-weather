<?php

namespace App\Http\Controllers;

use App\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class CityController extends Controller
{

    public function show(Request $request, $city)
    {
        $cityEncoded = strtolower(mb_convert_encoding($city, 'UTF-8'));
        $citiesFiltered = City::select('name', 'slug')
            ->where(
                DB::raw('LOWER(`name`)'),
                'LIKE',
                '%' . $cityEncoded . '%'
            )
            ->get();
        return $citiesFiltered;
    }

    public function reverseGeocode(Request $request, $lat, $lon)
    {
        $url = \config('owm.host') . \config('owm.reverse_geocode_url');
        $apiKey = \config('owm.api_key');

        $client = new Client();
        $response = $client->get($url, [
            'query' => [
                'appid' => $apiKey,
                'lat' => $lat,
                'lon' => $lon,
            ]
        ]);
        $content = $response->getBody();
        return $content;
    }

    public function weather(Request $request, $city) {
        $url = \config('owm.host') . \config('owm.weather_url');
        $apiKey = \config('owm.api_key');

        $client = new Client();
        $response = $client->get($url, [
            'query' => [
                'appid' => $apiKey,
                'q' => $city,
                'units' => 'metric',
                'lang' => 'ru',
            ]
        ]);
        $content = $response->getBody();
        return $content;
    }

}
