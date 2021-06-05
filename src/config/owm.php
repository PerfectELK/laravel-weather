<?php

return [
    'host' => 'http://api.openweathermap.org',
    'reverse_geocode_url' => '/geo/1.0/reverse',
    'weather_url' => '/data/2.5/weather',
    'api_key' => env('OWM_API_KEY')
];
