<?php

use Illuminate\Database\Seeder;
use App\City;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $citiesFile = file_get_contents('https://bulk.openweathermap.org/sample/city.list.json.gz');
        $cityJson = gzdecode($citiesFile);
        $cityArr = json_decode($cityJson, true);

        foreach ($cityArr as $cityOwm) {
            if ($cityOwm['country'] !== 'RU') {
                continue;
            }
            if (count(City::where('slug', $cityOwm['name'])->get())) {
                continue;
            }
            $city = new City;
            $city->owmId = $cityOwm['id'];
            $city->slug = $cityOwm['name'];
            $city->country = $cityOwm['country'];
            $city->name = $this->latToCyr($cityOwm['name']);
            $city->save();
        }
    }

    private function latToCyr($textLat) {
        if (strpos($textLat, 'Moscow') !== false) {
            $textLat = str_replace(['Moscow', 'Moscow Oblast'], ['Москва', 'Московская область'], $textLat);
        }
        $str = transliterator_transliterate('Latin-Russian/BGN', $textLat);
        return str_replace('’', 'ь', $str);
    }
}
