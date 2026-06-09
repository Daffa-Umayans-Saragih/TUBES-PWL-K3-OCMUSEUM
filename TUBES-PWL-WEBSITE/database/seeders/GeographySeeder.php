<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeographySeeder extends Seeder
{
    public function run()
    {
        // We must ensure 'Unknown' fallbacks exist for the hierarchy
        $unknownCountryId = $this->getId('countries', 'country_name', 'Unknown Country');
        $chinaId = $this->getId('countries', 'country_name', 'China');
        
        $unknownStateChinaId = $this->getId('states', 'state_name', 'Unknown State', 'country_id', $chinaId);

        $countries = [
            'Austria', 'China', 'France', 'Germany', 'Iran', 'Mexico', 
            'Papua New Guinea', 'Roman Empire', 'United Kingdom', 'United States'
        ];
        
        $countryIds = [];
        foreach ($countries as $c) {
            $countryIds[$c] = $this->getId('countries', 'country_name', $c);
        }

        $states = [
            'Bavaria' => 'Germany',
            'East New Britain' => 'Papua New Guinea',
            'New Britain' => 'Papua New Guinea',
            'Illinois' => 'United States',
            'Massachusetts' => 'United States',
            'Missouri' => 'United States',
            'New Jersey' => 'United States',
            'New York' => 'United States',
            'Ohio' => 'United States',
            'Washington' => 'United States',
            'England' => 'United Kingdom',
            'Scottish' => 'United Kingdom',
        ];

        $stateIds = [];
        foreach ($states as $state => $country) {
            $stateIds[$state] = $this->getId('states', 'state_name', $state, 'country_id', $countryIds[$country]);
        }

        $counties = [
            'Audrain' => 'Missouri'
        ];

        foreach ($counties as $county => $state) {
            $this->getId('counties', 'county_name', $county, 'state_id', $stateIds[$state]);
        }

        $cities = [
            'Akron' => 'Ohio',
            'Augsburg' => 'Bavaria',
            'Boston' => 'Massachusetts',
            'Bristol' => 'England',
            'Buffalo' => 'New York',
            'Chicago' => 'Illinois',
            'London' => 'England',
            'Martinsburg' => 'Missouri',
            'Newark' => 'New Jersey',
            'Nuremberg' => 'Bavaria'
        ];

        foreach ($cities as $city => $state) {
            $this->getId('cities', 'city_name', $city, 'state_id', $stateIds[$state]);
        }
        
        // Beijing needs a state. We use the unknown state of China.
        $this->getId('cities', 'city_name', 'Beijing', 'state_id', $unknownStateChinaId);

        $regions = [
            'Africa', 'Asia', 'Europe', 'North and Central America', 'Oceania'
        ];

        foreach ($regions as $region) {
            $this->getId('regions', 'region_name', $region, 'country_id', $unknownCountryId);
        }
    }

    private function getId($table, $colName, $name, $parentCol = null, $parentId = null)
    {
        $query = DB::table($table)->where($colName, $name);
        if ($parentCol) {
            $query->where($parentCol, $parentId);
        }
        
        $idField = substr($table, 0, -1) . 'id';
        if ($table === 'countries') $idField = 'country_id';
        if ($table === 'counties') $idField = 'county_id';
        if ($table === 'cities') $idField = 'city_id';
        if ($table === 'states') $idField = 'state_id';
        if ($table === 'regions') $idField = 'region_id';

        $id = $query->value($idField);
        
        if (!$id) {
            $data = [$colName => $name];
            if ($parentCol) {
                $data[$parentCol] = $parentId;
            }
            $id = DB::table($table)->insertGetId($data);
        }
        
        return $id;
    }
}
